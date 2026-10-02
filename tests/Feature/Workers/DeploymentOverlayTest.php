<?php

namespace Tests\Feature\Workers;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Guards the shape of our deployment overlay that M21.2's cutover relies on:
 * a pinned site image, template selection by env, and the routing each
 * template must carry (M21.1 §1.2–§1.3).
 */
class DeploymentOverlayTest extends TestCase
{
    public function test_the_site_image_is_pinned_and_never_falls_back_to_latest(): void
    {
        $overlay = $this->file('compose.alpha.yaml');

        $this->assertMatchesRegularExpression('/cryptozing-site:\$\{CZ_SITE_TAG:\?/', $overlay);
        $this->assertStringNotContainsString('cryptozing-site:latest', $overlay);
    }

    public function test_the_overlay_selects_the_nginx_template_set_by_env_with_frontdoor_as_the_default(): void
    {
        $this->assertStringContainsString(
            '${CZ_NGINX_TEMPLATES:-templates-frontdoor}:/etc/nginx/templates:ro',
            $this->file('compose.alpha.yaml'),
        );

        foreach (['templates-alpha', 'templates-transition', 'templates-apex', 'templates-frontdoor'] as $set) {
            $this->assertFileExists(base_path("docker/production/nginx/{$set}/default.conf.template"));
        }
    }

    #[DataProvider('publicTemplates')]
    public function test_public_templates_route_the_content_paths_to_the_site_container(string $set): void
    {
        $template = $this->file("docker/production/nginx/{$set}/default.conf.template");

        $this->assertStringContainsString('server_name ${CZ_PUBLIC_SERVER_NAME};', $template);
        $this->assertStringContainsString('server_name www.${CZ_PUBLIC_SERVER_NAME};', $template);
        foreach (['location ^~ /learn/', 'location ^~ /staging/', 'location = /robots.txt', 'location = /sitemap.xml', 'location ~ "^/[0-9a-f]{32}\.txt$"'] as $location) {
            $this->assertStringContainsString($location, $template);
        }
        $this->assertStringContainsString('location = /learn   { return 301 /learn/; }', $template);
        $this->assertStringContainsString('location /.well-known/acme-challenge/', $template);
    }

    public function test_the_transition_template_keeps_the_legacy_hostname_noindexed(): void
    {
        $template = $this->file('docker/production/nginx/templates-transition/default.conf.template');

        $this->assertStringContainsString('server_name ${CZ_LEGACY_SERVER_NAME};', $template);
        $this->assertStringContainsString('add_header X-Robots-Tag "noindex, nofollow" always;', $template);
    }

    public function test_the_apex_template_rejects_every_other_hostname(): void
    {
        $template = $this->file('docker/production/nginx/templates-apex/default.conf.template');

        $this->assertStringNotContainsString('CZ_LEGACY_SERVER_NAME', $template);
        preg_match('/server_name \$\{CZ_PUBLIC_SERVER_NAME\};(?<block>.*?)\n}/s', $template, $public);
        $this->assertStringNotContainsString('add_header X-Robots-Tag', $public['block']);
        $this->assertSame(2, preg_match_all('/listen (80|443 ssl) default_server;\n\s+(http2 on;\n\s+)?server_name _;/', $template));
        $this->assertSame(2, substr_count($template, 'return 444;'));
    }

    public function test_the_analytics_services_need_their_secrets_and_publish_no_ports(): void
    {
        $overlay = $this->file('compose.alpha.yaml');

        foreach (['UMAMI_DB_PASSWORD', 'UMAMI_APP_SECRET', 'UMAMI_2FA_KEY'] as $secret) {
            $this->assertMatchesRegularExpression('/\$\{'.$secret.':\?/', $overlay);
        }
        // The only ports line is cz-nginx giving up the recipe's public ports.
        $this->assertSame(1, substr_count($overlay, 'ports:'));
        $this->assertStringContainsString('ports: !reset []', $overlay);
        $this->assertStringContainsString('umami-db:/var/lib/postgresql/data', $overlay);
    }

    public function test_cz_nginx_serves_only_behind_the_shared_front_door(): void
    {
        $overlay = $this->file('compose.alpha.yaml');
        $this->assertStringNotContainsString('/etc/letsencrypt', $overlay);
        $this->assertMatchesRegularExpression('/networks:\n  frontdoor:\n    external: true/', $overlay);

        $template = $this->file('docker/production/nginx/templates-frontdoor/default.conf.template');
        $this->assertStringNotContainsString('ssl', $template);
        $this->assertSame(2, substr_count($template, 'set_real_ip_from 172.30.0.0/24;'));
        $this->assertSame(2, substr_count($template, 'absolute_redirect off;'));
        $this->assertSame(1, substr_count($template, 'return 444;'));

        $blocks = $this->file('docker/production/frontdoor/cryptozing.conf.template');
        $this->assertStringContainsString('server_name ${CZ_PUBLIC_SERVER_NAME} stats.${CZ_PUBLIC_SERVER_NAME};', $blocks);
        $this->assertStringContainsString('server_name www.${CZ_PUBLIC_SERVER_NAME};', $blocks);
        $this->assertStringContainsString('location /.well-known/acme-challenge/', $blocks);
        $this->assertStringContainsString('set $cryptozing_upstream http://cz-nginx:80;', $blocks);
        $this->assertStringContainsString('proxy_set_header X-Real-IP $remote_addr;', $blocks);

        $deploy = $this->file('scripts/deploy.sh');
        $this->assertStringContainsString('docker network create --subnet 172.30.0.0/24 frontdoor', $deploy);
        $this->assertStringContainsString('90-sites-guard.sh --strict cryptozing', $deploy);
    }

    #[DataProvider('allTemplates')]
    public function test_every_template_set_serves_the_stats_host_noindexed(string $set): void
    {
        $template = $this->file("docker/production/nginx/{$set}/default.conf.template");

        $this->assertStringContainsString('server_name stats.${CZ_PUBLIC_SERVER_NAME};', $template);
        $this->assertStringContainsString('set $umami_upstream http://umami:3000;', $template);
        $this->assertMatchesRegularExpression('/listen 80;\n\s+server_name [^;]*stats\.\$\{CZ_PUBLIC_SERVER_NAME\}/', $template);
        preg_match('/server_name stats\.\$\{CZ_PUBLIC_SERVER_NAME\};(?<block>.*?)\n}/s', $template, $stats);
        $this->assertStringContainsString('add_header X-Robots-Tag "noindex, nofollow" always;', $stats['block']);
    }

    public static function allTemplates(): array
    {
        return [
            'alpha' => ['templates-alpha'],
            'transition' => ['templates-transition'],
            'apex' => ['templates-apex'],
            'frontdoor' => ['templates-frontdoor'],
        ];
    }

    public static function publicTemplates(): array
    {
        return [
            'transition' => ['templates-transition'],
            'apex' => ['templates-apex'],
        ];
    }

    private function file(string $path): string
    {
        return (string) file_get_contents(base_path($path));
    }
}
