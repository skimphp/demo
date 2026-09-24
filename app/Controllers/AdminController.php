<?php declare(strict_types=1);

namespace App\Controllers;

use Skim\Core\Request;
use Skim\Core\Response;
use Skim\Session\Session;
use Skim\Validation\Validate;
use Skim\Cache\Cache;
use Skim\Events\Event;
use App\Models\User;
use App\Models\Post;
use App\Events\UserCreatedEvent;
use App\Events\PostPublishedEvent;

class AdminController {
    /**
     * @ai-contract renders admin dashboard with summary stats
     */
    public function dashboard(Request $req, Response $res): mixed {
        $stats = Cache::remember('admin:stats', ttl: 30, default: function () {
            return [
                'user_count'      => User::count(),
                'post_count'      => Post::count(),
                'published_count' => Post::count(['status' => 'published']),
                'draft_count'     => Post::count(['status' => 'draft']),
            ];
        });

        return $res->view('admin/dashboard', [
            'title'   => 'Dashboard — Admin',
            'stats'   => $stats,
            'message' => Session::get('message'),
        ]);
    }

    /**
     * @ai-contract lists all users for admin management
     */
    public function users(Request $req, Response $res): mixed {
        $users = Cache::remember('admin:users', ttl: 30, default: fn() => User::all(100));

        return $res->view('admin/users/index', [
            'title' => 'Users — Admin',
            'users' => $users,
        ]);
    }

    /**
     * @ai-contract creates a new user, emits user_created_event, redirects back
     */
    public function storeUser(Request $req, Response $res): mixed {
        $result = Validate::make([
            'name'     => ['required', 'min_len:2', 'max_len:120'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'min_len:6'],
            'role'     => ['required', 'in:admin,editor,viewer'],
        ])->check([
            'name'     => $req->post('name', ''),
            'email'    => $req->post('email', ''),
            'password' => $req->post('password', ''),
            'role'     => $req->post('role', ''),
        ]);

        if (!$result->ok) {
            Session::flash('errors', $result->errors());
            return $res->redirect('/admin/users');
        }

        $data = $result->validated();
        $new_user = User::create([
            'name'          => $data['name'],
            'email'         => strtolower(trim((string) $data['email'])),
            'password_hash' => password_hash((string) $data['password'], PASSWORD_BCRYPT),
            'role'          => $data['role'],
        ]);

        Cache::delete('admin:users');

        Event::emit(new UserCreatedEvent($new_user, $req->ip()));
        Session::flash('message', 'User "' . $new_user->name . '" created.');

        return $res->redirect('/admin/users');
    }

    /**
     * @ai-contract deletes a user by id and invalidates cache
     */
    public function deleteUser(Request $req, Response $res): mixed {
        $found = User::findOrFail((int) $req->param('id'));
        $found->delete();
        Cache::delete('admin:users');
        Session::flash('message', 'User deleted.');
        return $res->redirect('/admin/users');
    }

    /**
     * @ai-contract lists all posts for admin management
     */
    public function posts(Request $req, Response $res): mixed {
        $posts = Post::where([])->order('created_at DESC')->limit(100)->all();

        return $res->view('admin/posts/index', [
            'title' => 'Posts — Admin',
            'posts' => $posts,
        ]);
    }

    /**
     * @ai-contract creates a new post, emits post_published_event when status=published
     */
    public function storePost(Request $req, Response $res): mixed {
        $result = Validate::make([
            'title'  => ['required', 'min_len:2', 'max_len:255'],
            'body'   => ['required'],
            'status' => ['required', 'in:draft,published'],
        ])->check([
            'title'  => $req->post('title', ''),
            'body'   => $req->post('body', ''),
            'status' => $req->post('status', ''),
        ]);

        if (!$result->ok) {
            Session::flash('errors', $result->errors());
            return $res->redirect('/admin/posts');
        }

        $data    = $result->validated();
        $slug    = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $data['title']), '-'));
        $user_id = (int) Session::get('user_id', 1);

        $new_post = Post::create([
            'user_id' => $user_id,
            'title'   => $data['title'],
            'slug'    => $slug,
            'body'    => $data['body'],
            'status'  => $data['status'],
        ]);

        Cache::delete('posts:published');

        if ($new_post->isPublished()) {
            Event::emit(new PostPublishedEvent($new_post));
        }

        Session::flash('message', 'Post "' . $new_post->title . '" saved.');
        return $res->redirect('/admin/posts');
    }

    /**
     * @ai-contract shows post edit form (admins can edit drafts too)
     */
    public function editPost(Request $req, Response $res): mixed {
        $post = Post::findOrFail((int) $req->param('id'));
        return $res->view('admin/posts/edit', [
            'title' => 'Edit Post — Admin',
            'post'  => $post,
        ]);
    }

    /**
     * @ai-contract updates a post, emits post_published_event when status changes to published
     */
    public function updatePost(Request $req, Response $res): mixed {
        $found = Post::findOrFail((int) $req->param('id'));

        $result = Validate::make([
            'title'  => ['required', 'min_len:2', 'max_len:255'],
            'body'   => ['required'],
            'status' => ['required', 'in:draft,published'],
        ])->check([
            'title'  => $req->post('title', ''),
            'body'   => $req->post('body', ''),
            'status' => $req->post('status', ''),
        ]);

        if (!$result->ok) {
            Session::flash('errors', $result->errors());
            return $res->redirect('/admin/posts/' . $found->id);
        }

        $data = $result->validated();
        $slug = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $data['title']), '-'));

        $was_published = $found->isPublished();
        $found->fill([
            'title'  => $data['title'],
            'slug'   => $slug,
            'body'   => $data['body'],
            'status' => $data['status'],
        ]);
        $found->save();

        Cache::delete('posts:published');

        if (!$was_published && $found->isPublished()) {
            Event::emit(new PostPublishedEvent($found));
        }

        Session::flash('message', 'Post updated.');
        return $res->redirect('/admin/posts');
    }

    /**
     * @ai-contract deletes a post by id and invalidates caches
     */
    public function deletePost(Request $req, Response $res): mixed {
        $found = Post::findOrFail((int) $req->param('id'));
        $found->delete();
        Cache::delete('posts:published');
        Session::flash('message', 'Post deleted.');
        return $res->redirect('/admin/posts');
    }
}
