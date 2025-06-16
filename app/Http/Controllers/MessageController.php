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
        $teams = $user->teams()->get(); // For the filter dropdown
        return view('messages.inbox', ['teams' => $teams]);
    }
}
