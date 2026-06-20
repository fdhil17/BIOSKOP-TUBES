<?php

namespace App\Jobs;

use App\Models\Movie;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessMovieAddedJob implements ShouldQueue
{
    use Queueable;

    public $movie;

    /**
     * Create a new job instance.
     */
    public function __construct(Movie $movie)
    {
        $this->movie = $movie;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        sleep(1);
        Log::info("Message Broker: Notifikasi rilis film baru disebarkan untuk film: " . $this->movie->title);
    }
}
