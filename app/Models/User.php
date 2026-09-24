<?php declare(strict_types=1);

namespace App\Models;

use Skim\Db\Model;

// @property int    $id
// @property string $name
// @property string $email
// @property string $password_hash
// @property string $role
// @property string $created_at
class User extends Model {
    protected static string $table   = 'users';
    protected static array  $guarded = ['id', 'created_at'];
    protected static array  $casts   = ['id' => 'int'];

    /**
     * @ai-contract verifies a plain-text password against the stored hash
     */
    public function verifyPassword(#[\SensitiveParameter] string $plain): bool {
        return password_verify($plain, (string) $this->password_hash);
    }

    /**
     * @ai-contract returns true when user has admin role
     */
    public function isAdmin(): bool {
        return $this->role === 'admin';
    }
}
