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

class SubmissionScheduleReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $whatsappHelper;
    protected $submission;
    protected $submission_schedule;

    public function __construct($submission, $submisison_schedule)
    {
        $wa_api_key = Setting::where('key', 'wa_api_key')->firstOrFail();
        $wa_sender_phone_number = Setting::where('key', 'wa_sender_phone_number')->firstOrFail();
        $this->whatsappHelper = new WhatsappHelper($wa_api_key->value, $wa_sender_phone_number->value);

        $this->submission = $submission;
        $this->submission_schedule = $submisison_schedule;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->submission_schedule === $this->submission->schedule) {
            $this->whatsappHelper->submission_scheduler_reminder($this->submission);
        }
    }
}
