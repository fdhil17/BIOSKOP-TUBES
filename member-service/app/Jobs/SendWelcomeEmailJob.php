<?php

namespace App\Jobs;

use App\Models\Member;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendWelcomeEmailJob implements ShouldQueue
{
    use Queueable;

    public $member;

    /**
     * Create a new job instance.
     */
    public function __construct(Member $member)
    {
        $this->member = $member;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Simulasi pengiriman email lambat (Message Broker / Asynchronous)
        sleep(2);
        Log::info("Message Broker: Berhasil mengirim Welcome Email ke " . $this->member->email);
    }
}
