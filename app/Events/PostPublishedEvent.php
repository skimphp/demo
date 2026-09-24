<?php declare(strict_types=1);

namespace App\Events;

use App\Models\Post;

class PostPublishedEvent {
    public function __construct(
        public readonly post $post,
    ) {}
}
