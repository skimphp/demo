<?php declare(strict_types=1);

namespace App\Controllers;

use Skim\Core\Request;
use Skim\Core\Response;
use Skim\Cache\Cache;
use App\Models\User;
use App\Models\Post;

class ApiController {
    /**
     * @ai-contract health-check endpoint — returns 200 JSON with status and timestamp
     */
    public function health(Request $req, Response $res): mixed {
        return $res->json([
            'status'    => 'ok',
            'timestamp' => date('c'),
            'framework' => 'SKIM Demo',
        ]);
    }

    /**
     * @ai-contract returns paginated users list as JSON, fields: id, name, email, role, created_at
     */
    public function users(Request $req, Response $res): mixed {
        $page     = max(1, (int) $req->get('page', 1));
        $per_page = 20;

        $all   = User::all(1000);
        $total = count($all);
        $slice = array_slice($all, ($page - 1) * $per_page, $per_page);

        $data = array_map(fn(User $u) => [
            'id'         => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'role'       => $u->role,
            'created_at' => $u->created_at,
        ], $slice);

        return $res->json([
            'data'  => $data,
            'meta'  => ['total' => $total, 'page' => $page, 'per_page' => $per_page],
        ]);
    }

    /**
     * @ai-contract returns paginated published posts as JSON, with excerpt field
     */
    public function posts(Request $req, Response $res): mixed {
        $posts = Cache::remember('posts:published', ttl: 60, default: function () {
            return Post::where(['status' => 'published'])
                ->order('created_at DESC')
                ->limit(50)
                ->all();
        });

        $data = array_map(fn(Post $p) => [
            'id'         => $p->id,
            'title'      => $p->title,
            'slug'       => $p->slug,
            'excerpt'    => $p->excerpt(),
            'status'     => $p->status,
            'created_at' => $p->created_at,
        ], $posts);

        return $res->json(['data' => $data, 'meta' => ['total' => count($data)]]);
    }

    /**
     * @ai-contract returns single post as JSON, 404 when not found
     */
    public function postShow(Request $req, Response $res): mixed {
        $found = Post::find((int) $req->param('id'));

        if ($found === null) {
            return $res->status(404)->json(['error' => 'Post not found.']);
        }

        return $res->json([
            'id'         => $found->id,
            'title'      => $found->title,
            'slug'       => $found->slug,
            'body'       => $found->body,
            'status'     => $found->status,
            'created_at' => $found->created_at,
        ]);
    }
}
