<?php declare(strict_types=1);

namespace App\Middleware;

use Skim\Core\Middleware;
use Skim\Core\Request;
use Skim\Core\Response;
use Skim\Session\Session;

class AuthMiddleware implements Middleware {
    /**
     * @ai-contract redirects to /login when no active session user_id
     * @ai-contract JSON requests receive 401 instead of redirect
     */
    public function handle(Request $req, Response $res, callable $next): mixed {
        if (!Session::has('user_id')) {
            if ($req->isJson() || $req->isAjax()) {
                return $res->status(401)->json(['error' => 'Unauthorized']);
            }
            return $res->redirect('/login');
        }
        return $next($req, $res);
    }
}
