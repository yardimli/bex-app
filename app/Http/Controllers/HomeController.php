<?php

	namespace App\Http\Controllers;

	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;

	class HomeController extends Controller
	{
		/**
		 * Create a new controller instance.
		 *
		 * @return void
		 */
		public function __construct()
		{
			$this->middleware('auth');
		}

		/**
		 * Show the application dashboard.
		 *
		 * @return \Illuminate\Contracts\Support\Renderable
		 */
		public function index()
		{
			$user = Auth::user();
			// Get all teams the user is a member of.
			$userTeams = $user->teams()->get();
			// Get the ID of the currently active team from the session.
			$currentTeamId = session('current_team_id');

			// Pass the user's teams and the current team ID to the view.
			return view('home', [
				'userTeams' => $userTeams,
				'currentTeamId' => $currentTeamId,
			]);
		}
	}
