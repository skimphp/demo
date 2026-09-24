<?php declare(strict_types=1);

namespace App\Controllers;

use Skim\Core\Request;
use Skim\Core\Response;
use Skim\Session\Session;
use Skim\Validation\Validate;
use App\Models\User;

class AuthController {
    /**
     * @ai-contract renders the login form
     * @ai-contract redirects to /admin when already authenticated
     */
    public function loginForm(Request $req, Response $res): mixed {
        if (Session::has('user_id')) {
            return $res->redirect('/admin');
        }
        return $res->view('auth/login', ['title' => 'Sign In — SKIM Demo']);
    }

    /**
     * @ai-contract validates credentials, starts Session, redirects to /admin
     * @ai-contract flashes errors back to login form on failure
     */
    public function login(Request $req, Response $res): mixed {
        $result = Validate::make([
            'email'    => ['required', 'email'],
            'password' => ['required', 'min_len:4'],
        ])->check([
            'email'    => $req->post('email', ''),
            'password' => $req->post('password', ''),
        ]);

        if (!$result->ok) {
            Session::flash('errors', $result->errors());
            Session::flash('old', ['email' => $req->post('email', '')]);
            return $res->redirect('/login');
        }

        $found = User::findBy('email', strtolower(trim((string) $result->validated()['email'])));

        if ($found === null || !$found->verifyPassword((string) $result->validated()['password'])) {
            Session::flash('errors', ['email' => ['Invalid email or password.']]);
            Session::flash('old', ['email' => $req->post('email', '')]);
            return $res->redirect('/login');
        }

        Session::regenerate();
        Session::set('user_id', $found->id);
        Session::set('user_name', $found->name);
        Session::set('user_role', $found->role);
        Session::flash('message', 'Welcome back, ' . $found->name . '!');

        return $res->redirect('/admin');
    }

    /**
     * @ai-contract destroys session and redirects to home
     */
    public function logout(Request $req, Response $res): mixed {
        Session::flush();
        Session::flash('message', 'You have been signed out.');
        return $res->redirect('/');
    }
}
