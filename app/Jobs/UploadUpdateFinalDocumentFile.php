<?php

namespace App\Jobs;

use App\Helpers\GoogleDriveHelper;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UploadUpdateFinalDocumentFile implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $submission;
    protected $googleDriveHelper;

    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($submission)
    {
        $this->submission = $submission;
        $this->googleDriveHelper = new GoogleDriveHelper();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $submission = $this->submission;
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

        // cek apakah subfolder exists
        $subFolderExists = $this->googleDriveHelper->searchFolder($subFolderName);

        if (!is_array($subFolderExists) && $subFolderExists->getData()->status === 404) {
            $this->googleDriveHelper->createNewSubFolder($parentFolderName, $subFolderName);
        }

        // upload file to googledrive
        $uploadFile = $this->googleDriveHelper->uploadFileInSubFolder($this->submission->final_document_url, $this->submission->final_document_name, $subFolderName);

        if (!is_array($uploadFile) && $uploadFile->getData()->status === 404) {
            // retry to do queue job to upload file to googledrive
            $uploadUpdateFinalDocumentFile = new UploadUpdateFinalDocumentFile($this->submission);
            dispatch($uploadUpdateFinalDocumentFile);

            $response = [
                'status' => 500,
                'message' => 'File failed to upload to Google Drive',
            ];

            return response()->json($response);
        } else {
            $this->googleDriveHelper->setPermissionReaderViewing($this->submission->final_document_name);

            Storage::disk('public')->delete('submissions/'.$this->submission->final_document_name);
            $this->submission->update([
                'final_document_url' => 'https://drive.google.com/file/d/'.$uploadFile['basename'],
            ]);

            $response = [
                'status' => 200,
                'message' => 'File uploaded to Google Drive',
            ];

            return response()->json($response);
        }
    }
}
