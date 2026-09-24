<?php declare(strict_types=1);

namespace App\Controllers;

use Skim\Core\Request;
use Skim\Core\Response;
use Skim\Cache\Cache;
use App\Models\Post;

class PostController {
    /**
     * @ai-contract lists published posts, cached for 60 s
     * @ai-contract supports htmx partial via smart_view — returns fragment 'posts-list' on htmx request
     */
    public function index(Request $req, Response $res): mixed {
        $posts = Cache::remember('posts:published', ttl: 60, default: function () {
            return Post::where(['status' => 'published'])
                ->order('created_at DESC')
                ->limit(20)
                ->all();
        });

        return $res->smartView('posts/index', [
            'title' => 'Posts — SKIM Demo',
            'posts' => $posts,
        ], $req);
    }

    /**
     * @ai-contract fetches single published post by id, 404 when missing or draft
     */
    public function show(Request $req, Response $res): mixed {
        $post = Post::findOrFail((int) $req->param('id'));

        if (!$post->isPublished()) {
            return $res->status(404)->view('errors/404', ['title' => 'Not Found']);
        }

        return $res->view('posts/show', [
            'title' => e($post->title) . ' — SKIM Demo',
            'post'  => $post,
        ]);
    }
}
