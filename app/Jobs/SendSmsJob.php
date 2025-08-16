<?php

namespace App\Jobs;

use App\Sms\CustomSmsApi;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public $phone_number;

    public $message;

    /**
     * Create a new job instance.
     */
    public function __construct($phone_number, $message)
    {
        $this->phone_number = $phone_number;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(CustomSmsApi $sms)
    {
        $sms->sendMessage($this->phone_number, $this->message);
    }
}
