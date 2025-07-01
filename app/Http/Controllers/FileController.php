<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userTeams = $user->teams()->get();
        $currentTeamId = session('current_team_id');

        return view('files.index', [
            'userTeams' => $userTeams,
            'currentTeamId' => $currentTeamId,
        ]);
    }
}
