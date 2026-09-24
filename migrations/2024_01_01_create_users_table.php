<?php declare(strict_types=1);

return new class extends \Skim\Db\Migration {
    public function up(): string {
        return "
            CREATE TABLE users (
                id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name          VARCHAR(120) NOT NULL,
                email         VARCHAR(255) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role          ENUM('admin','editor','viewer') NOT NULL DEFAULT 'viewer',
                created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
    }

    public function down(): string {
        return 'DROP TABLE IF EXISTS users';
    }
};
