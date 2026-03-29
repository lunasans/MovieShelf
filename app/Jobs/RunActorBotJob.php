<?php

namespace App\Jobs;

use App\Models\Actor;
use App\Models\BotRun;
use App\Services\ActorBotService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunActorBotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // Allow 1 hour

    protected $botRun;

    public function __construct(BotRun $botRun)
    {
        $this->botRun = $botRun;
    }

    public function handle(ActorBotService $botService): void
    {
        try {
            // Keep running until all chunks are finished or status changed
            while (true) {
                // Check if user cancelled it from admin panel
                $currentRun = BotRun::find($this->botRun->id);
                if (!$currentRun || $currentRun->status !== 'running') {
                    break;
                }

                $hasMore = $botService->processChunk($currentRun, 50);

                if (!$hasMore) {
                    break;
                }
            }

        } catch (\Exception $e) {
            BotRun::where('id', $this->botRun->id)->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
        }
    }
}
