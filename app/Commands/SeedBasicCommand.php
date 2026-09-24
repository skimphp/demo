<?php declare(strict_types=1);

namespace App\Commands;

use Skim\Cli\Command;
use Skim\Db\Db;
use App\Models\User;
use App\Models\Post;

class SeedBasicCommand extends Command {
    protected string $signature   = 'seed:basic';
    protected string $description = 'Seed demo users and posts for the basic demo';

    /**
     * @ai-contract inserts one admin user + 6 sample posts if tables are empty
     * @ai-contract idempotent — skips seed when data already present
     */
    public function handle(): int {
        if (User::count() > 0) {
            $this->info('Already seeded — skipping.');
            return 0;
        }

        Db::transaction(function (): void {
            $admin = User::create([
                'name'          => 'Admin User',
                'email'         => 'admin@example.com',
                'password_hash' => password_hash('secret', PASSWORD_BCRYPT),
                'role'          => 'admin',
            ]);

            $editor = User::create([
                'name'          => 'Editor User',
                'email'         => 'editor@example.com',
                'password_hash' => password_hash('secret', PASSWORD_BCRYPT),
                'role'          => 'editor',
            ]);

            $sample_posts = [
                ['Getting Started with SKIM Framework',   'published', 'SKIM is a PHP 8.5+ micro-framework designed for speed, clarity, and zero magic. This post walks you through the core concepts: routing, controllers, models, and views — all in under 10 minutes.'],
                ['Understanding query_gen',               'published', 'The query_gen system lets you write dynamic SQL without string concatenation. %where% and %set% tokens are removed silently when their keys are absent, making conditional queries trivial.'],
                ['Property Hooks in PHP 8.4',             'published', 'PHP 8.4 property hooks bring get/set interceptors directly onto class Properties. SKIM uses them for auto-normalization (email → lowercase), dirty tracking, and computed fields.'],
                ['Cache: Redis-first with File Fallback', 'published', "SKIM's cache facade uses Redis by default and silently falls back to file when Redis is unreachable. This prevents a cache failure from cascading into a full app failure."],
                ['Fragment Rendering with HTMX',          'published', '<!-- @fragment name --> blocks let you extract a named section of any PHP template. Pair with HTMX and smart_view() for seamless partial page updates without a JS framework.'],
                ['Draft: Upcoming Features',              'draft',     'This post is a draft — it will not appear on the public /posts page. Only logged-in admins can see draft posts in the admin panel.'],
            ];

            foreach ($sample_posts as [$title, $status, $body]) {
                $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
                Post::create([
                    'user_id' => $admin->id,
                    'title'   => $title,
                    'slug'    => $slug,
                    'body'    => $body,
                    'status'  => $status,
                ]);
            }
        });

        $this->success('Seeded: 2 users + 6 posts (5 published, 1 draft).');
        $this->info('  Admin login: admin@example.com / secret');
        return 0;
    }
}
