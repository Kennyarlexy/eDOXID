<?php

namespace App\Jobs;

use App\Helpers\GoogleDriveHelper;
use App\Models\FinalStepSubmissionFile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadUpdateFinalStepFile implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $final_step_submission_file_id;
    protected $final_step_file_type_name;

    protected $googleDriveHelper;

    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($final_step_submission_file_id, $final_step_file_type_name)
    {
        $this->final_step_submission_file_id = $final_step_submission_file_id;
        $this->final_step_file_type_name = $final_step_file_type_name;
        $this->googleDriveHelper = new GoogleDriveHelper();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $final_step_submission_file = FinalStepSubmissionFile::where('id', $this->final_step_submission_file_id)->first();
        if (!$final_step_submission_file) {
            return;
        }

        $student = User::where('id', $final_step_submission_file->mahasiswa_id)->first();

        if ($student->google_drive_folder_id === null) {
            // create new folder by student nim - name
            $createFolder = $this->googleDriveHelper->createNewFolder("$student->unique_id - $student->name");

            // update the google drive folder path id to student
            $student->google_drive_folder_id = $createFolder['path'];
            $student->save();
        }

        // buat folder baru jika pengajuan pada tipe ini dilakukan pertama kali
        $parentFolderName = $student->unique_id.' - '.$student->name;
        $subFolderName = $student->unique_id.' - '.$student->name.' - Tahapan Akhir';

        // cek apakah subfolder exists
        $subFolderExists = $this->googleDriveHelper->searchFolder($subFolderName);

        if (!is_array($subFolderExists) && $subFolderExists->getData()->status === 404) {
            $this->googleDriveHelper->createNewSubFolder($parentFolderName, $subFolderName);
        }

        $file_name = $final_step_submission_file->final_thesis_file_name;
        $file_url = $final_step_submission_file->final_thesis_file_url;

        if ($this->final_step_file_type_name == FinalStepSubmissionFile::FINAL_STEP_FILE_TYPE_NAME_VALIDITY_SHEET) {
            $file_name = $final_step_submission_file->validity_sheet_file_name;
            $file_url = $final_step_submission_file->validity_sheet_file_url;
        }

        // upload file to googledrive
        $uploadFile = $this->googleDriveHelper->uploadFileInSubFolder($file_url, $file_name, $subFolderName);

        if (!is_array($uploadFile) && $uploadFile->getData()->status === 404) {
            // retry to do queue job to upload file to googledrive
            $uploadUpdateFile = new UploadUpdateFinalStepFile($this->final_step_submission_file_id, $this->final_step_file_type_name);
            dispatch($uploadUpdateFile);

            $response = [
                'status' => 500,
                'message' => 'File failed to upload to Google Drive',
                'final_step_submission_file_id' => $this->final_step_submission_file_id,
                'final_step_file_type_name' => $this->final_step_file_type_name,
                'detail' => $uploadFile,
            ];

            Log::error($response);

            return response()->json($response);
        } else {
            $this->googleDriveHelper->setPermissionReaderViewing($file_name);
            Storage::disk('public')->delete('submissions/'.$file_name);

            if ($this->final_step_file_type_name == FinalStepSubmissionFile::FINAL_STEP_FILE_TYPE_NAME_VALIDITY_SHEET) {
                $final_step_submission_file->update([
                    'validity_sheet_file_url' => 'https://drive.google.com/file/d/'.$uploadFile['basename'],
                ]);
            } else {
                $final_step_submission_file->update([
                    'final_thesis_file_url' => 'https://drive.google.com/file/d/'.$uploadFile['basename'],
                ]);
            }

            $response = [
                'status' => 200,
                'message' => 'File uploaded to Google Drive',
            ];

            return response()->json($response);
        }
    }
}
