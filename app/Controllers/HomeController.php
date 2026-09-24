<?php declare(strict_types=1);

namespace App\Controllers;

use Skim\Core\Request;
use Skim\Core\Response;

class HomeController {
    /**
     * @ai-contract renders the framework feature showcase home page
     */
    public function index(Request $req, Response $res): mixed {
        return $res->view('home', ['title' => 'SKIM Framework Demo']);
    }

    /**
     * @ai-contract deliberately throws so the dev error page can be demonstrated
     */
    public function error(Request $req, Response $res): mixed {
        throw new \RuntimeException('Intentional demo error for stack trace page.');
    }
}
