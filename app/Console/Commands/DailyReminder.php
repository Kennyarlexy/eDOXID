<?php

namespace App\Console\Commands;

use App\Helpers\WhatsappHelper;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\Verificator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DailyReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily Reminder';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $wa_api_key = Setting::where('key', 'wa_api_key')->firstOrFail();
        $wa_sender_phone_number = Setting::where('key', 'wa_sender_phone_number')->firstOrFail();
        $whatsappHelper = new WhatsappHelper($wa_api_key->value, $wa_sender_phone_number->value);

        // submission that not have been have result status yet
        $submissions = Submission::where('result_status', Submission::RESULT_PENDING)->where('schedule', '<', now()->subDays(7)->endOfDay()->format('Y-m-d H:i:s'))->get();
        foreach ($submissions as $submission) {
            $pembimbing_verificators = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PEMBIMBING)->get();
            foreach ($pembimbing_verificators as $pembimbing_verificator) {
                $whatsappHelper->result_status_not_changed($submission, $pembimbing_verificator);
                sleep(5);
            }
        }

        // submission that the verificators not have been accepted yet the final_status
        $submissions = Submission::where('result_status', Submission::RESULT_APPROVED_WITH_FIXED)->where('final_document_url', '!=', null)->where('final_document_created_at', '!=', null)->where('final_document_created_at', '<', now()->subDay()->endOfDay()->format('Y-m-d H:i:s'))->get();
        foreach ($submissions as $submission) {
            $verificators = Verificator::where('submission_id', $submission->id)->where('final_status', 'pending')->get();
            foreach ($verificators as $verificator) {
                $whatsappHelper->verificator_final_status_not_changed($submission, $verificator);
                sleep(5);
            }
        }
    }
}
