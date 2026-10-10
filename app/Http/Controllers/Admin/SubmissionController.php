<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\FunctionHelper;
use App\Helpers\GoogleDriveHelper;
use App\Helpers\SpotaHelper;
use App\Http\Controllers\Controller;
use App\Jobs\DeleteFile;
use App\Jobs\SetEmailForAccessSubmissionFile;
use App\Jobs\UploadUpdateFile;
use App\Jobs\UploadUpdateFinalDocumentFile;
use App\Models\ActivitySubmissionLog;
use App\Models\ActivityUserLog;
use App\Models\Format;
use App\Models\Grading;
use App\Models\Notification;
use App\Models\RequiredFile;
use App\Models\RequiredValue;
use App\Models\Setting;
use App\Models\SkillGroup;
use App\Models\Submission;
use App\Models\SubmissionRequiredFile;
use App\Models\SubmissionRequiredValue;
use App\Models\TemporaryFile;
use App\Models\Type;
use App\Models\User;
use App\Models\Verificator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubmissionController extends Controller
{
    private $functionHelper;
    private $googleDriveHelper;
    private $spotaHelper;

    public function __construct()
    {
        $this->functionHelper = new FunctionHelper();
        $this->googleDriveHelper = new GoogleDriveHelper();
        $this->spotaHelper = new SpotaHelper();
    }

    public function index(Request $request, $type_slug)
    {
        $search = $request->cari;
        $status = $request->status_pengajuan;
        $result_status = $request->hasil_keputusan;
        $angkatan = $request->angkatan;
        $ready_to_be_processed = $request->siap_diproses ? $request->siap_diproses : null;
        $sort_by = $request->urut_berdasarkan;
        $jadwal_seminar = $request->jadwal_seminar;

        $type = Type::where('slug', $type_slug)->firstOrFail();

        $q_submissions = Submission::with(['student', 'skill_group']);

        $q_submissions->when($search, function ($q_submissions) use ($search) {
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

        $q_submissions->when($status, function ($q_submissions) use ($status) {
            $q_submissions->where('status', $status);
        });

        $q_submissions->when($result_status, function ($q_submissions) use ($result_status) {
            $q_submissions->where('result_status', $result_status);
        });

        $q_submissions->when($angkatan, function ($q_submissions) use ($angkatan) {
            $q_submissions->whereHas('student', function ($q2) use ($angkatan) {
                $q2->where('unique_id', 'LIKE', "D10__{$angkatan}%");
            });
        });

        $q_submissions->when($sort_by, function ($q_submissions) use ($sort_by) {
            $q_submissions->orderBy('id', $sort_by);
        });

        $q_submissions->when($ready_to_be_processed, function ($q_submissions) use ($ready_to_be_processed) {
            if ($ready_to_be_processed === 'true') {
                $q_submissions->where('is_ready_to_be_processed', true);
            } else {
                $q_submissions->where('is_ready_to_be_processed', false);
            }
        });

        $q_submissions->when($jadwal_seminar, function ($q) use ($jadwal_seminar) {
            if ($jadwal_seminar == 'Belum ditentukan') {
                $q->where('schedule', null);
            } elseif ($jadwal_seminar == 'Telah ditentukan') {
                $q->where('schedule', '!=', null);
            }
        });

        $submissions = $q_submissions->where('type_id', $type->id)->paginate(10);

        $submissions->appends(request()->query());

        return view('panel.admin.pengajuan.index', compact(
            'type',
            'submissions'
        ));
    }

    public function show($type_slug, $id)
    {
        $skill_groups = SkillGroup::where('status', 'active')->get();
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $id)->where('type_id', $type->id)->firstOrFail();
        $dosens = User::where('role', User::ROLE_DOSEN)->get();

        $verificator_pembimbings = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PEMBIMBING)->orderBy('created_at', 'asc')->get();
        $verificator_penguji = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PENGUJI)->orderBy('created_at', 'asc')->get();

        $verificators = $verificator_pembimbings->merge($verificator_penguji);
        $notifications = Notification::where('to_user_id', auth()->user()->id)->where('submission_id', $submission->id)->update(['is_read' => true]);

        $formats = Format::where('type_id', $type->id)->get();

        $dataStudent = User::where('id', $submission->student_id)->firstOrFail();

        $banyakKehadiran = 0;

        switch ($type_slug) {
            case 'proposal':
                $resultKehadiran = \DB::select("SELECT * FROM submission_presences LEFT JOIN (SELECT id, type_id FROM submissions) t ON submission_presences.submission_id = t.id WHERE type_id = '1' AND submission_presences.unique_id = ?", [$dataStudent->unique_id]);
                $banyakKehadiran = sizeof($resultKehadiran);
                break;
            case 'sidang-akhir':
                $resultKehadiran = \DB::select("SELECT * FROM submission_presences LEFT JOIN (SELECT id, type_id FROM submissions) t ON submission_presences.submission_id = t.id WHERE type_id = '3' AND submission_presences.unique_id = ?", [$dataStudent->unique_id]);
                $banyakKehadiran = sizeof($resultKehadiran);
                break;
        }

        return view('panel.admin.pengajuan.show.description', compact('type', 'submission', 'dosens', 'skill_groups', 'verificators', 'formats', 'banyakKehadiran'));
    }

    public function update(Request $request, $type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $id)->where('type_id', $type->id)->firstOrFail();

        $academic_year = Setting::where('key', 'academic_year')->firstOrFail();
        $semester = Setting::where('key', 'semester')->firstOrFail();

        $request->validate([
            'judul' => 'required',
            'status' => 'required',
            'semester' => 'required',
            'tahun_akademik' => 'required',
            'sudah_siap_di_proses' => 'required',
            'format_pemberkasan_penilaian' => 'required',
        ]);

        $skill_group_id = $submission->skill_group_id;

        // if submission format is need skill group
        if ($submission->bidang_keahlian) {
            $request->validate([
                'bidang_keahlian' => 'required|exists:skill_groups,id',
            ]);

            $skill_group_id = $request->bidang_keahlian;
        }

        // if submission format is need trial schedule
        $schedule = null;
        $room = $request->ruangan;
        $schedule_id = $submission->schedule_id;
        if ($submission->format->is_need_trial_schedule) {
            // jika jadwal sidang ada
            if ($request->jadwal_sidang) {
                $request->validate([
                    'jadwal_sidang' => 'required|date_format:d-m-Y H:i',
                ]);

                $schedule = Carbon::parse($request->jadwal_sidang)->format('Y-m-d H:i:s');
                $room = $request->ruangan;

                if ($schedule || $schedule_id) {
                    $sync_schedule = $this->spotaHelper->syncSchedule($submission, $schedule, $room, $schedule_id);
                    if ($sync_schedule['status'] !== true) {
                        return redirect()->back()->with('failed', $sync_schedule['msg']);
                    } else {
                        if ($schedule_id == null) {
                            $submission->schedule_id = $sync_schedule['id'];
                        }
                    }
                }
            } elseif ($schedule === null && $schedule_id !== null) {
                $sync_schedule = $this->spotaHelper->syncSchedule($submission, $schedule, $room, $schedule_id);
                if ($sync_schedule['status'] !== true) {
                    return redirect()->back()->with('failed', $sync_schedule['msg']);
                } else {
                    $submission->schedule_id = null;
                }
            }
        }

        $is_submission_format_change = false;
        if ($submission->format_id != $request->format_pemberkasan_penilaian) {
            // check if format was exists
            if (Format::where('type_id', $type->id)->where('id', $request->format_pemberkasan_penilaian)->count() == 0) {
                return redirect()->back()->with('failed', 'Format pemberkasan penilaian tidak ditemukan');
            }

            $is_submission_format_change = true;
        }

        $submission_status_before = $submission->status;
        $submission_status_after = $request->status;

        $submission->update([
            'title' => $request->judul,
            'status' => $request->status,
            'amount_sks_now' => $request->jumlah_sks,
            'ipk_now' => $request->ipk,
            'academic_year' => $request->tahun_akademik,
            'semester' => $request->semester,
            'skill_group_id' => $skill_group_id,
            'schedule' => $schedule,
            'room' => $room,
            // 'updated_at' => now(),
            'is_ready_to_be_processed' => $request->sudah_siap_di_proses == 'true' ? 1 : 0,
            'format_id' => $request->format_pemberkasan_penilaian,
        ]);

        if ($is_submission_format_change) {
            // delete all required files
            SubmissionRequiredFile::where('submission_id', $submission->id)->delete();
            // delete all required values
            SubmissionRequiredValue::where('submission_id', $submission->id)->delete();

            // add new required files
            $required_files = RequiredFile::where('format_id', $request->format_pemberkasan_penilaian)->get();
            foreach ($required_files as $required_file) {
                SubmissionRequiredFile::create([
                    'submission_id' => $submission->id,
                    'name' => $required_file->name,
                    'key' => $required_file->key,
                    'type' => $required_file->type,
                ]);
            }
        }

        $details = 'Memperbarui data pengajuan dengan nilai id: '.$submission->id;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        if ($submission_status_before !== Submission::STATUS_APPROVED && $submission_status_after == Submission::STATUS_APPROVED) {
            $this->functionHelper->createNewNextTypeSubmission($submission->id);
        } elseif ($submission->status !== Submission::STATUS_APPROVED) {
            $this->functionHelper->createNewNextSubmission($submission->id);
        }

        return redirect()->back()->with('success', 'Detail pengajuan berhasil diperbarui');
    }

    public function showVerificators($type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $id)->where('type_id', $type->id)->firstOrFail();
        $dosens = User::where('role', User::ROLE_DOSEN)->get();

        $verificator_pembimbings = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PEMBIMBING)->orderBy('created_at', 'asc')->get();
        $verificator_penguji = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PENGUJI)->orderBy('created_at', 'asc')->get();

        $verificators = $verificator_pembimbings->merge($verificator_penguji);
        $notifications = Notification::where('to_user_id', auth()->user()->id)->where('submission_id', $submission->id)->update(['is_read' => true]);

        return view('panel.admin.pengajuan.show.verificators', compact('type', 'submission', 'dosens', 'verificators'));
    }

    public function updateVerificators($type_slug, $id, Request $request)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $id)->where('type_id', $type->id)->firstOrFail();

        $emails = [];

        if ($submission->format->is_penguji_required) {
            // delete all verificator with type Penguji that the ids was not in request penguji_ids
            Verificator::where('type', 'Penguji')->where('submission_id', $submission->id)->whereNotIn('user_id', $request->penguji_ids)->delete();

            $trial_status = Verificator::STATUS_PENDING;
            if ($submission->format->is_penguji_need_to_change_trial_status == false) {
                $trial_status = Verificator::STATUS_APPROVED;
            }

            // insert new verificator with type Penguji that the ids was in request penguji_ids
            if ($request->penguji_ids) {
                foreach ($request->penguji_ids as $penguji_id) {
                    $verificator = Verificator::where('user_id', $penguji_id)->where('submission_id', $submission->id)->first();
                    if ($verificator) {
                        $emails[] = $verificator->user->email;
                        continue;
                    }

                    $verificator = Verificator::create([
                        'type' => 'Penguji',
                        'user_id' => $penguji_id,
                        'submission_id' => $submission->id,
                        'status' => $trial_status,
                        'is_need_to_change_trial_status' => $submission->format->is_penguji_need_to_change_trial_status,
                    ]);

                    // notification to verificator
                    Notification::create([
                        'action' => 'Menjadi Penguji pada pengajuan',
                        'submission_id' => $submission->id,
                        'from_user_id' => auth()->user()->id,
                        'to_user_id' => $penguji_id,
                        'url' => route('panel.dosen.pengajuan.required_files.show', ['type_slug' => $submission->type->slug, 'id' => $submission->id]),
                    ]);

                    $emails[] = $verificator->user->email;
                }
            }
        }

        // if submission format is required of pembimbing or student can select pembimbing more than zero
        if ($submission->format->is_pembimbing_required) {
            // delete all verificator with type Pembimbing that the ids was not in request pembimbing_ids
            Verificator::where('type', 'Pembimbing')->where('submission_id', $submission->id)->whereNotIn('user_id', $request->pembimbing_ids)->delete();

            $trial_status = Verificator::STATUS_PENDING;
            if ($submission->format->is_pembimbing_need_to_change_trial_status == false) {
                $trial_status = Verificator::STATUS_APPROVED;
            }

            // insert new verificator with type Pembimbing that the ids was in request pembimbing_ids
            if ($request->pembimbing_ids) {
                foreach ($request->pembimbing_ids as $pembimbing_id) {
                    $verificator = Verificator::where('user_id', $pembimbing_id)->where('submission_id', $submission->id)->first();
                    if ($verificator) {
                        $emails[] = $verificator->user->email;
                        continue;
                    }

                    $verificator = Verificator::create([
                        'type' => 'Pembimbing',
                        'user_id' => $pembimbing_id,
                        'submission_id' => $submission->id,
                        'status' => $trial_status,
                        'is_need_to_change_trial_status' => $submission->format->is_pembimbing_need_to_change_trial_status,
                    ]);

                    // notification to verificator
                    Notification::create([
                        'action' => 'Menjadi Pembimbing pada pengajuan',
                        'submission_id' => $submission->id,
                        'from_user_id' => auth()->user()->id,
                        'to_user_id' => $pembimbing_id,
                        'url' => route('panel.dosen.pengajuan.required_files.show', ['type_slug' => $submission->type->slug, 'id' => $submission->id]),
                    ]);

                    $emails[] = $verificator->user->email;
                }
            }
        }

        $details = 'Memperbarui verifikator pengajuan dengan nilai id: '.$submission->id;

        // get all required files that has uploaded to google drive and has permission of is_verificator_can_edit
        $submission_required_files_can_edit = SubmissionRequiredFile::where('submission_id', $submission->id)->whereHas('required_file', function ($q) {
            $q->where('is_verificator_can_edit', true);
        })->get();

        if (getenv('APP_ENV') !== 'production') {
            $emails = [
                'willyferry@student.untan.ac.id',
            ];
        }

        foreach ($submission_required_files_can_edit as $submission_required_file) {
            if ($submission_required_file->url !== null && str_contains($submission_required_file->url, 'drive.google.com')) {
                //$this->googleDriveHelper->setPermissionEditToAllEmails($submission_required_file->filename, $emails);
                $setEmailForAccessSubmissionFile = new SetEmailForAccessSubmissionFile($submission_required_file->filename, $emails);
                dispatch($setEmailForAccessSubmissionFile);
            }
        }

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        $this->functionHelper->createNewNextSubmission($submission->id);

        return redirect()->back()->with('success', 'Berhasil perbarui verifikator');
    }

    public function showRequiredFiles($type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $id)->where('type_id', $type->id)->firstOrFail();

        $notifications = Notification::where('to_user_id', auth()->user()->id)->where('submission_id', $submission->id)->update(['is_read' => true]);

        return view('panel.admin.pengajuan.show.required_files', compact('type', 'submission'));
    }

    public function updateRequiredFiles($type_slug, $id, Request $request)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $id)->where('type_id', $type->id)->firstOrFail();
        $format = $submission->format;
        $student = User::where('id', $submission->student_id)->where('role', User::ROLE_MAHASISWA)->firstOrFail();

        $request->validate([
            'status_tandatangan' => 'required|in:pending,approved,rejected',
        ]);

        // if $format && required files more than one
        if ($format && $format->required_files->count() > 0) {
            foreach ($format->required_files as $required_file) {
                $request_required_file_id = str_replace('"', '', $request->input($required_file->id));
                if ($request_required_file_id) {
                    $submission_required_file = SubmissionRequiredFile::where('required_file_id', $required_file->id)->where('submission_id', $submission->id)->first();

                    if ($submission_required_file->url !== null && str_contains($submission_required_file->url, 'drive.google.com')) {
                        $deleteFileQueue = new DeleteFile($submission_required_file->filename);
                        dispatch($deleteFileQueue);
                    }

                    $temporaryFile = TemporaryFile::where('folder', $request_required_file_id)->first();

                    $url = $submission_required_file->url;
                    $file_name = $submission_required_file->filename;
                    if ($temporaryFile) {
                        // move storage from tmp to submissions
                        Storage::move('public/submissions/tmp/'.$temporaryFile->folder.'/'.$temporaryFile->filename, 'public/submissions/'.$temporaryFile->filename);

                        $url = asset('storage/submissions/'.$temporaryFile->filename);
                        $file_name = $temporaryFile->filename;
                    }

                    $submission_required_file->update([
                        'url' => $url,
                        'filename' => $file_name,
                    ]);

                    // do queue jobs to upload to google drive
                    $uploadUpdateFile = new UploadUpdateFile($submission_required_file);
                    dispatch($uploadUpdateFile);

                    $temporaryFile->delete();
                }
            }
        }

        if ($request->required_file_status) {
            foreach ($request->required_file_status as $key => $value) {
                $submission->required_files()->where('required_file_id', $key)->first()->update([
                    'status' => $value,
                ]);
            }
        }

        if ($request->input('fileSKSidang')) {
            $file_name = $submission->file_sk_name;
            $file_url = $submission->file_sk_url;

            if ($submission->file_sk_url !== null) {
                if (str_contains($submission->file_sk_url, 'drive.google.com')) {
                    $deleteFileQueue = new DeleteFile($submission->file_sk_name);
                    dispatch($deleteFileQueue);
                }
            }

            $request_file_key = str_replace('"', '', $request->input('fileSKSidang'));
            $temporaryFile = TemporaryFile::where('folder', $request_file_key)->first();
            if ($temporaryFile) {
                // move storage from tmp to submissions

                Storage::move('public/submissions/tmp/'.$temporaryFile->folder.'/'.$temporaryFile->filename, 'public/submissions/'.$temporaryFile->filename);

                $file_url = asset('storage/submissions/'.$temporaryFile->filename);
                $file_name = $temporaryFile->filename;
            }

            $submission->update([
                'file_sk_url' => $file_url,
                'file_sk_name' => $file_name,
                'file_sk_created' => now(),
            ]);
        }

        $student->update([
            'signature_status' => $request->status_tandatangan,
        ]);

        $details = 'Memperbarui berkas pengajuan dengan nilai id: '.$submission->id;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        $this->functionHelper->createNewNextSubmission($submission->id);

        return redirect()->back()->with('success', 'Berhasil perbarui file yang diperlukan');
    }

    public function showEvaluation($type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $id)->where('type_id', $type->id)->firstOrFail();

        $verificator_pembimbings = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PEMBIMBING)->orderBy('created_at', 'asc')->get();
        $verificator_penguji = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PENGUJI)->orderBy('created_at', 'asc')->get();

        $verificators = $verificator_pembimbings->merge($verificator_penguji);
        $notifications = Notification::where('to_user_id', auth()->user()->id)->where('submission_id', $submission->id)->update(['is_read' => true]);

        // get all required_values
        $format = Format::where('type_id', $type->id)->where('id', $submission->format_id)->first();
        $required_values = RequiredValue::where('format_id', $format->id)->first();

        return view('panel.admin.pengajuan.show.evaluation', compact('type', 'submission', 'verificators', 'format', 'required_values'));
    }

    public function updateEvaluation($type_slug, $id, Request $request)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $id)->where('type_id', $type->id)->firstOrFail();

        // looping request of looping
        if ($request->verificators) {
            foreach ($request->verificators as $i => $request_verificator) {
                // checking if verificator was in submission verificator
                $verificator = Verificator::where('id', $request_verificator['id'])->where('submission_id', $submission->id)->first();
                if (!$verificator) {
                    return redirect()->back()->with('failed', 'Verifikator tidak valid');
                }

                // update the evaluation
                if ($verificator->is_need_to_change_trial_status) {
                    $verificator->update([
                        'status' => $request_verificator['status'],
                    ]);
                }

                $verificator->update([
                    'note' => $request_verificator['note'],
                ]);

                if ($submission->format->required_values) {
                    foreach ($submission->format->required_values as $submission_format_required_value) {
                        if ($request->verificators[$i]['required_values'][$submission_format_required_value->id] === null) {
                            continue;
                        }

                        if ($request->verificators[$i]['required_values'][$submission_format_required_value->id]) {
                            $request->validate([
                                'verificators.'.$i.'.required_values.'.$submission_format_required_value->id => 'required|min:0|max:100|numeric',
                            ]);
                        }

                        $submission_required_value = $submission->required_values()->where('required_value_id', $submission_format_required_value->id)->where('verificator_id', $verificator->id)->first();
                        if ($submission_required_value) {
                            $submission_required_value->update([
                                'value' => $request->verificators[$i]['required_values'][$submission_format_required_value->id],
                            ]);
                        } else {
                            SubmissionRequiredValue::create([
                                'submission_id' => $submission->id,
                                'required_value_id' => $submission_format_required_value->id,
                                'value' => $request->verificators[$i]['required_values'][$submission_format_required_value->id],
                                'verificator_id' => $verificator->id,
                            ]);
                        }
                    }

                    $total_average_score = 0;

                    $verificators = Verificator::where('submission_id', $submission->id)->get();

                    foreach ($verificators as $j => $verificator) {
                        $verificator_submission_required_values = SubmissionRequiredValue::where('submission_id', $submission->id)->where('verificator_id', $verificator->id)->get();

                        $total_score = 0;
                        if ($verificator_submission_required_values->count() != $submission->format->required_values->count()) {
                            $total_average_score = 0;
                            break;
                        } else {
                            foreach ($verificator_submission_required_values as $verificator_submission_required_value) {
                                $total_score += $verificator_submission_required_value->value * ($verificator_submission_required_value->required_value->weight / 100);
                            }
                        }

                        $total_average_score += $total_score;
                    }

                    if ($total_average_score != 0) {
                        $total_average_score /= $verificators->count();
                        $grading = Grading::where('maksimal_score', '>=', $total_average_score)->where('minimal_score', '<=', $total_average_score)->first();

                        $grade = null;
                        $grade_description = null;
                        $grade_status = null;

                        if ($grading) {
                            $grade = $grading->grade;
                            $grade_description = $grading->description;
                            $grade_status = $grading->submission_status;
                        }

                        $submission->update([
                            'grade' => $grade,
                            'score' => $total_average_score,
                            'grade_description' => $grade_description,
                            'grade_status' => $grade_status,
                            // 'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        $details = 'Memperbarui penilaian pengajuan dengan nilai id: '.$submission->id;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        $this->functionHelper->createNewNextSubmission($submission->id);

        return redirect()->back()->with('success', 'Berhasil perbarui penilaian');
    }

    public function destroy($type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $id)->where('type_id', $type->id)->firstOrFail();

        $details = 'Menghapus pengajuan dengan nilai id: '.$submission->id;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        $submission->delete();

        return redirect()->back()->with('success', 'Berkas berhasil dihapus');
    }

    public function updateFile(Request $request, $type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('type_id', $type->id)->where('id', $id)->firstOrFail();
        $student = User::where('id', $submission->student_id)->where('role', User::ROLE_MAHASISWA)->firstOrFail();
        $format = $submission->format;

        foreach ($format->required_files as  $required_file) {
            if ($request->hasFile($required_file->id)) {
                $submission_required_file = SubmissionRequiredFile::where('required_file_id', $required_file->id)->where('submission_id', $submission->id)->first();

                $folder = uniqid().'-'.now()->timestamp;
                $file_name = $student->unique_id.'-'.Str::slug($student->name).'-'.$type->slug.'-'.Str::slug($required_file->name).'-'.uniqid().'.'.$request->file($required_file->id)->getClientOriginalExtension();
                $request->file($required_file->id)->storeAs('submissions/tmp/'.$folder, $file_name, 'public');

                TemporaryFile::create([
                    'folder' => $folder,
                    'filename' => $file_name,
                ]);

                return response()->json($folder);
            }
        }

        if ($request->hasFile('fileSKSidang')) {
            $folder = uniqid().'-'.now()->timestamp;
            $file_name = $student->unique_id.'-'.Str::slug($student->name).'-'.$type->slug.'-'.'SK'.'-'.uniqid().'.'.$request->file('fileSKSidang')->getClientOriginalExtension();
            $request->file('fileSKSidang')->storeAs('submissions/tmp/'.$folder, $file_name, 'public');
            TemporaryFile::create([
                'folder' => $folder,
                'filename' => $file_name,
            ]);

            return response()->json($folder);
        }

        return response()->json(['error' => 'Tidak ada file yang diupload'], 422);
    }

    public function showDiscussions($type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('type_id', $type->id)->where('id', $id)->firstOrFail();

        $discussions = $submission->consultations()->orderBy('id', 'desc')->get();

        return view('panel.admin.pengajuan.show.discussions', compact('type', 'submission', 'discussions'));
    }

    public function makeResultSubmission($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();
        $student = User::where('id', $submission->student_id)->firstOrFail();

        return view('panel.admin.pengajuan.show.make-result', compact('student', 'submission', 'type'));
    }

    public function makeResultSubmissionUpdate(Request $request, $type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();

        $request->validate([
            'hasil_sidang' => 'required|in:'.Submission::RESULT_PENDING.','.Submission::RESULT_APPROVED.','.Submission::RESULT_REJECTED.','.Submission::RESULT_REPEATED.','.Submission::RESULT_APPROVED_WITH_FIXED,
        ]);

        $recomendation_repeated_schedule = null;
        if ($request->hasil_sidang && $request->hasil_sidang == Submission::RESULT_REPEATED) {
            $request->validate([
                'rekomendasi_waktu_sidang_ulang' => 'required|date_format:d-m-Y H:i',
            ]);

            $recomendation_repeated_schedule = Carbon::parse($request->rekomendasi_waktu_sidang_ulang)->format('Y-m-d H:i:s');
        }

        $next_title_recomendation = $submission->next_title_recomendation;
        if ($request->rekomendasi_perubahan_judul) {
            $next_title_recomendation = $request->rekomendasi_perubahan_judul;
        }

        $submission->update([
            'record_url' => $request->hasil_rekaman_sidang,
            'result_status' => $request->hasil_sidang,
            'recomendation_repeated_schedule' => $recomendation_repeated_schedule,
            'next_title_recomendation' => $next_title_recomendation,
        ]);

        $details = 'Memperbaharui hasil keputusan pengajuan dengan nilai id: '.$submission->id;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        $this->functionHelper->createNewNextSubmission($submission->id);

        return redirect()->back()->with('success', 'Berhasil perbaharui hasil pengajuan');
    }

    public function resultSubmission($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();
        $student = User::where('id', $submission->student_id)->firstOrFail();

        return view('panel.admin.pengajuan.show.result', compact('student', 'submission', 'type'));
    }

    public function uploadFileFinalDocument(Request $request, $type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();
        $student = User::where('id', $submission->student_id)->firstOrFail();

        $request->validate([
            'berkas_final_revisi' => 'required|file',
        ]);

        $folder = uniqid().'-'.now()->timestamp;

        $file_name = $student->unique_id.'-'.Str::slug($student->name).'-'.$type->slug.'-dokumen-final-'.uniqid().'.'.$request->file('berkas_final_revisi')->getClientOriginalExtension();
        $request->file('berkas_final_revisi')->storeAs('submissions/tmp/'.$folder, $file_name, 'public');

        $temporaryFile = TemporaryFile::create([
            'folder' => $folder,
            'filename' => $file_name,
        ]);

        if ($temporaryFile) {
            return response()->json($folder);
        } else {
            return response()->json(['error' => 'Tidak ada file yang diupload'], 422);
        }
    }

    public function resultSubmissionUpdate(Request $request, $type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();

        $file_name = $submission->final_document_name;
        $file_url = $submission->final_document_url;

        if ($request->input('berkas_final_revisi')) {
            if ($submission->final_document_url !== null) {
                if (str_contains($submission->final_document_url, 'drive.google.com')) {
                    $deleteFileQueue = new DeleteFile($submission->final_document_name);
                    dispatch($deleteFileQueue);
                }
            }

            $request_file_key = str_replace('"', '', $request->input('berkas_final_revisi'));
            $temporaryFile = TemporaryFile::where('folder', $request_file_key)->first();
            // move storage from tmp to submissions
            Storage::move('public/submissions/tmp/'.$temporaryFile->folder.'/'.$temporaryFile->filename, 'public/submissions/'.$temporaryFile->filename);
            $file_url = asset('storage/submissions/'.$temporaryFile->filename);
            $file_name = $temporaryFile->filename;
        }

        $submission->update([
            'final_document_url' => $file_url,
            'final_document_name' => $file_name,
            'final_document_created_at' => now(),
        ]);

        if ($request->input('berkas_final_revisi')) {
            // do queue jobs to upload to google drive
            $uploadUpdateFinalDocumentFile = new UploadUpdateFinalDocumentFile($submission);
            dispatch($uploadUpdateFinalDocumentFile);

            $temporaryFile->delete();
        }

        if ($request->verificators) {
            foreach ($request->verificators as $request_verificator) {
                $verificator = Verificator::where('id', $request_verificator['id'])->firstOrFail();
                $verificator->update([
                    'final_status' => $request_verificator['final_status'],
                ]);
            }
        }

        $this->functionHelper->createNewNextSubmission($submission->id);

        return redirect()->back()->with('success', 'Berhasil perbaharui hasil pengajuan');
    }

    public function syncSubmissionFormat()
    {
        $submissions = Submission::all();

        foreach ($submissions as $submission) {
            if ($submission->format->is_pembimbing_required && $submission->format->is_pembimbing_need_to_change_trial_status == false) {
                $pembimbings = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PEMBIMBING)->get();
                foreach ($pembimbings as $pembimbing) {
                    $pembimbing->update([
                        'status' => Verificator::STATUS_APPROVED,
                        'is_need_to_change_trial_status' => false,
                    ]);
                }
            }

            if ($submission->format->is_penguji_required && $submission->format->is_penguji_need_to_change_trial_status == false) {
                $pengujis = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PENGUJI)->get();
                foreach ($pengujis as $penguji) {
                    $penguji->update([
                        'status' => Verificator::STATUS_APPROVED,
                        'is_need_to_change_trial_status' => false,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Berhasil menyinkronisasi format pengajuan');
    }
}
