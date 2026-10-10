<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Jobs\DeleteFile;
use App\Jobs\UploadUpdateFinalStepFile;
use App\Models\ActivityUserLog;
use App\Models\FinalStepSubmissionFile;
use App\Models\TemporaryFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FinalStepController extends Controller
{
    public function index()
    {
        $auth_user = User::where('id', auth()->user()->id)->where('role', User::ROLE_MAHASISWA)->firstOrFail();
        $final_step_submission_file = FinalStepSubmissionFile::where('mahasiswa_id', $auth_user->id)->first();

        if (!$final_step_submission_file) {
            $final_step_submission_file = FinalStepSubmissionFile::create([
                'mahasiswa_id' => $auth_user->id,
            ]);
        }

        return view('panel.mahasiswa.final-step', compact(
            'auth_user',
            'final_step_submission_file'
        ));
    }

    public function update(Request $request)
    {
        $student = User::where('id', auth()->user()->id)->firstOrFail();
        $final_step_submission_file = FinalStepSubmissionFile::where('mahasiswa_id', $student->id)->first();

        // final_thesis_file
        $request_required_file_id = str_replace('"', '', $request->input('final_thesis_file'));
        if ($request_required_file_id) {
            $student_final_thesis_file_name = $final_step_submission_file->final_thesis_file_name;
            $student_final_thesis_file_url = $final_step_submission_file->final_thesis_file_url;
            $student_final_thesis_file_status = $final_step_submission_file->final_thesis_file_status;

            if ($student_final_thesis_file_url && $student_final_thesis_file_status === 'approved') {
                return redirect()->back()->with('failed', 'Berkas skripsi final yang sudah disetujui tidak bisa diubah, jika ingin mengubah harap hubungi admin / tata usaha');
            }

            if ($student_final_thesis_file_url !== null && str_contains($student_final_thesis_file_url, 'drive.google.com')) {
                $deleteFileQueue = new DeleteFile($student_final_thesis_file_name);
                dispatch($deleteFileQueue);
            }

            $temporaryFile = TemporaryFile::where('folder', $request_required_file_id)->first();

            $url = $student_final_thesis_file_url;
            $file_name = $student_final_thesis_file_name;

            if ($temporaryFile) {
                // move storage from tmp to submissions
                Storage::move('public/submissions/tmp/'.$temporaryFile->folder.'/'.$temporaryFile->filename, 'public/submissions/'.$temporaryFile->filename);

                $url = asset('storage/submissions/'.$temporaryFile->filename);
                $file_name = $temporaryFile->filename;
            }

            $final_step_submission_file->update([
                'final_thesis_file_name' => $file_name,
                'final_thesis_file_url' => $url,
                'final_thesis_file_status' => 'pending',
                'final_thesis_file_upload_at' => now(),
            ]);

            if ($temporaryFile) {
                // do queue jobs to upload final step file to google drive
                $uploadUpdateFile = new UploadUpdateFinalStepFile($final_step_submission_file->id, FinalStepSubmissionFile::FINAL_STEP_FILE_TYPE_NAME_FINAL_THESIS);
                dispatch($uploadUpdateFile);

                $temporaryFile->delete();
            }
        }

        // validity_sheet_file
        $validity_sheet_id = str_replace('"', '', $request->input('validity_sheet_file'));
        if ($validity_sheet_id) {
            $student_validity_sheet_file_name = $final_step_submission_file->validity_sheet_file_name;
            $student_validity_sheet_file_url = $final_step_submission_file->validity_sheet_file_url;
            $student_validity_sheet_file_status = $final_step_submission_file->validity_sheet_file_status;

            if ($student_validity_sheet_file_url && $student_validity_sheet_file_status === 'approved') {
                return redirect()->back()->with('failed', 'Berkas skripsi final yang sudah disetujui tidak bisa diubah, jika ingin mengubah harap hubungi admin / tata usaha');
            }

            if ($student_validity_sheet_file_url !== null && str_contains($student_validity_sheet_file_url, 'drive.google.com')) {
                $deleteFileQueue = new DeleteFile($student_validity_sheet_file_name);
                dispatch($deleteFileQueue);
            }

            $temporaryFile = TemporaryFile::where('folder', $validity_sheet_id)->first();

            $url = $student_validity_sheet_file_url;
            $file_name = $student_validity_sheet_file_name;

            if ($temporaryFile) {
                // move storage from tmp to submissions
                Storage::move('public/submissions/tmp/'.$temporaryFile->folder.'/'.$temporaryFile->filename, 'public/submissions/'.$temporaryFile->filename);

                $url = asset('storage/submissions/'.$temporaryFile->filename);
                $file_name = $temporaryFile->filename;
            }

            $final_step_submission_file->update([
                'validity_sheet_file_name' => $file_name,
                'validity_sheet_file_url' => $url,
                'validity_sheet_file_status' => 'pending',
                'validity_sheet_file_upload_at' => now(),
            ]);

            if ($temporaryFile) {
                // do queue jobs to upload final step file to google drive
                $uploadUpdateFile = new UploadUpdateFinalStepFile($final_step_submission_file->id, FinalStepSubmissionFile::FINAL_STEP_FILE_TYPE_NAME_VALIDITY_SHEET);
                dispatch($uploadUpdateFile);

                $temporaryFile->delete();
            }
        }

        $details = 'Memperbarui berkas tahapan akhir';

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        return redirect()->back()->with('success', 'Berhasil perbarui berkas tahapan akhir');
    }

    public function upload(Request $request)
    {
        $student = User::where('id', auth()->user()->id)->firstOrFail();

        // final_thesis_file
        if ($request->hasFile('final_thesis_file')) {
            if ($student->final_thesis_status === 'approved') {
                return redirect()->back()->with('failed', 'Berkas skripsi final yang sudah disetujui tidak bisa diubah, jika ingin mengubah harap hubungi admin / Bagian Administrasi');
            }

            $folder = uniqid().'-'.now()->timestamp;
            $file_name = $student->unique_id.'-'.Str::slug($student->name).'-'.'final-thesis-file'.'-'.uniqid().'.'.$request->file('final_thesis_file')->getClientOriginalExtension();
            $request->file('final_thesis_file')->storeAs('submissions/tmp/'.$folder, $file_name, 'public');

            TemporaryFile::create([
                'folder' => $folder,
                'filename' => $file_name,
            ]);

            return response()->json($folder);
        }

        // validity_sheet_file
        if ($request->hasFile('validity_sheet_file')) {
            if ($student->validity_sheet_status === 'approved') {
                return redirect()->back()->with('failed', 'Lembar pengesahan yang sudah disetujui tidak bisa diubah, jika ingin mengubah harap hubungi admin / Bagian Administrasi');
            }

            $folder = uniqid().'-'.now()->timestamp;
            $file_name = $student->unique_id.'-'.Str::slug($student->name).'-'.'validity-sheet'.'-'.uniqid().'.'.$request->file('validity_sheet_file')->getClientOriginalExtension();
            $request->file('validity_sheet_file')->storeAs('submissions/tmp/'.$folder, $file_name, 'public');

            TemporaryFile::create([
                'folder' => $folder,
                'filename' => $file_name,
            ]);

            return response()->json($folder);
        }

        return response()->json(['error' => 'Tidak ada file yang diupload'], 422);
    }
}
