<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Get all games with optional filtering
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $games = Game::all();
        return response()->json([
            'success' => true,
            'message' => 'Games retrieved successfully',
            'data' => $games
        ]);
    }

    /**
     * Get specific game by ID
     *
     * @param Game $game
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Game $game)
    {
        return response()->json([
            'success' => true,
            'message' => 'Game retrieved successfully',
            'data' => $game
        ]);
    }
}
