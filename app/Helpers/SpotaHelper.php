<?php

namespace App\Helpers;

use App\Models\Format;
use App\Models\MahasiswaTypeAccessPermission;
use App\Models\SkillGroup;
use App\Models\SkillGroupLecturer;
use App\Models\Submission;
use App\Models\SubmissionRequiredFile;
use App\Models\Type;
use App\Models\User;
use App\Models\Verificator;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;

class SpotaHelper
{
    public function getAllStudents()
    {
        $client = new Client();
        $req = $client->get('http://spota.untan.ac.id/steven/API/getListMahasiswa.php?withHP&secret=in14d4lahP4ssWordSP0TA!12!');

        $response = $req->getBody()->getContents();
        $datas = json_decode($response, true);

        return $datas['data'];
    }

    public function getAllLecturers()
    {
        $client = new Client();
        $client = new Client();

        $req = $client->get('http://spota.untan.ac.id/steven/API/getListDosen.php?secret=in14d4lahP4ssWordSP0TA!12!');

        $response = $req->getBody()->getContents();
        $datas = json_decode($response, true);

        return $datas['data'];
    }

    public function getAllSKillGroups()
    {
        $client = new Client();

        $req = $client->get('http://spota.untan.ac.id/steven/API/getListKK.php?secret=in14d4lahP4ssWordSP0TA!12!');

        $response = $req->getBody()->getContents();
        $datas = json_decode($response, true);

        return $datas['data'];
    }

    public function getAllSubmissions()
    {
        $client = new Client();
        $req = $client->get('http://spota.untan.ac.id/steven/API/getListDataTAMahasiswa.php?secret=in14d4lahP4ssWordSP0TA!12!');

        $response = $req->getBody()->getContents();
        $datas = json_decode($response, true);

        return $datas['data'];
    }

    public function syncStudentData()
    {
        $spota_students = $this->getAllStudents();
        foreach ($spota_students as $spota_student) {
            $student = User::where('unique_id', $spota_student['nim'])->where('role', User::ROLE_MAHASISWA)->first();
            if (!$student) {
                $student = User::create([
                    'name' => $spota_student['nama'],
                    'unique_id' => $spota_student['nim'],
                    'email' => $spota_student['email'] ? $spota_student['email'] : null,
                    'role' => User::ROLE_MAHASISWA,
                    'password' => Hash::make(rand(100000, 999999)),
                    'profile_image' => $spota_student['urlFoto'] ? $spota_student['urlFoto'] : '',
                    'phone_number' => $spota_student['hp'] ? $spota_student['hp'] : null,
                ]);
            } else {
                $student->update([
                    'name' => $spota_student['nama'],
                    'unique_id' => $spota_student['nim'],
                    'email' => $spota_student['email'] ? $spota_student['email'] : null,
                    'profile_image' => $spota_student['urlFoto'] ? $spota_student['urlFoto'] : '',
                    'phone_number' => $spota_student['hp'] ? $spota_student['hp'] : null,
                ]);
            }
        }
    }

    public function syncLecturerData()
    {
        $spota_lecturers = $this->getAllLecturers();
        foreach ($spota_lecturers as $spota_lecturer) {
            $lecturer = User::where('unique_id', $spota_lecturer['nip'])->where('role', User::ROLE_DOSEN)->first();
            if (!$lecturer) {
                $lecturer = User::create([
                    'name' => $spota_lecturer['nama'],
                    'unique_id' => $spota_lecturer['nip'],
                    'email' => $spota_lecturer['email'] ? $spota_lecturer['email'] : null,
                    'role' => User::ROLE_DOSEN,
                    'password' => Hash::make(rand(100000, 999999)),
                    'phone_number' => $spota_lecturer['hp'] ? $spota_lecturer['hp'] : '',
                    'profile_image' => $spota_lecturer['urlFoto'] ? $spota_lecturer['urlFoto'] : '',
                ]);
            } else {
                $lecturer->update([
                    'name' => $spota_lecturer['nama'],
                    'unique_id' => $spota_lecturer['nip'],
                    'email' => $spota_lecturer['email'] ? $spota_lecturer['email'] : null,
                    'profile_image' => $spota_lecturer['urlFoto'] ? $spota_lecturer['urlFoto'] : '',
                    'phone_number' => $spota_lecturer['hp'] ? $spota_lecturer['hp'] : '',
                ]);
            }
        }
    }

    public function syncSkillGroupData()
    {
        $spota_skill_groups = $this->getAllSKillGroups();
        foreach ($spota_skill_groups as $spota_skill_group) {
            $skill_group = SkillGroup::where('id', $spota_skill_group['id'])->first();
            if (!$skill_group) {
                $skill_group = SkillGroup::create([
                    'id' => $spota_skill_group['id'],
                    'name' => $spota_skill_group['nama'],
                ]);
            } else {
                $skill_group->update([
                    'name' => $spota_skill_group['nama'],
                ]);
            }

            // clear all skill group lecturer
            SkillGroupLecturer::where('skill_group_id', $skill_group->id)->delete();

            // insert it again
            $spota_skill_group_lecturers = $spota_skill_group['anggotaKK'];
            foreach ($spota_skill_group_lecturers as $spota_skill_group_lecturer) {
                $lecturer = User::where('name', $spota_skill_group_lecturer['nama'])->where('role', User::ROLE_DOSEN)->first();
                if ($lecturer) {
                    $position = $spota_skill_group_lecturer['status'] == 'ketua' ? SkillGroupLecturer::KETUA : SkillGroupLecturer::ANGGOTA;

                    SkillGroupLecturer::create([
                        'skill_group_id' => $skill_group->id,
                        'lecturer_id' => $lecturer->id,
                        'position' => $position,
                    ]);
                }
            }
        }
    }

    public function submissionWithoutPenguji()
    {
        $spota_submissions = $this->getAllSubmissions();

        $submissions = [];
        foreach ($spota_submissions as $spota_submission) {
            $pembimbing1 = User::where('unique_id', $spota_submission['pemb1'])->where('role', User::ROLE_DOSEN)->first();
            $pembimbing2 = User::where('unique_id', $spota_submission['pemb2'])->where('role', User::ROLE_DOSEN)->first();
            $penguji1 = User::where('unique_id', $spota_submission['peng1'])->where('role', User::ROLE_DOSEN)->first();
            $penguji2 = User::where('unique_id', $spota_submission['peng2'])->where('role', User::ROLE_DOSEN)->first();
            $mahasiswa = User::where('unique_id', $spota_submission['nim'])->where('role', User::ROLE_MAHASISWA)->first();

            if (count($spota_submission['jadwal']) > 0) {
                $jadwal = $spota_submission['jadwal'][count($spota_submission['jadwal']) - 1];
                if (($jadwal['jenis'] == 'Sidang' || $jadwal['jenis'] == 'SidHas') && !($penguji1 && $penguji2)) {
                    $submissions[] = [
                        'id' => $spota_submission['id'],
                        'judul' => $spota_submission['judul'],
                        'mahasiswa_name' => $mahasiswa->name,
                        'mahasiswa_nim' => $mahasiswa->unique_id,
                        'pembimbing1_name' => $pembimbing1 ? $pembimbing1->name : null,
                        'pembimbing1_nip' => $pembimbing1 ? $pembimbing1->unique_id : null,
                        'pembimbing2_name' => $pembimbing2 ? $pembimbing2->name : null,
                        'pembimbing2_nip' => $pembimbing2 ? $pembimbing2->unique_id : null,
                        'penguji1_name' => $penguji1 ? $penguji1->name : null,
                        'penguji1_nip' => $penguji1 ? $penguji1->unique_id : null,
                        'penguji2_name' => $penguji2 ? $penguji2->name : null,
                        'penguji2_nip' => $penguji2 ? $penguji2->unique_id : null,
                    ];
                }
            }
        }

        return $submissions;
    }

    public function syncSubmissionData()
    {
        $spota_submissions = $this->getAllSubmissions();

        foreach ($spota_submissions as $i => $spota_submission) {
            $nim = explode('-', str_replace(' ', '', $spota_submission['nim']))[0];
            $angkatan = (int) substr($nim, 5, 2);
            if ($angkatan < 15) {
                continue;
            }

            $student = User::where('unique_id', $spota_submission['nim'])->where('role', User::ROLE_MAHASISWA)->first();

            $skill_group = null;
            if ($spota_submission['kelompokKeahlian'] != '0') {
                $skill_group = SkillGroup::where('id', $spota_submission['kelompokKeahlian'])->first();
            }

            $pembimbing1 = User::where('unique_id', $spota_submission['pemb1'])->where('role', User::ROLE_DOSEN)->first();
            $pembimbing2 = User::where('unique_id', $spota_submission['pemb2'])->where('role', User::ROLE_DOSEN)->first();

            $penguji1 = User::where('unique_id', $spota_submission['peng1'])->where('role', User::ROLE_DOSEN)->first();
            $penguji2 = User::where('unique_id', $spota_submission['peng2'])->where('role', User::ROLE_DOSEN)->first();

            $type = Type::where('name', 'Proposal')->first();

            if ($pembimbing1 && $pembimbing2 && $penguji1 && $penguji2 && count($spota_submission['jadwal']) == 0) {
                continue;
            } elseif ($pembimbing1 && $pembimbing2 && !($penguji1 && $penguji2) && count($spota_submission['jadwal']) == 0) {
                $type = Type::where('name', 'Proposal')->first();
            } elseif (count($spota_submission['jadwal']) > 0) {
                $jadwal = $spota_submission['jadwal'][count($spota_submission['jadwal']) - 1];
                if ($jadwal['jenis'] == 'Outline') {
                    $type = Type::where('name', 'Sidang Hasil')->first();
                } elseif ($jadwal['jenis'] == 'SidHas') {
                    $type = Type::where('name', 'Sidang Akhir')->first();
                } else {
                    continue;
                }
            }

            $semester = $spota_submission['semester'];
            $tahun = $spota_submission['tahun'];

            $format = null;

            $student_submissions = Submission::where('student_id', $student->id)->get();
            if ($student_submissions->count() > 0) {
                // do the checking and update it if has some changed
            } else {
                // $type = Type::where('name', 'Outline')->first();
                $format = Format::where('type_id', $type->id)->where('is_primary', 1)->first();
                $format_required_files = $format->required_files()->get();

                $tanggal_keputusan = $spota_submission['tanggalKeputusan'] != '0000-00-00' ? $spota_submission['tanggalKeputusan'] : null;
                $waktu_keputusan = $spota_submission['waktuKeputusan'] != '00:00:00' ? $spota_submission['waktuKeputusan'] : null;

                // turn into timestamps
                $tanggal = null;
                if ($tanggal_keputusan && $waktu_keputusan) {
                    $tanggal = strtotime($tanggal_keputusan.' '.$waktu_keputusan);
                }

                MahasiswaTypeAccessPermission::create([
                    'mahasiswa_id' => $student->id,
                    'type_id' => $type->id,
                ]);

                $submission = Submission::create([
                    'title' => $spota_submission['judul'],
                    'description' => '',
                    'semester' => $semester,
                    'academic_year' => $tahun,
                    'type_id' => $type->id,
                    'format_id' => $format->id,
                    'skill_group_id' => $skill_group ? $skill_group->id : null,
                    'student_id' => $student->id,
                    'spota_submission_id' => $spota_submission['id'],
                    'created_at' => $tanggal,
                    'updated_at' => $tanggal,
                ]);

                foreach ($format_required_files as $format_required_file) {
                    SubmissionRequiredFile::create([
                        'submission_id' => $submission->id,
                        'required_file_id' => $format_required_file->id,
                    ]);
                }

                // pembimbing 1
                if ($format->is_pembimbing_required) {
                    $trial_status = 'pending';
                    if ($format->is_pembimbing_need_to_change_trial_status === 0) {
                        $trial_status = 'approved';
                    }

                    if ($pembimbing1) {
                        $verificator = Verificator::where('submission_id', $submission->id)->where('user_id', $pembimbing1->id)->first();
                        if (!$verificator) {
                            $verificator = Verificator::create([
                                'type' => Verificator::TYPE_PEMBIMBING,
                                'user_id' => $pembimbing1->id,
                                'submission_id' => $submission->id,
                                'status' => $trial_status,
                                'is_need_to_change_trial_status' => $format->is_pembimbing_need_to_change_trial_status,
                            ]);
                        } else {
                            $verificator->update([
                                'submission_id' => $submission->id,
                                'status' => $trial_status,
                                'is_need_to_change_trial_status' => $format->is_pembimbing_need_to_change_trial_status,
                            ]);
                        }
                    }

                    // pembimbing 2
                    if ($pembimbing2) {
                        $verificator = Verificator::where('submission_id', $submission->id)->where('user_id', $pembimbing2->id)->first();
                        if (!$verificator) {
                            $verificator = Verificator::create([
                                'type' => Verificator::TYPE_PEMBIMBING,
                                'user_id' => $pembimbing2->id,
                                'submission_id' => $submission->id,
                                'status' => $trial_status,
                                'is_need_to_change_trial_status' => $format->is_pembimbing_need_to_change_trial_status,
                            ]);
                        } else {
                            $verificator->update([
                                'submission_id' => $submission->id,
                                'status' => $trial_status,
                                'is_need_to_change_trial_status' => $format->is_pembimbing_need_to_change_trial_status,
                            ]);
                        }
                    }
                }

                if ($format->is_penguji_required) {
                    $trial_status = 'pending';
                    if ($format->is_pembimbing_need_to_change_trial_status === 0) {
                        $trial_status = 'approved';
                    }

                    // penguji 1
                    if ($penguji1) {
                        $verificator = Verificator::where('submission_id', $submission->id)->where('user_id', $penguji1->id)->first();
                        if (!$verificator) {
                            $verificator = Verificator::create([
                                'type' => Verificator::TYPE_PENGUJI,
                                'user_id' => $penguji1->id,
                                'submission_id' => $submission->id,
                                'status' => $trial_status,
                                'is_need_to_change_trial_status' => $format->is_penguji_need_to_change_trial_status,
                            ]);
                        } else {
                            $verificator->update([
                                'submission_id' => $submission->id,
                                'status' => $trial_status,
                                'is_need_to_change_trial_status' => $format->is_penguji_need_to_change_trial_status,
                            ]);
                        }
                    }

                    // penguji 2
                    if ($penguji2) {
                        $verificator = Verificator::where('submission_id', $submission->id)->where('user_id', $penguji2->id)->first();
                        if (!$verificator) {
                            $verificator = Verificator::create([
                                'type' => Verificator::TYPE_PENGUJI,
                                'user_id' => $penguji2->id,
                                'submission_id' => $submission->id,
                                'status' => $trial_status,
                                'is_need_to_change_trial_status' => $format->is_penguji_need_to_change_trial_status,
                            ]);
                        } else {
                            $verificator->update([
                                'submission_id' => $submission->id,
                                'status' => $trial_status,
                                'is_need_to_change_trial_status' => $format->is_penguji_need_to_change_trial_status,
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function syncAllData()
    {
        $this->syncStudentData();
        $this->syncLecturerData();
        $this->syncSkillGroupData();
        $this->syncSubmissionData();
    }

    public function syncSchedule($submission, $schedule, $room, $schedule_id)
    {
        $student = User::where('role', User::ROLE_MAHASISWA)->where('id', $submission->student_id)->first();
        $type = Type::where('id', $submission->type_id)->first();

        $submission_type = null;
        if ($type->name == 'Proposal') {
            $submission_type = 'Outline';
        } elseif ($type->name == 'Sidang Hasil') {
            $submission_type = 'SidHas';
        } elseif ($type->name == 'Sidang Akhir') {
            $submission_type = 'Sidang';
        }

        $namaPemb1 = ' ';
        $namaPemb2 = ' ';
        $namaPeng1 = ' ';
        $namaPeng2 = ' ';

        if (getenv('APP_ENV') == 'production') {
            $pembimbings = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PEMBIMBING)->get();
            $pengujis = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PENGUJI)->get();

            if ($pembimbings->count() > 0) {
                $namaPemb1 = $pembimbings->first()->user->name;
                $namaPemb2 = $pembimbings->last()->user->name;
            }

            if ($pengujis->count() > 0) {
                $namaPeng1 = $pengujis->first()->user->name;
                $namaPeng2 = $pengujis->last()->user->name;
            }
        }

        // digunakan jika ingin menggunakan notif WA
        // $format_text = 'Berikut adalah jadwal sidang yang akan dilaksanakan pada tanggal ' . $schedule . '. Silahkan menghubungi pembimbing untuk mengkonfirmasi jadwal sidang.';
        // if ($schedule_id !== false) {
        //     $format_text = 'Jadwal sidang telah diubah. Berikut adalah jadwal sidang yang akan dilaksanakan pada tanggal ' . $schedule . '. Silahkan menghubungi pembimbing untuk mengkonfirmasi jadwal sidang.';
        // }

        $client = new Client();

        $json_body = [];
        if ($schedule === null && $schedule_id === null) {
            return;
        } elseif ($schedule && $schedule_id === null) { // add
            $json_body = [
                'act' => 'add',
                'nim' => 'D03111037',
                'judul' => 'Tes API Jadwal',
                'ruangan' => $room ?: '',
                'jenis' => $submission_type,
                'waktuSidang' => $schedule ?: '',
                'namaPemb1' => 'Dosen 1',
                'namaPemb2' => 'Dosen 2',
                'namaPeng1' => 'Dosen 3',
                'namaPeng2' => 'Dosen 4',
            ];

            if (getenv('APP_ENV') === 'production') {
                $json_body = [
                    'act' => 'add',
                    'nim' => $student->unique_id,
                    'judul' => $submission->title,
                    'ruangan' => $room,
                    'jenis' => $submission_type,
                    'waktuSidang' => $schedule,
                    'namaPemb1' => $namaPemb1,
                    'namaPemb2' => $namaPemb2,
                    'namaPeng1' => $namaPeng1,
                    'namaPeng2' => $namaPeng2,
                ];
            }
        } elseif ($schedule && $schedule_id !== null) { // edit
            $json_body = [
                'act' => 'update',
                'id' => $schedule_id,
                'nim' => 'D03111037',
                'judul' => 'Tes API Jadwal',
                'ruangan' => $room ?: '',
                'jenis' => $submission_type,
                'waktuSidang' => $schedule ?: '',
                'namaPemb1' => 'Dosen 1',
                'namaPemb2' => 'Dosen 2',
                'namaPeng1' => 'Dosen 3',
                'namaPeng2' => 'Dosen 4',
            ];

            if (getenv('APP_ENV') === 'production') {
                $json_body = [
                    'act' => 'update',
                    'id' => $schedule_id,
                    'nim' => $student->unique_id,
                    'judul' => $submission->title,
                    'ruangan' => $room,
                    'jenis' => $submission_type,
                    'waktuSidang' => $schedule,
                    'namaPemb1' => $namaPemb1,
                    'namaPemb2' => $namaPemb2,
                    'namaPeng1' => $namaPeng1,
                    'namaPeng2' => $namaPeng2,
                ];
            }
        } elseif ($schedule === null && $schedule_id !== null) {
            $json_body = [
                'act' => 'delete',
                'id' => $schedule_id,
                'nim' => 'D03111037',
            ];

            if (getenv('APP_ENV') === 'production') {
                $json_body = [
                    'act' => 'delete',
                    'id' => $schedule_id,
                    'nim' => $student->unique_id,
                ];
            }
        }

        $req = $client->post('http://spota.untan.ac.id/steven/API/willy_manageJadwal.php', [
            'json' => $json_body,
        ]);

        $response = $req->getBody()->getContents();
        $datas = json_decode($response, true);

        return $datas;
    }

    public function updateSubmissionTitleAtSpota($spota_submission_id, $next_recomendation_title)
    {
        $client = new Client();

        $json_body = [
            'id' => $spota_submission_id,
            'judul' => $next_recomendation_title,
            'act' => 'update',
        ];

        $req = $client->post('http://spota.untan.ac.id/steven/API/willy_updateDataRekapHasil.php', [
            'json' => $json_body,
        ]);

        $response = $req->getBody()->getContents();
        $datas = json_decode($response, true);

        return $datas;
    }

    public function updatePengujiKeSpota($spota_submission_id, $peng1, $peng2)
    {
        $client = new Client();

        $json_body = [
            'id' => $spota_submission_id,
            'peng1' => $peng1,
            'peng2' => $peng2,
        ];

        $req = $client->post('http://spota.untan.ac.id/steven/API/doxid_penunjukan_penguji.php', [
            'json' => $json_body,
        ]);

        $response = $req->getBody()->getContents();
        $datas = json_decode($response, true);

        return $datas;
    }
}
