<?php

namespace App\Jobs;

use App\Helpers\WhatsappHelper;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StudentAddNewDiscussionNotificationQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $whatsappHelper;
    private $submission;
    private $created_at;

    public function __construct($submission, $created_at)
    {
        $wa_api_key = Setting::where('key', 'wa_api_key')->firstOrFail();
        $wa_sender_phone_number = Setting::where('key', 'wa_sender_phone_number')->firstOrFail();
        $this->whatsappHelper = new WhatsappHelper($wa_api_key->value, $wa_sender_phone_number->value);

        $this->submission = $submission;
        $this->created_at = $created_at;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->whatsappHelper->student_add_new_discussions($this->submission, $this->created_at);
    }
}
