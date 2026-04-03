<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BotRun;
use App\Models\BotLog;
use App\Models\Actor;
use App\Jobs\RunActorBotJob;
use Illuminate\Http\Request;

class BotController extends Controller
{
    public function index()
    {
        $currentRun = BotRun::where('status', 'running')->first();
        $recentRuns = BotRun::orderBy('created_at', 'desc')->limit(10)->get();

        return view('admin.bot.index', compact('currentRun', 'recentRuns'));
    }

    public function start(Request $request)
    {
        if (BotRun::where('status', 'running')->exists()) {
            return back()->with('error', 'Ein Bot-Lauf ist bereits aktiv.');
        }

        $botRun = BotRun::create([
            'status' => 'running',
            'total_actors' => Actor::count(),
            'processed_actors' => 0,
            'last_actor_id' => 0,
        ]);

        // Dispatch as a true background Job (the Bot)
        RunActorBotJob::dispatch($botRun);

        return redirect()->route('admin.bot.index')->with('success', 'Bot-Prozess wurde im Hintergrund auf dem Server gestartet!');
    }

    public function process(Request $request)
    {
        $currentRun = BotRun::where('status', 'running')->first();
        if (!$currentRun) {
            return response()->json(['running' => false, 'status' => 'inactive']);
        }

        $botService = app(\App\Services\ActorBotService::class);
        
        // Small chunk size for browser requests to avoid timeouts
        $hasMore = $botService->processChunk($currentRun, 5); 

        return response()->json([
            'running' => true,
            'status' => 'running',
            'hasMore' => $hasMore,
            'total' => $currentRun->total_actors,
            'processed' => $currentRun->processed_actors,
        ]);
    }

    public function cancel(Request $request)
    {
        $botRun = BotRun::where('status', 'running')->first();
        if ($botRun) {
            $botRun->update([
                'status' => 'aborted',
                'error_message' => 'Abgebrochen durch Nutzer',
                'completed_at' => now()
            ]);
            return back()->with('success', 'Bot-Ausführung als Abgebrochen markiert.');
        }

        return back();
    }

    public function status(Request $request)
    {
        $currentRun = BotRun::where('status', 'running')->first();
        if (!$currentRun) {
            return response()->json(['running' => false, 'status' => 'completed']);
        }

        return response()->json([
            'running' => true,
            'status' => 'running',
            'total' => $currentRun->total_actors,
            'processed' => $currentRun->processed_actors,
        ]);
    }

    public function logs(BotRun $botRun)
    {
        $logs = $botRun->logs()->with('actor')->orderBy('created_at', 'asc')->get();
        return response()->json(['logs' => $logs]);
    }
}
