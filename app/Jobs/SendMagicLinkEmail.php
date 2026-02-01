<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\MagicLinkMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendMagicLinkEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $token
    ) {}

    public function handle(): void
    {
        Mail::to($this->user->email)
            ->send(new MagicLinkMail($this->user, $this->token));
    }
}
