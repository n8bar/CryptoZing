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

    public function test_the_overlay_selects_the_nginx_template_set_by_env_with_alpha_as_the_default(): void
    {
        $this->assertStringContainsString(
            '${CZ_NGINX_TEMPLATES:-templates-alpha}:/etc/nginx/templates:ro',
            $this->file('compose.alpha.yaml'),
        );

        foreach (['templates-alpha', 'templates-transition', 'templates-apex'] as $set) {
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
        $this->assertStringNotContainsString('add_header X-Robots-Tag', $template);
        $this->assertSame(2, preg_match_all('/listen (80|443 ssl) default_server;\n\s+(http2 on;\n\s+)?server_name _;/', $template));
        $this->assertSame(2, substr_count($template, 'return 444;'));
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
