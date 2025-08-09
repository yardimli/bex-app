<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LlmUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsageController extends Controller
{
    public function getUsageLogs(Request $request)
    {
        $user = Auth::user();

        $userTeamIds = $user->teams()->pluck('teams.id');

        $logs = LlmUsageLog::with(['user:id,name', 'team:id,name', 'llm:id,name'])
            ->where('user_id', $user->id)
            ->orWhereIn('team_id', $userTeamIds)
            ->orderBy('created_at', 'desc')
            ->paginate(25); // Paginate for performance

        return response()->json($logs);
    }
}
