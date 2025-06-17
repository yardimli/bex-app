<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        $team = DB::transaction(function () use ($user, $validated) {
            $team = Team::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'owner_id' => $user->id,
            ]);

            $team->teamMembers()->create([
                'user_id' => $user->id,
                'role' => 'owner',
            ]);

            return $team;
        });

        return response()->json($team->load('teamMembers.user'), 201);
    }

    public function addMember(Request $request, Team $team)
    {
        if (Auth::id() !== $team->owner_id) {
            return response()->json(['error' => 'Only the team owner can add members.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userToAdd = User::where('email', $request->email)->first();

        if ($team->teamMembers()->where('user_id', $userToAdd->id)->exists()) {
            return response()->json(['error' => 'User is already a member of this team.'], 409);
        }

        $member = $team->teamMembers()->create([
            'user_id' => $userToAdd->id,
            'role' => 'member',
        ]);

        return response()->json($member->load('user'), 201);
    }

    public function userTeams()
    {
        $user = Auth::user();
        $teams = $user->teams()->with('owner', 'teamMembers.user')->get();
        return response()->json([
            'teams' => $teams,
            'user_id' => $user->id,
            'current_team_id' => session('current_team_id', $teams->first()->id ?? null)
        ]);
    }

    public function switchTeam(Request $request)
    {
        $validated = $request->validate([
            'team_id' => 'required|integer|exists:teams,id',
        ]);

        $user = Auth::user();
        $teamId = $validated['team_id'];

        if (!$user->teamMemberships()->where('team_id', $teamId)->exists()) {
            return response()->json(['error' => 'You are not a member of this team.'], 403);
        }

        session(['current_team_id' => $teamId]);

        return response()->json(['success' => true, 'message' => 'Switched to team ' . $teamId]);
    }

    public function getMembers(Team $team)
    {
        // Security check: ensure the current user is a member of the team they are requesting members for.
        if (!Auth::user()->teams()->where('team_id', $team->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($team->users()->get(['users.id', 'users.name']));
    }
}
