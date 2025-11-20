<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $games = Game::withCount('currencies')->orderByDesc('created_at')->get();

        return view('blog.index', [
            'games' => $games,
        ]);
    }
}
