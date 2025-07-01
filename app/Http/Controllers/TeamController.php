<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userTeams = $user->teams()->get();
        $currentTeamId = session('current_team_id');

        return view('teams.index', [
            'userTeams' => $userTeams,
            'currentTeamId' => $currentTeamId,
        ]);
    }
}
