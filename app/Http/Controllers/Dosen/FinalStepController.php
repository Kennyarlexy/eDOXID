<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
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

        $auth_user = User::where('id', auth()->user()->id)->firstOrFail();
        $sidang_akhir_type = Type::where('name', Type::NAME_SIDANG_AKHIR)->first();

        $q_students = User::query();

        $q_students->whereHas('submissions', function ($q) use ($sidang_akhir_type, $auth_user, $search) {
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

            if ($auth_user->position === null) {
                $q->whereHas('verificators', function ($q2) use ($auth_user) {
                    $q2->where('user_id', $auth_user->id);
                });
            }

            $q->where('status', Submission::STATUS_APPROVED);
        })->where('role', User::ROLE_MAHASISWA);

        $q_students->when($angkatan, function ($q) use ($angkatan) {
            $q->where('unique_id', 'LIKE', "D10__{$angkatan}%");
        });

        $students = $q_students->paginate(15);

        return view('panel.dosen.final-step.index', compact(
            'students',
            'sidang_akhir_type'
        ));
    }

    public function show($student_id)
    {
        $auth_user = User::where('id', auth()->user()->id)->firstOrFail();
        $sidang_akhir_type = Type::where('name', Type::NAME_SIDANG_AKHIR)->first();

        $mahasiswa = User::whereHas('submissions', function ($q) use ($sidang_akhir_type, $auth_user) {
            $q->where('type_id', $sidang_akhir_type->id);
            if ($auth_user->position === null) {
                $q->whereHas('verificators', function ($q2) use ($auth_user) {
                    $q2->where('user_id', $auth_user->id);
                });
            }
        })->where('role', User::ROLE_MAHASISWA)->where('id', $student_id)->firstOrFail();

        $final_step_submission_file = FinalStepSubmissionFile::where('mahasiswa_id', $student_id)->first();

        if (!$final_step_submission_file) {
            $final_step_submission_file = FinalStepSubmissionFile::create([
                'student_id' => $student_id,
            ]);
        }

        return view('panel.dosen.final-step.show', compact(
            'mahasiswa',
            'final_step_submission_file'
        ));
    }
}
