<?php

namespace App\Jobs;

use App\Helpers\GoogleDriveHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetEmailForAccessSubmissionFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $submission_required_file_filename;
    protected $emails;
    protected $googleDriveHelper;

    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($submission_required_file_filename, $emails)
    {
        $this->submission_required_file_filename = $submission_required_file_filename;
        $this->emails = $emails;
        $this->googleDriveHelper = new GoogleDriveHelper();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->googleDriveHelper->setPermissionEditToAllEmails($this->submission_required_file_filename, $this->emails);
    }
}
