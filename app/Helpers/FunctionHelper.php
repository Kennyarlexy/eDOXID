<?php

namespace App\Helpers;

use App\Models\GetTypeAccessPermission;
use App\Models\MahasiswaTypeAccessPermission;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\SubmissionRequiredFile;
use App\Models\SubmissionRequiredValue;
use App\Models\Type;
use App\Models\User;
use App\Models\Verificator;

class FunctionHelper
{
    public $count = 0;

    public function __construct()
    {
        // jika diperlukan
    }

    public function checkAllVerificatorStatus($submission_id)
    {
        $all_verificators = Verificator::where('submission_id', $submission_id)->get();

        $data = [
            'all_approved' => true,
            'has_rejected' => false,
        ];

        foreach ($all_verificators as $verificator) {
            if ($verificator->status == 'approved' && $verificator->final_status == 'approved') {
                $this->count++;
            } else if ($verificator->status == 'rejected') {
                $data['all_approved'] = false;
                // $data['has_rejected'] = true;
            } else {
                $data['all_approved'] = false;
            }
        }

        return $data;
    }

    public function checkAllRequiredFileStatus($submission_id)
    {
        $submission = Submission::where('id', $submission_id)->first();
        $format_required_files = $submission->format->required_files;
        $all_submission_required_files = SubmissionRequiredFile::where('submission_id', $submission_id)->get();

        $all_approved = true;

        if ($format_required_files->count() != $all_submission_required_files->count()) {
            $all_approved = false;
        } else {
            foreach ($all_submission_required_files as $submission_required_file) {
                if ($submission_required_file->status == 'pending') {
                    $all_approved = false;
                } else {
                    $this->count++;
                }
            }
        }

        return $all_approved;
    }

    public function checkAllRequiredValues($submission_id)
    {
        $submission = Submission::where('id', $submission_id)->first();
        $format_required_values = $submission->format->required_values;
        $all_submission_required_values = SubmissionRequiredValue::where('submission_id', $submission_id)->get();

        $verificators = Verificator::where('submission_id', $submission_id)->get();

        $total_submission_required_values = $format_required_values->count() * $verificators->count();

        $all_approved = true;

        if ($format_required_values->count() != ($total_submission_required_values / $verificators->count())) {
            $all_approved = false;
        } else {
            $this->count += $all_submission_required_values->count();
        }

        return $all_approved;
    }

    public function createNewNextSubmission($submission_id)
    {
        $submission = Submission::where('id', $submission_id)->firstOrFail();
        $format = $submission->format;
        $student = User::where('id', $submission->student_id)->where('role', User::ROLE_MAHASISWA)->firstOrFail();

        $all_approved = true;
        $has_rejected = false;

        if ($format->required_files->count() > 0 && $all_approved) {
            $all_approved = $this->checkAllRequiredFileStatus($submission_id);
        }

        if ($format->is_pembimbing_required || $format->is_penguji_required && $all_approved) {
            $data_return = $this->checkAllVerificatorStatus($submission_id);
            $all_approved = $data_return['all_approved'];
            $has_rejected = $data_return['has_rejected'];
        }

        if ($student->signature_status !== 'approved') {
            $all_approved = false;
        }

        if ($submission->result_status == Submission::RESULT_APPROVED) {
            $submission->update([
                'status' => Submission::STATUS_APPROVED
            ]);

            $submission_verificators = Verificator::where('submission_id', $submission_id)->get();
            foreach ($submission_verificators as $submission_verificator) {
                $submission_verificator->update([
                    'status' => 'approved',
                    'final_status' => 'approved'
                ]);
            }

            $this->createNewNextTypeSubmission($submission_id);
        } else if ($submission->result_status == Submission::RESULT_REPEATED) {
            $submission->update([
                'status' => Submission::STATUS_REPEATED
            ]);

            $this->createNewSameTypeSubmission($submission_id);
        } else if ($submission->result_status == Submission::RESULT_REJECTED) {
            $submission->update([
                'status' => Submission::STATUS_REJECTED
            ]);
        } else {
            if ($format->required_values->count() === 0) {
                if ($all_approved) {
                    $this->createNewNextTypeSubmission($submission_id);
                } else {
                    if (!($submission->status == Submission::STATUS_REJECTED || $submission->status == Submission::STATUS_REPEATED)) {
                        if ($has_rejected) {
                            $submission->update([
                                'status' => Submission::STATUS_REJECTED
                            ]);
                        } else {
                            if ($this->count != 0) {
                                $submission->update([
                                    'status' => Submission::STATUS_PROCESSED
                                ]);
                            } else {
                                if ($submission->is_ready_to_be_processed) {
                                    $submission->update([
                                        'status' => Submission::STATUS_PROCESSED
                                    ]);
                                } else {
                                    $submission->update([
                                        'status' => Submission::STATUS_PENDING
                                    ]);
                                }
                            }
                        }
                    }
                }
            } else {
                if ($format->required_values->count() > 0 && $all_approved) {
                    $all_approved = $this->checkAllRequiredValues($submission_id);
                }

                if ($all_approved) {
                    if ($submission->grade_status == Submission::GRADE_STATUS_APPROVED) {
                        $this->createNewNextTypeSubmission($submission_id);
                    } else if ($submission->grade_status == Submission::GRADE_STATUS_REPEATED) {
                        $this->createNewSameTypeSubmission($submission_id, Submission::STATUS_REPEATED);
                    }
                } else {
                    if (!($submission->status == Submission::STATUS_REJECTED || $submission->status == Submission::STATUS_REPEATED)) {
                        if ($has_rejected) {
                            $submission->update([
                                'status' => Submission::STATUS_REJECTED
                            ]);
                        } else {
                            if ($this->count != 0) {
                                $submission->update([
                                    'status' => Submission::STATUS_PROCESSED
                                ]);
                            } else {
                                if ($submission->is_ready_to_be_processed) {
                                    $submission->update([
                                        'status' => Submission::STATUS_PROCESSED
                                    ]);
                                } else {
                                    $submission->update([
                                        'status' => Submission::STATUS_PENDING
                                    ]);
                                }
                            }
                        }
                    }
                }
            }
        }

    }

    public function createNewSameTypeSubmission($submission_id)
    {
        $submission = Submission::where('id', $submission_id)->firstOrFail();

        $submission->update([
            'status' => Submission::STATUS_REPEATED
        ]);

        $sameType = Type::where('id', $submission->type_id)->firstOrFail();
        $newFormatType = $sameType->formats()->where('is_primary', 1)->first();

        $title = $submission->title;

        if ($submission->next_title_recomendation) {
            $title = $submission->next_title_recomendation;
        }

        $newSubmission = Submission::create([
            'title' => $title,
            'description' => $submission->description,
            'type_id' => $sameType->id,
            'skill_group_id' => $submission->skill_group_id,
            'format_id' => $newFormatType->id,
            'academic_year' => $submission->academic_year,
            'semester' => $submission->semester,
            'student_id' => $submission->student_id,
            'document_format' => $newFormatType->document_format,
            'spota_submission_id' => $submission->spota_submission_id,
            'schedule_request' => $submission->recomendation_repeated_schedule,
        ]);

        // notification old submission to student
        Notification::create([
            'action' => 'Pengajuan telah disetujui',
            'submission_id' => $submission->id,
            'from_user_id' => auth()->user()->id,
            'to_user_id' => $submission->student_id,
            'url' => route('panel.mahasiswa.pengajuan.required_files.show', ['type_slug' => $submission->type->slug, 'id' => $submission->id]),
        ]);

        // notification new next submission to student
        Notification::create([
            'action' => 'Pengajuan telah dibuatkan otomatis',
            'submission_id' => $newSubmission->id,
            'from_user_id' => auth()->user()->id,
            'to_user_id' => $newSubmission->student_id,
            'url' => route('panel.mahasiswa.pengajuan.required_files.show', ['type_slug' => $newSubmission->type->slug, 'id' => $newSubmission->id]),
        ]);

        // cek apakah format pengajuan sekarang memiliki dosen pembimbing
        if ($submission->format->is_pembimbing_required) {
            $submission_verificator_pembimbing_ids = Verificator::where('submission_id', $submission->id)->where('type', 'Pembimbing')->pluck('user_id');
            foreach ($submission_verificator_pembimbing_ids as $submission_verificator_pembimbing_id) {
                Verificator::create([
                    'submission_id' => $newSubmission->id,
                    'user_id' => $submission_verificator_pembimbing_id,
                    'type' => 'Pembimbing'
                ]);

                // notification new next submission to student
                Notification::create([
                    'action' => 'Menjadi pembimbing pada pengajuan',
                    'submission_id' => $newSubmission->id,
                    'from_user_id' => auth()->user()->id,
                    'to_user_id' => $submission_verificator_pembimbing_id,
                    'url' => route('panel.dosen.pengajuan.required_files.show', ['type_slug' => $newSubmission->type->slug, 'id' => $newSubmission->id]),
                ]);
            }
        }

        // cek apakah format pengajuan sekarang memiliki penguji
        if ($submission->format->is_penguji_required) {
            $submission_verificator_penguji_ids = Verificator::where('submission_id', $submission->id)->where('type', 'Penguji')->pluck('user_id');
            foreach ($submission_verificator_penguji_ids as $submission_verificator_penguji_id) {
                Verificator::create([
                    'submission_id' => $newSubmission->id,
                    'user_id' => $submission_verificator_penguji_id,
                    'type' => 'Penguji'
                ]);

                // notification new next submission to student
                Notification::create([
                    'action' => 'Menjadi pembimbing pada pengajuan',
                    'submission_id' => $newSubmission->id,
                    'from_user_id' => auth()->user()->id,
                    'to_user_id' => $submission_verificator_penguji_id,
                    'url' => route('panel.dosen.pengajuan.required_files.show', ['type_slug' => $newSubmission->type->slug, 'id' => $newSubmission->id]),
                ]);
            }
        }

        // lakukan insert newFormat required_files ke newSubmission
        foreach ($newFormatType->required_files as $required_file) {
            SubmissionRequiredFile::create([
                'submission_id' => $newSubmission->id,
                'required_file_id' => $required_file->id,
            ]);
        }
    }

    public function createNewNextTypeSubmission($submission_id, $status = "approved")
    {
        $submission = Submission::where('id', $submission_id)->firstOrFail();

        $submission->update([
            'status' => $status
        ]);

        $title = $submission->title;

        if ($submission->next_title_recomendation) {
            $title = $submission->next_title_recomendation;

            // run spota api to update title
            $spotaHelper = new SpotaHelper();
            $spotaHelper->updateSubmissionTitleAtSpota($submission->spota_submission_id, $title);
        }

        $getTypeAccessPermissions = GetTypeAccessPermission::where('type_id', $submission->type->id)->where('format_id', $submission->format_id)->get();
        foreach ($getTypeAccessPermissions as $getTypeAccessPermission) {
            $mahasiswaTypeAccessPermission  = MahasiswaTypeAccessPermission::where('mahasiswa_id', $submission->student_id)->where('type_id', $getTypeAccessPermission->access_type_id)->first();
            $newType = Type::where('id', $getTypeAccessPermission->access_type_id)->first();
            $newFormatType = $newType->formats()->where('is_primary', 1)->first();
            if (!$mahasiswaTypeAccessPermission) {
                MahasiswaTypeAccessPermission::create([
                    'mahasiswa_id' => $submission->student_id,
                    'type_id' => $getTypeAccessPermission->access_type_id
                ]);

                // cek apakah format pengajuan sekarang meminta untuk memberikan verifikator kepada pengajuan selanjutnya
                if ($submission->format->if_approved_next_submission_using_previous_verificators == 1) {

                    $newSubmission = Submission::create([
                        'title' => $title,
                        'description' => $submission->description,
                        'type_id' => $newType->id,
                        'skill_group_id' => $submission->skill_group_id,
                        'format_id' => $newFormatType->id,
                        'academic_year' => $submission->academic_year,
                        'semester' => $submission->semester,
                        'student_id' => $submission->student_id,
                        'document_format' => $newFormatType->document_format,
                        'spota_submission_id' => $submission->spota_submission_id
                    ]);

                    // notification old submission to student
                    Notification::create([
                        'action' => 'Pengajuan telah disetujui',
                        'submission_id' => $submission->id,
                        'from_user_id' => auth()->user()->id,
                        'to_user_id' => $submission->student_id,
                        'url' => route('panel.mahasiswa.pengajuan.required_files.show', ['type_slug' => $submission->type->slug, 'id' => $submission->id]),
                    ]);

                    // notification new next submission to student
                    Notification::create([
                        'action' => 'Pengajuan telah dibuatkan otomatis',
                        'submission_id' => $newSubmission->id,
                        'from_user_id' => auth()->user()->id,
                        'to_user_id' => $newSubmission->student_id,
                        'url' => route('panel.mahasiswa.pengajuan.required_files.show', ['type_slug' => $newSubmission->type->slug, 'id' => $newSubmission->id]),
                    ]);

                    // cek apakah format pengajuan sekarang memiliki dosen pembimbing
                    if ($submission->format->is_pembimbing_required) {
                        $submission_verificator_pembimbing_ids = Verificator::where('submission_id', $submission->id)->where('type', 'Pembimbing')->pluck('user_id');
                        foreach ($submission_verificator_pembimbing_ids as $submission_verificator_pembimbing_id) {
                            Verificator::create([
                                'submission_id' => $newSubmission->id,
                                'user_id' => $submission_verificator_pembimbing_id,
                                'type' => 'Pembimbing'
                            ]);

                            // notification new next submission to student
                            Notification::create([
                                'action' => 'Menjadi pembimbing pada pengajuan',
                                'submission_id' => $newSubmission->id,
                                'from_user_id' => auth()->user()->id,
                                'to_user_id' => $submission_verificator_pembimbing_id,
                                'url' => route('panel.dosen.pengajuan.required_files.show', ['type_slug' => $newSubmission->type->slug, 'id' => $newSubmission->id]),
                            ]);
                        }
                    }

                    // cek apakah format pengajuan sekarang memiliki penguji
                    if ($submission->format->is_penguji_required) {
                        $submission_verificator_penguji_ids = Verificator::where('submission_id', $submission->id)->where('type', 'Penguji')->pluck('user_id');
                        foreach ($submission_verificator_penguji_ids as $submission_verificator_penguji_id) {
                            Verificator::create([
                                'submission_id' => $newSubmission->id,
                                'user_id' => $submission_verificator_penguji_id,
                                'type' => 'Penguji'
                            ]);

                            // notification new next submission to student
                            Notification::create([
                                'action' => 'Menjadi pembimbing pada pengajuan',
                                'submission_id' => $newSubmission->id,
                                'from_user_id' => auth()->user()->id,
                                'to_user_id' => $submission_verificator_penguji_id,
                                'url' => route('panel.dosen.pengajuan.required_files.show', ['type_slug' => $newSubmission->type->slug, 'id' => $newSubmission->id]),
                            ]);
                        }
                    }

                    // lakukan insert newFormat required_files ke newSubmission
                    foreach ($newFormatType->required_files as $required_file) {
                        SubmissionRequiredFile::create([
                            'submission_id' => $newSubmission->id,
                            'required_file_id' => $required_file->id,
                        ]);
                    }
                }
            }
        }
    }

    public function translateDayToIndonesian($day_index)
    {
        if ($day_index == 0) {
            return 'Minggu';
        } else if ($day_index == 1) {
            return 'Senin';
        } else if ($day_index == 2) {
            return 'Selasa';
        } else if ($day_index == 3) {
            return 'Rabu';
        } else if ($day_index == 4) {
            return 'Kamis';
        } else if ($day_index == 5) {
            return 'Jumat';
        } else if ($day_index == 6) {
            return 'Sabtu';
        }
    }

    public function translateMonthToIndonesian($month_index)
    {
        if ($month_index == 1) {
            return "Januari";
        } else if ($month_index == 2) {
            return "Februari";
        } else if ($month_index == 3) {
            return "Maret";
        } else if ($month_index == 4) {
            return "April";
        } else if ($month_index == 5) {
            return "Mei";
        } else if ($month_index == 6) {
            return "Juni";
        } else if ($month_index == 7) {
            return "Juli";
        } else if ($month_index == 8) {
            return "Agustus";
        } else if ($month_index == 9) {
            return "September";
        } else if ($month_index == 10) {
            return "Oktober";
        } else if ($month_index == 11) {
            return "November";
        } else if ($month_index == 12) {
            return "Desember";
        }
    }
}
