<?php declare(strict_types=1);

return new class extends \Skim\Db\Migration {
    public function up(): string {
        return "
            CREATE TABLE posts (
                id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id    INT UNSIGNED NOT NULL,
                title      VARCHAR(255) NOT NULL,
                slug       VARCHAR(255) NOT NULL,
                body       TEXT NOT NULL,
                status     ENUM('draft','published') NOT NULL DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_slug (slug),
                KEY fk_posts_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
    }

    public function down(): string {
        return 'DROP TABLE IF EXISTS posts';
    }
};
