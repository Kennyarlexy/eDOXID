<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DeleteFile;
use App\Jobs\UploadUpdateFinalStepFile;
use App\Models\ActivityUserLog;
use App\Models\FinalStepSubmissionFile;
use App\Models\TemporaryFile;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FinalStepController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->cari;
        $angkatan = $request->angkatan;

        $sidang_akhir_type = Type::where('name', Type::NAME_SIDANG_AKHIR)->first();

        $q_students = User::query();

        $q_students->whereHas('submissions', function ($q) use ($sidang_akhir_type, $search) {
            $q->where('type_id', $sidang_akhir_type->id);

            $q->when($search, function ($q_submissions) use ($search) {
                $field = ['title', 'student.name', 'student.unique_id', 'skill_group.name'];

                return $q_submissions->where(function ($q_submissions) use ($search, $field) {
                    foreach ($field as $f) {
                        $separated_fields = explode('.', $f);
                        if (count($separated_fields) > 1) {
                            $q_submissions->orWhereHas($separated_fields[0], function ($q_submissions) use ($separated_fields, $search) {
                                $q_submissions->where($separated_fields[1], 'like', "%{$search}%");
                            });
                        } else {
                            $q_submissions->orWhere($f, 'like', "%{$search}%");
                        }
                    }
                });
            });
        })->where('role', User::ROLE_MAHASISWA);

        $q_students->when($angkatan, function ($q) use ($angkatan) {
            $q->where('unique_id', 'LIKE', "D10__{$angkatan}%");
        });

        $students = $q_students->paginate(15);

        return view('panel.admin.final-step.index', compact(
            'students',
            'sidang_akhir_type'
        ));
    }

    public function show($student_id)
    {
        $mahasiswa = User::where('role', User::ROLE_MAHASISWA)->where('id', $student_id)->firstOrFail();
        $final_step_submission_file = FinalStepSubmissionFile::where('mahasiswa_id', $student_id)->first();

        if (!$final_step_submission_file) {
            $final_step_submission_file = FinalStepSubmissionFile::create([
                'student_id' => $student_id,
            ]);
        }

        return view('panel.admin.final-step.show', compact(
            'mahasiswa',
            'final_step_submission_file'
        ));
    }

    public function update(Request $request, $student_id)
    {
        $request->validate([
            'final_thesis_file_status' => 'required|in:pending,approved,rejected',
            'validity_sheet_file_status' => 'required|in:pending,approved,rejected',
        ]);

        $student = User::where('id', $student_id)->firstOrFail();
        $final_step_submission_file = FinalStepSubmissionFile::where('mahasiswa_id', $student->id)->first();

        $final_thesis_file_name = $final_step_submission_file->final_thesis_file_name;
        $final_thesis_file_url = $final_step_submission_file->final_thesis_file_url;
        $final_thesis_file_status = $request->final_thesis_file_status;
        $final_thesis_file_upload_at = $final_step_submission_file->final_thesis_file_upload_at;

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

            $final_thesis_file_name = $file_name;
            $final_thesis_file_url = $url;
            $final_thesis_file_upload_at = now();

            if ($temporaryFile) {
                // do queue jobs to upload final step file to google drive
                $uploadUpdateFile = new UploadUpdateFinalStepFile($final_step_submission_file->id, FinalStepSubmissionFile::FINAL_STEP_FILE_TYPE_NAME_FINAL_THESIS);
                dispatch($uploadUpdateFile);

                $temporaryFile->delete();
            }
        }

        $validity_sheet_file_name = $final_step_submission_file->validity_sheet_file_name;
        $validity_sheet_file_url = $final_step_submission_file->validity_sheet_file_url;
        $validity_sheet_file_status = $request->validity_sheet_file_status;
        $validity_sheet_file_upload_at = $final_step_submission_file->validity_sheet_file_upload_at;

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

            $validity_sheet_file_name = $file_name;
            $validity_sheet_file_url = $url;
            $validity_sheet_file_upload_at = now();

            if ($temporaryFile) {
                // do queue jobs to upload final step file to google drive
                $uploadUpdateFile = new UploadUpdateFinalStepFile($final_step_submission_file->id, FinalStepSubmissionFile::FINAL_STEP_FILE_TYPE_NAME_VALIDITY_SHEET);
                dispatch($uploadUpdateFile);

                $temporaryFile->delete();
            }
        }

        $final_step_submission_file->update([
            'final_thesis_file_name' => $final_thesis_file_name,
            'final_thesis_file_url' => $final_thesis_file_url,
            'final_thesis_file_status' => $final_thesis_file_status,
            'final_thesis_file_upload_at' => $final_thesis_file_upload_at,
            'validity_sheet_file_name' => $validity_sheet_file_name,
            'validity_sheet_file_url' => $validity_sheet_file_url,
            'validity_sheet_file_status' => $validity_sheet_file_status,
            'validity_sheet_file_upload_at' => $validity_sheet_file_upload_at,
        ]);

        $details = 'Memperbarui berkas tahapan akhir mahasiswa atas nama: '.$student->name;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        return redirect()->back()->with('success', 'Berhasil perbarui berkas tahapan akhir');
    }

    public function upload(Request $request, $student_id)
    {
        $student = User::where('id', $student_id)->firstOrFail();

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
