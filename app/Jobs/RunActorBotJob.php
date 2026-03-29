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
            $currentRun = BotRun::find($this->botRun->id);
            
            // Check if user cancelled it from admin panel
            if (!$currentRun || $currentRun->status !== 'running') {
                return;
            }

            $hasMore = $botService->processChunk($currentRun, 25);

            if ($hasMore) {
                self::dispatch($currentRun)->delay(now()->addSeconds(1));
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
