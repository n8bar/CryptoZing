<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Guards MS19.4 §3: "RC" / "Release Candidate" are internal milestone terms and
 * must never reach a user. The deployed RC is publicly called "open beta".
 *
 * We assert on the unambiguous phrase "Release Candidate" in user-facing template
 * source. We deliberately do NOT flag:
 *   - "pre-release" — an accurate lifecycle word, kept where true.
 *   - bare "RC" — it appears legitimately in internal code/config comments.
 */
class UserFacingCopyTest extends TestCase
{
    public function test_user_facing_copy_has_no_release_candidate_jargon(): void
    {
        $roots = [base_path('resources/views'), base_path('site')];
        $skipDirs = ['node_modules', '_site', 'vendor'];
        $extensions = ['.blade.php', '.php', '.html', '.njk', '.md'];

        $offenders = [];

        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveCallbackFilterIterator(
                    new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
                    fn ($current) => ! ($current->isDir() && in_array($current->getFilename(), $skipDirs, true))
                )
            );

            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $matchesExtension = false;
                foreach ($extensions as $extension) {
                    if (str_ends_with($file->getFilename(), $extension)) {
                        $matchesExtension = true;
                        break;
                    }
                }
                if (! $matchesExtension) {
                    continue;
                }

                if (preg_match('/release\s+candidate/i', (string) file_get_contents($file->getPathname()))) {
                    $offenders[] = str_replace(base_path() . '/', '', $file->getPathname());
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'User-facing copy must not contain "Release Candidate" (use "open beta" for the deployed RC). Offenders: ' . implode(', ', $offenders)
        );
    }
}
