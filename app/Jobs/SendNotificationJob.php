<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected string $title;
    protected string $message;
    protected string $type;
    protected array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, string $title, string $message, string $type = 'info', array $data = [])
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Notification::create([
            'user_id' => $this->userId,
            'title'   => $this->title,
            'message' => $this->message,
            'type'    => $this->type,
            'data'    => $this->data,
            'read_at' => null,
        ]);

        Log::info("Notification sent to User #{$this->userId}: {$this->title}");
        
        // In a real app, you would also trigger Pusher, Firebase, or Email here
    }
}
