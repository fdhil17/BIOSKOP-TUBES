<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTicketNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $ticket;
    protected $member;
    protected $movie;

    public function __construct($ticket, $member, $movie)
    {
        $this->ticket = $ticket;
        $this->member = $member;
        $this->movie = $movie;
    }

    public function handle(): void
    {
        Log::info('Processing: E-Ticket Notification');
        Log::info('Sending E-Ticket to: ' . $this->member['email']);
        Log::info('Movie: ' . $this->movie['title']);
        Log::info('Ticket Status: ' . $this->ticket['status']);
        
        sleep(2); // Simulate delay
        
        Log::info('Success: E-Ticket sent successfully to ' . $this->member['name']);
    }
}
