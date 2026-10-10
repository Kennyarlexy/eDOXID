<?php

namespace App\Http\Controllers\BagianAdministrasi;

use App\Http\Controllers\Controller;
use App\Models\ActivityUserLog;
use App\Models\FinalStepSubmissionFile;
use App\Models\Submission;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;

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

            $q->where('status', Submission::STATUS_APPROVED);
        })->where('role', User::ROLE_MAHASISWA);

        $q_students->when($angkatan, function ($q) use ($angkatan) {
            $q->where('unique_id', 'LIKE', "D10__{$angkatan}%");
        });

        $students = $q_students->paginate(15);

        return view('panel.bagian-administrasi.final-step.index', compact(
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

        return view('panel.bagian-administrasi.final-step.show', compact(
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

        $final_thesis_file_status = $request->final_thesis_file_status;
        $validity_sheet_file_status = $request->validity_sheet_file_status;

        $final_step_submission_file->update([
            'final_thesis_file_status' => $final_thesis_file_status,
            'validity_sheet_file_status' => $validity_sheet_file_status,
        ]);

        $details = 'Memperbarui berkas tahapan akhir mahasiswa atas nama: '.$student->name;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        return redirect()->back()->with('success', 'Berhasil perbarui berkas tahapan akhir');
    }
}
