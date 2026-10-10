<?php

namespace App\Http\Controllers\Api;

use App\Helpers\GoogleDriveHelper;
use App\Http\Controllers\Controller;
use App\Jobs\SetEmailForAccessSubmissionFile;
use App\Models\ActivitySubmissionLog;
use App\Models\ActivityUserLog;
use App\Models\Format;
use App\Models\MahasiswaTypeAccessPermission;
use App\Models\Notification;
use App\Models\SkillGroup;
use App\Models\Submission;
use App\Models\SubmissionRequiredFile;
use App\Models\Type;
use App\Models\User;
use App\Models\Verificator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubmissionController extends Controller
{
    private $googleDriveHelper;

    public function __construct()
    {
        $this->googleDriveHelper = new GoogleDriveHelper();
    }

    public function syncSubmissionData(Request $request)
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $rules = [
            'id' => 'required',
            'judul' => 'required',
            'mahasiswa' => 'required|array',
            'mahasiswa.nim' => 'required|string',
            'mahasiswa.nama' => 'required|string',
            'semester' => 'required',
            'tahun' => 'required',
            'tanggalKeputusan' => 'required',
            'waktuKeputusan' => 'required',
            'token' => 'required|string',
            'tipe_pengajuan' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $type = null;
        if ($request->tipe_pengajuan == 'SidHas') {
            $type = Type::where('name', 'Sidang Hasil')->first();
        } elseif ($request->tipe_pengajuan == 'Sidang') {
            $type = Type::where('name', 'Sidang Akhir')->first();
        } elseif ($request->tipe_pengajuan == 'Outline') {
            $type = Type::where('name', 'Proposal')->first();
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'tipe pengajuan tidak ditemukan',
            ], 400);
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $tokenFromEnv = getenv('API_TOKEN');
        $tokenFromRequest = $request->token ? $request->token : '';

        if ($tokenFromEnv != $tokenFromRequest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token',
            ], 400);
        }

        $mahasiswa_request = $request->mahasiswa;

        // check student account in this system
        $student = User::where('unique_id', $mahasiswa_request['nim'])->first();
        if (!$student) {
            $student = User::create([
                'unique_id' => $mahasiswa_request['nim'],
                'name' => $mahasiswa_request['nama'],
                'email' => $mahasiswa_request['email'] ? $mahasiswa_request['email'] : '',
                'password' => Hash::make(Str::random(8)),
                'role' => User::ROLE_MAHASISWA,
                'profile_image' => $mahasiswa_request['urlFoto'] ? $mahasiswa_request['urlFoto'] : '',
            ]);

            ActivityUserLog::create([
                'user_id' => $student->id,
                'details' => 'Akun dibuat berdasarkan data SPOTA',
            ]);
        }

        // get skill group
        $skill_group = null;
        if ($request->kelompokKeahlian) {
            $skill_group = SkillGroup::where('name', $request->kelompokKeahlian)->first();
            if (!$skill_group) {
                $skill_group = SkillGroup::create([
                    'name' => $request->kelompokKeahlian,
                ]);
            }
        }

        // check submission in this system
        $submission = Submission::where('student_id', $student->id)->where('type_id', $type->id)->where('spota_submission_id', $request->id)->first();

        if (!$submission) {
            return $this->storeSubmission($request, $student, $type, $skill_group);
        } else {
            return $this->updateSubmission($request, $submission, $skill_group);
        }
    }

    public function storeSubmission(Request $request, $student, $type, $skill_group)
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $format = Format::where('type_id', $type->id)->where('is_primary', true)->first();
        if (!$format) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format tidak ditemukan, silahkan hubungi admin',
            ], 400);
        }

        $rules = [];

        if ($format->is_pembimbing_required) {
            $rules = [
                'pembimbings' => 'required|array|min:1',
                'pembimbings.*.nama' => 'required|string',
                'pembimbings.*.nip' => 'required|string',
            ];
        }

        if ($format->is_penguji_required) {
            $rules = [
                'pengujis' => 'required|array|min:1',
                'pengujis.*.nama' => 'required|string',
                'pengujis.*.nip' => 'required|string',
            ];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $submission = Submission::create([
            'spota_submission_id' => $request->id,
            'title' => $request->judul,
            'description' => '',
            'type_id' => $type->id,
            'skill_group_id' => $skill_group ? $skill_group->id : null,
            'format_id' => $format->id,
            'academic_year' => $request->tahun,
            'semester' => $request->semester,
            'student_id' => $student->id,
        ]);

        foreach ($format->required_files as $required_file) {
            SubmissionRequiredFile::create([
                'submission_id' => $submission->id,
                'required_file_id' => $required_file->id,
            ]);
        }

        if ($submission->format->is_pembimbing_required) {
            $trial_status = Verificator::STATUS_PENDING;
            if ($submission->format->is_pembimbing_need_to_change_trial_status == false) {
                $trial_status = Verificator::STATUS_APPROVED;
            }

            foreach ($request->pembimbings as $request_pembimbing) {
                $pembimbing = User::where('unique_id', $request_pembimbing['nip'])->where('role', User::ROLE_DOSEN)->first();
                if (!$pembimbing) {
                    $pembimbing = User::create([
                        'unique_id' => $request_pembimbing['nip'],
                        'name' => $request_pembimbing['nama'],
                        'email' => $request_pembimbing['email'] ? $request_pembimbing['email'] : '',
                        'password' => Hash::make(Str::random(8)),
                        'phone_number' => $request_pembimbing['hp'] ? $request_pembimbing['hp'] : '',
                        'profile_image' => $request_pembimbing['urlFoto'] ? $request_pembimbing['urlFoto'] : '',
                        'role' => User::ROLE_DOSEN,
                    ]);
                }

                $verificator = Verificator::create([
                    'type' => Verificator::TYPE_PEMBIMBING,
                    'submission_id' => $submission->id,
                    'user_id' => $pembimbing->id,
                    'status' => $trial_status,
                    'is_need_to_change_trial_status' => $submission->format->is_pembimbing_need_to_change_trial_status,
                ]);

                // notification to verificator pembimbing1
                Notification::create([
                    'action' => 'Menjadi pembimbing pada pengajuan dengan id '.$submission->id,
                    'submission_id' => $submission->id,
                    'from_user_id' => $admin->id,
                    'to_user_id' => $verificator->user_id,
                    'url' => route('panel.dosen.pengajuan.required_files.show', ['type_slug' => $submission->type->slug, 'id' => $submission->id]),
                ]);
            }
        }

        if ($submission->format->is_penguji_required) {
            $trial_status = Verificator::STATUS_PENDING;
            if ($submission->format->is_penguji_need_to_change_trial_status == false) {
                $trial_status = Verificator::STATUS_APPROVED;
            }

            foreach ($request->pengujis as $request_penguji) {
                $penguji = User::where('unique_id', $request_penguji['nip'])->where('role', User::ROLE_DOSEN)->first();
                if (!$penguji) {
                    $penguji = User::create([
                        'unique_id' => $request_penguji['nip'],
                        'name' => $request_penguji['nama'],
                        'email' => $request_penguji['email'] ? $request_penguji['email'] : '',
                        'password' => Hash::make(Str::random(8)),
                        'phone_number' => $request_penguji['hp'] ? $request_penguji['hp'] : '',
                        'role' => User::ROLE_DOSEN,
                        'profile_image' => $request_penguji['urlFoto'] ? $request_penguji['urlFoto'] : '',
                    ]);
                }

                $verificator = Verificator::create([
                    'type' => Verificator::TYPE_PENGUJI,
                    'submission_id' => $submission->id,
                    'user_id' => $penguji->id,
                    'status' => $trial_status,
                    'is_need_to_change_trial_status' => $submission->format->is_penguji_need_to_change_trial_status,
                ]);

                // notification to verificator
                Notification::create([
                    'action' => 'Menjadi penguji pada pengajuan dengan id '.$submission->id,
                    'submission_id' => $submission->id,
                    'from_user_id' => $admin->id,
                    'to_user_id' => $verificator->user_id,
                    'url' => route('panel.dosen.pengajuan.required_files.show', ['type_slug' => $submission->type->slug, 'id' => $submission->id]),
                ]);
            }
        }

        // notification to all admin where the status was active
        $admins = User::where('role', User::ROLE_ADMIN)->where('status', 'active')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'action' => 'Pengajuan ditambah dari sistem SPOTA',
                'submission_id' => $submission->id,
                'from_user_id' => $admin->id,
                'to_user_id' => $admin->id,
                'url' => route('panel.admin.pengajuan.show', ['type_slug' => $type->slug, 'id' => $submission->id]),
            ]);
        }

        // notification to all Bagian Administrasi
        $bagianAdministrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->where('status', 'active')->get();
        foreach ($bagianAdministrasis as $bagianAdministrasi) {
            Notification::create([
                'action' => 'Pengajuan ditambah dari sistem SPOTA',
                'submission_id' => $submission->id,
                'from_user_id' => $admin->id,
                'to_user_id' => $bagianAdministrasi->id,
                'url' => route('panel.bagian-administrasi.pengajuan.required_files.show', ['type_slug' => $type->slug, 'id' => $submission->id]),
            ]);
        }

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => 'Pengajuan baru ditambahkan berdasarkan request sistem SPOTA',
            'user_id' => $admin->id,
        ]);

        $student_type_access_permission = MahasiswaTypeAccessPermission::where('mahasiswa_id', $student->id)->where('type_id', $type->id)->first();
        if (!$student_type_access_permission) {
            MahasiswaTypeAccessPermission::create([
                'mahasiswa_id' => $submission->student_id,
                'type_id' => $type->id,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menambah data pengajuan',
        ], 200);
    }

    public function updateSubmission(Request $request, $submission, $skill_group)
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $type = $submission->type;
        $format = Format::where('type_id', $type->id)->where('is_primary', true)->first();
        if (!$format) {
            return response()->json([
                'status' => 'error',
                'message' => 'Format tidak ditemukan, silahkan hubungi admin',
            ], 400);
        }

        $rules = [];

        if ($format->is_pembimbing_required) {
            $rules = [
                'pembimbings' => 'required|array|min:1',
                'pembimbings.*.nama' => 'required|string',
                'pembimbings.*.nip' => 'required|string',
            ];
        }

        if ($format->is_penguji_required) {
            $rules = [
                'pengujis' => 'required|array|min:1',
                'pengujis.*.nama' => 'required|string',
                'pengujis.*.nip' => 'required|string',
            ];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $submission->update([
            'title' => $request->judul,
            'skill_group_id' => $skill_group ? $skill_group->id : null,
        ]);

        if ($request->keputusan && $request->keputusan == 2) {
            $submission->update([
                'status' => Submission::STATUS_REJECTED,
            ]);
        }

        $emails = [];

        if ($submission->format->is_pembimbing_required) {
            $submission_verificator_pembimbings = Verificator::where('submission_id', $submission->id)->where('type', 'Pembimbing')->get();
            $request_pembimbings = $request->pembimbings;

            $i = 0;

            $trial_status = Verificator::STATUS_PENDING;
            if ($submission->format->is_pembimbing_need_to_change_trial_status == false) {
                $trial_status = Verificator::STATUS_APPROVED;
            }

            foreach ($request_pembimbings as $request_pembimbing) {
                $verificator = null;
                if (!empty($submission_verificator_pembimbings[$i])) {
                    $verificator = Verificator::where('id', $submission_verificator_pembimbings[$i]->id)->first();
                }

                if ($verificator !== null) {
                    if ($verificator->user->unique_id == $request_pembimbing['nip']) {
                        ++$i;
                        $emails[] = $verificator->user->email;
                        continue;
                    }

                    // delete current verificator
                    $verificator->delete();
                }

                $pembimbing = User::where('unique_id', $request_pembimbing['nip'])->where('role', User::ROLE_DOSEN)->first();
                if (!$pembimbing) {
                    $pembimbing = User::create([
                        'unique_id' => $request_pembimbing['nip'],
                        'name' => $request_pembimbing['nama'],
                        'email' => $request_pembimbing['email'] ? $request_pembimbing['email'] : '',
                        'password' => Hash::make(Str::random(8)),
                        'phone_number' => $request_pembimbing['hp'] ? $request_pembimbing['hp'] : '',
                        'role' => User::ROLE_DOSEN,
                    ]);
                }

                $verificator = Verificator::create([
                    'type' => Verificator::TYPE_PEMBIMBING,
                    'submission_id' => $submission->id,
                    'user_id' => $pembimbing->id,
                    'status' => $trial_status,
                    'is_need_to_change_trial_status' => $submission->format->is_pembimbing_need_to_change_trial_status,
                ]);

                // notification to verificator pembimbing
                Notification::create([
                    'action' => 'Menjadi pembimbing pada pengajuan dengan id '.$submission->id,
                    'submission_id' => $submission->id,
                    'from_user_id' => $admin->id,
                    'to_user_id' => $verificator->user_id,
                    'url' => route('panel.dosen.pengajuan.required_files.show', ['type_slug' => $submission->type->slug, 'id' => $submission->id]),
                ]);

                ++$i;
                $emails[] = $verificator->user->email;
            }

            if ($submission_verificator_pembimbings->count() > $i) {
                $verificator = Verificator::where('id', $submission_verificator_pembimbings[$i]->id)->first();
                $verificator->delete();

                ++$i;
            }
        }

        if ($submission->format->is_penguji_required) {
            $submission_verificator_pengujis = Verificator::where('submission_id', $submission->id)->where('type', 'Penguji')->get();
            $request_pengujis = $request->pengujis;
            $i = 0;

            $trial_status = Verificator::STATUS_PENDING;
            if ($submission->format->is_penguji_need_to_change_trial_status == false) {
                $trial_status = Verificator::STATUS_APPROVED;
            }

            foreach ($request_pengujis as $request_penguji) {
                $verificator = null;
                if (!empty($submission_verificator_pengujis[$i])) {
                    $verificator = Verificator::where('id', $submission_verificator_pengujis[$i]->id)->first();
                }

                if ($verificator !== null) {
                    if ($verificator->user->unique_id == $request_penguji['nip']) {
                        ++$i;
                        $emails[] = $verificator->user->email;
                        continue;
                    }

                    // delete current verificator
                    $verificator->delete();
                }

                $penguji = User::where('unique_id', $request_penguji['nip'])->where('role', User::ROLE_DOSEN)->first();
                if (!$penguji) {
                    $penguji = User::create([
                        'unique_id' => $request_penguji['nip'],
                        'name' => $request_penguji['nama'],
                        'email' => $request_penguji['email'] ? $request_penguji['email'] : '',
                        'password' => Hash::make(Str::random(8)),
                        'phone_number' => $request_penguji['hp'] ? $request_penguji['hp'] : '',
                        'role' => User::ROLE_DOSEN,
                    ]);
                }

                $verificator = Verificator::create([
                    'type' => Verificator::TYPE_PENGUJI,
                    'submission_id' => $submission->id,
                    'user_id' => $penguji->id,
                    'status' => $trial_status,
                    'is_need_to_change_trial_status' => $submission->format->is_penguji_need_to_change_trial_status,
                ]);

                // notification to verificator penguji
                Notification::create([
                    'action' => 'Menjadi penguji pada pengajuan dengan id '.$submission->id,
                    'submission_id' => $submission->id,
                    'from_user_id' => $admin->id,
                    'to_user_id' => $verificator->user_id,
                    'url' => route('panel.dosen.pengajuan.required_files.show', ['type_slug' => $submission->type->slug, 'id' => $submission->id]),
                ]);

                ++$i;
                $emails[] = $verificator->user->email;
            }

            if ($submission_verificator_pengujis->count() > $i) {
                $verificator = Verificator::where('id', $submission_verificator_pengujis[$i]->id)->first();
                $verificator->delete();

                ++$i;
            }
        }

        // get all required files that has uploaded to google drive and has permission of is_verificator_can_edit
        $submission_required_files_can_edit = SubmissionRequiredFile::where('submission_id', $submission->id)->whereHas('required_file', function ($q) {
            $q->where('is_verificator_can_edit', true);
        })->get();

        if (getenv('APP_ENV') !== 'production') {
            $emails = [
                'willyferry@student.untan.ac.id',
            ];
        }

        // disabled this feature becaseu need to add it to queue in next version

        foreach ($submission_required_files_can_edit as $submission_required_file) {
            if ($submission_required_file->url !== null && str_contains($submission_required_file->url, 'drive.google.com')) {
                // do queue jobs to set permissions
                $setEmailForAccessSubmissionFile = new SetEmailForAccessSubmissionFile($submission_required_file->filename, $emails);
                dispatch($setEmailForAccessSubmissionFile);
            }
        }

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => 'Pengajuan diperbarui berdasarkan request sistem SPOTA',
            'user_id' => $admin->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil memperbarui data pengajuan',
        ], 200);
    }

    public function updateDetailSubmission(Request $request)
    {
        $admin = User::where('role', User::ROLE_ADMIN)->first();

        $rules = [
            'id' => 'required',
            'judul' => 'required',
            'kelompok_keahlian' => 'required',
            'token' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 400);
        }

        $tokenFromEnv = getenv('API_TOKEN');
        $tokenFromRequest = $request->token ? $request->token : '';

        if ($tokenFromEnv != $tokenFromRequest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token',
            ], 400);
        }

        $submissions = Submission::where('spota_submission_id', $request->id)->get();
        if ($submissions->count() == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengajuan tidak ditemukan',
            ], 404);
        }

        $skill_group = SkillGroup::where('name', $request->kelompok_keahlian)->first();
        if ($skill_group === null) {
            $skill_group = SkillGroup::create([
                'name' => $request->kelompok_keahlian,
            ]);
        }

        foreach ($submissions as $submission) {
            $submission->update([
                'title' => $request->judul,
                'skill_group_id' => $skill_group->id,
            ]);

            ActivitySubmissionLog::create([
                'submission_id' => $submission->id,
                'details' => 'Informasi pengajuan diperbarui berdasarkan request dari sistem SPOTA',
                'user_id' => $admin->id,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil memperbarui data informasi pengajuan',
        ], 200);
    }
}
