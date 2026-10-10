<?php

namespace App\Jobs;

use App\Helpers\GoogleDriveHelper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DeleteFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $submission_required_filename;
    protected $googleDriveHelper;

    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($submission_required_filename)
    {
        $this->submission_required_filename = $submission_required_filename;
        $this->googleDriveHelper = new GoogleDriveHelper();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
         // delete the file from google drive
         $this->googleDriveHelper->deleteFile($this->submission_required_filename);
    }
}
