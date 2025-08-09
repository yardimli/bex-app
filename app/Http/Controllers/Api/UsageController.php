<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LlmUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function getUsageStats(Request $request)
    {
        $user = Auth::user();

        $stats = LlmUsageLog::where('user_id', $user->id)
            ->select(
                DB::raw('SUM(prompt_tokens) as total_prompt_tokens'),
                DB::raw('SUM(completion_tokens) as total_completion_tokens'),
                DB::raw('SUM(prompt_cost + completion_cost) as total_cost')
            )
            ->first();

        return response()->json([
            'total_prompt_tokens' => (int) $stats->total_prompt_tokens,
            'total_completion_tokens' => (int) $stats->total_completion_tokens,
            'total_cost' => (float) $stats->total_cost,
        ]);
    }
}
