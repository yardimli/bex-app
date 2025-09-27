<?php

	namespace App\Http\Controllers;

	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;

	/**
	 * NEW: Controller to handle the public-facing landing page.
	 */
	class LandingController extends Controller
	{
		/**
		 * Show the application's landing page.
		 *
		 * If the user is already logged in, redirect them to the dashboard.
		 *
		 * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
		 */
		public function index()
		{
			if (Auth::check()) {
				return redirect()->route('home');
			}
			return view('welcome');
		}
	}
