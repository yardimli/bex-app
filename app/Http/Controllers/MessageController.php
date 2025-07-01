<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Display the user's inbox.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $userTeams = $user->teams()->get(); // For the filter dropdown
        $currentTeamId = session('current_team_id');
//        return view('messages.inbox', ['teams' => $teams]);
        return view('messages.inbox', [
            'userTeams' => $userTeams,
            'currentTeamId' => $currentTeamId,
        ]);
    }
}
