<?php declare(strict_types=1);

namespace App\Models;

use Skim\Db\Model;

// @property int    $id
// @property int    $user_id
// @property string $title
// @property string $slug
// @property string $body
// @property string $status
// @property string $created_at
class Post extends Model {
    protected static string $table   = 'posts';
    protected static array  $guarded = ['id', 'created_at'];
    protected static array  $casts   = ['id' => 'int', 'user_id' => 'int'];

    /**
     * @ai-contract returns true when post status is published
     */
    public function isPublished(): bool {
        return $this->status === 'published';
    }

    /**
     * @ai-contract returns a 160-char excerpt stripped of HTML tags
     */
    public function excerpt(int $length = 160): string {
        $text = strip_tags((string) $this->body);
        return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . '…' : $text;
    }
}
