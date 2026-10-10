<?php

namespace App\Jobs;

use App\Helpers\GoogleDriveHelper;
use App\Models\Submission;
use App\Models\User;
use App\Models\Verificator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadUpdateFile implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $submission_required_file;
    protected $googleDriveHelper;

    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($submission_required_file)
    {
        $this->submission_required_file = $submission_required_file;
        $this->googleDriveHelper = new GoogleDriveHelper();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug('start sync drive');
        $submission = $this->submission_required_file->submission;
        $type = $submission->type;
        $student = User::where('id', $submission->student_id)->first();

        if ($student->google_drive_folder_id === null) {
            // create new folder by student nim - name
            $createFolder = $this->googleDriveHelper->createNewFolder("$student->unique_id - $student->name");

            // update the google drive folder path id to student
            $student->google_drive_folder_id = $createFolder['path'];
            $student->save();
        }

        // buat folder baru jika pengajuan pada tipe ini dilakukan pertama kali
        $parentFolderName = $student->unique_id.' - '.$student->name;
        $subFolderName = $student->unique_id.' - '.$student->name.' - '.$type->name;

        if (Submission::where('type_id', $type->id)->where('student_id', $student->id)->count() > 1) {
            $all_submissions = Submission::where('type_id', $type->id)->where('student_id', $student->id)->get();
            $index = 1;
            foreach ($all_submissions as $all_submission) {
                if ($all_submission->id == $submission->id) {
                    break;
                }

                ++$index;
            }
            if ($index > 1) {
                $subFolderName = $student->unique_id.' - '.$student->name.' - '.$type->name.'- ke-'.$index;
            }
        }

        Log::debug('check subfolder '.$subFolderName);

        // cek apakah subfolder exists
        $subFolderExists = $this->googleDriveHelper->searchFolder($subFolderName);

        Log::debug('done check subfolder');

        if (!is_array($subFolderExists) && $subFolderExists->getData()->status === 404) {
            $this->googleDriveHelper->createNewSubFolder($parentFolderName, $subFolderName);
        }

        // upload file to googledrive
        $uploadFile = $this->googleDriveHelper->uploadFileInSubFolder($this->submission_required_file->url, $this->submission_required_file->filename, $subFolderName);

        Log::debug('start sync drive student id'.$student->id);

        if (!is_array($uploadFile) && $uploadFile->getData()->status === 404) {
            // retry to do queue job to upload file to googledrive
            $uploadUpdateFile = new UploadUpdateFile($this->submission_required_file);
            dispatch($uploadUpdateFile);

            $response = [
                'status' => 500,
                'message' => 'File failed to upload to Google Drive',
                'submission_id' => $this->submission_required_file->submission_id,
                'submission_required_filename' => $this->submission_required_file->filename,
                'detail' => $uploadFile,
            ];

            Log::error($response);

            return response()->json($response);
        } else {
            if ($this->submission_required_file->required_file->is_verificator_can_edit) {
                $emails = [];
                if (getenv('APP_ENV') === 'production') {
                    $verificators = Verificator::where('submission_id', $this->submission_required_file->submission_id)->get();
                    foreach ($verificators as $verificator) {
                        $verificator_user = User::where('id', $verificator->user_id)->first();
                        // set their email role to editable and notified
                        $emails[] = $verificator_user->email;
                    }

                    // set student email role to editable and notified
                    $emails[] = $student->email;
                    $this->googleDriveHelper->setPermissionEditToAllEmails($this->submission_required_file->filename, $emails);
                } else {
                    $emails = [
                        'willyferry@student.untan.ac.id',
                    ];
                }

                $this->googleDriveHelper->setPermissionEditToAllEmails($this->submission_required_file->filename, $emails);
            } else {
                $this->googleDriveHelper->setPermissionReaderViewing($this->submission_required_file->filename);
            }

            Storage::disk('public')->delete('submissions/'.$this->submission_required_file->filename);
            $this->submission_required_file->update([
                'url' => 'https://drive.google.com/file/d/'.$uploadFile['basename'],
            ]);

            Log::debug('end sync drive'.$uploadFile['basename']);

            $response = [
                'status' => 200,
                'message' => 'File uploaded to Google Drive',
            ];

            return response()->json($response);
        }
    }
}
