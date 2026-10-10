<?php

namespace App\Http\Controllers\Dosen;

use App\Helpers\FunctionHelper;
use App\Helpers\GoogleDriveHelper;
use App\Helpers\SpotaHelper;
use App\Helpers\WhatsappHelper;
use App\Http\Controllers\Controller;
use App\Jobs\SetEmailForAccessSubmissionFile;
use App\Jobs\VerificatorAddNewDiscussionNotificationQueue;
use App\Models\ActivitySubmissionLog;
use App\Models\ActivityUserLog;
use App\Models\Grading;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\SubmissionConsultation;
use App\Models\SubmissionRequiredFile;
use App\Models\SubmissionRequiredValue;
use App\Models\Type;
use App\Models\User;
use App\Models\Verificator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    private $functionHelper;
    private $whatsappHelper;
    private $spotaHelper;
    private $googleDriveHelper;

    public function __construct()
    {
        $this->functionHelper = new FunctionHelper();

        $wa_api_key = Setting::where('key', 'wa_api_key')->firstOrFail();
        $wa_sender_phone_number = Setting::where('key', 'wa_sender_phone_number')->firstOrFail();
        $this->whatsappHelper = new WhatsappHelper($wa_api_key->value, $wa_sender_phone_number->value);
        $this->spotaHelper = new SpotaHelper();
        $this->googleDriveHelper = new GoogleDriveHelper();
    }

    public function index(Request $request, $type_slug)
    {
        $search = $request->cari;
        $status = $request->status_pengajuan;
        $result_status = $request->hasil_keputusan;
        $angkatan = $request->angkatan;
        $status_lihat = $request->status_lihat;
        $sebagai = $request->sebagai;
        $sort_by = $request->urut_berdasarkan ?: 'asc';
        $tim = $request->tim;

        $type = Type::where('slug', $type_slug)->firstOrFail();
        $dosen = User::where('id', auth()->user()->id)->firstOrFail();
        $submissions = [];

        $q_submissions = Submission::with(['student', 'skill_group'])->where('is_ready_to_be_processed', 1);

        if ($dosen->position === null) {
            $q_submissions->whereHas('verificators', function ($q) use ($dosen) {
                $q->where('user_id', $dosen->id);
            });
        }

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

        $q_submissions->when($sebagai, function ($q_submissions) use ($sebagai, $dosen) {
            if ($sebagai == 'Penguji') {
                $q_submissions->whereHas('verificators', function ($q) use ($dosen) {
                    return $q->where('type', Verificator::TYPE_PENGUJI)->where('user_id', $dosen->id);
                });
            } elseif ($sebagai == 'Pembimbing') {
                $q_submissions->whereHas('verificators', function ($q) use ($dosen) {
                    return $q->where('type', Verificator::TYPE_PEMBIMBING)->where('user_id', $dosen->id);
                });
            } elseif ($sebagai == 'Tidak Keduanya') {
                if ($dosen->position !== null) {
                    $q_submissions->whereDoesntHave('verificators', function ($q) use ($dosen) {
                        return $q->where('user_id', $dosen->id);
                    });
                }
            }
        });

        $q_submissions->when($sort_by, function ($q_submissions) use ($sort_by) {
            $q_submissions->orderBy('ready_at', $sort_by);
        });

        if ($dosen->position !== null) {
            $q_submissions->when($tim, function ($q) use ($tim) {
                if ($tim == 'Belum Memiliki Pembimbing') {
                    $q->whereDoesntHave('verificators', function ($q) {
                        return $q->where('type', Verificator::TYPE_PEMBIMBING);
                    });
                } elseif ($tim == 'Belum Memiliki Penguji') {
                    $q->whereDoesntHave('verificators', function ($q) {
                        return $q->where('type', Verificator::TYPE_PENGUJI);
                    });
                }
            });
        }

        $q_submissions->when($status_lihat, function ($q) use ($status_lihat, $dosen) {
            if ($status_lihat == 'Belum Dilihat') {
                return $q->whereHas('verificators', function ($q2) use ($dosen) {
                    return $q2->where('user_id', $dosen->id)->where('is_read_by_dosen', '=', 0);
                });
            } elseif ($status_lihat == 'Telah Dilihat') {
                return $q->whereHas('verificators', function ($q2) use ($dosen) {
                    return $q2->where('user_id', $dosen->id)->where('is_read_by_dosen', '=', 1);
                });
            }
        });

        $submissions = $q_submissions->where('type_id', $type->id)->paginate(10);
        $submissions->appends(request()->query());

        return view('panel.dosen.pengajuan.index', compact(
            'type',
            'submissions',
            'dosen'
        ));
    }

    public function show($type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $auth_dosen = User::where('id', auth()->user()->id)->firstOrFail();
        $submission = null;
        $dosens = [];

        if ($auth_dosen->position == 'Ketua Jurusan' || $auth_dosen->position == 'Ketua Prodi') {
            $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $id)->where('type_id', $type->id)->firstOrFail();
            $dosens = User::where('role', User::ROLE_DOSEN)->get();
        } else {
            $submission = Submission::where('is_ready_to_be_processed', 1)->where('type_id', $type->id)->where('id', $id)->whereHas('verificators', function ($q) use ($auth_dosen) {
                $q->where('user_id', $auth_dosen->id);
            })->firstOrFail();
        }

        $verificator = Verificator::where('submission_id', $id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->save();
        }

        $idSidangHasil = 0;
        if ($type_slug == 'sidang-akhir') {
            $idSpota = $submission->spota_submission_id;
            $submissionSidangHasil = Submission::where('spota_submission_id', $idSpota)->where('type_id', '2')->orderBy('id', 'desc')->first();
            if ($submissionSidangHasil != null) {
                $idSidangHasil = $submissionSidangHasil->id;
            }
        }

        $idSeminarProposal = 0;
        if ($type_slug == 'sidang-hasil') {
            $idSpota = $submission->spota_submission_id;
            $submissionSeminarProposal = Submission::where('spota_submission_id', $idSpota)->where('type_id', '1')->orderBy('id', 'desc')->first();
            if ($submissionSeminarProposal != null) {
                $idSeminarProposal = $submissionSeminarProposal->id;
            }
        }

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

        $notifications = Notification::where('to_user_id', auth()->user()->id)->where('submission_id', $submission->id)->update(['is_read' => true]);

        return view('panel.dosen.pengajuan.show.description', compact('type', 'submission', 'auth_dosen', 'dosens', 'verificator', 'idSidangHasil', 'idSeminarProposal', 'banyakKehadiran'));
    }

    public function update(Request $request, $type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $dosen = User::where('id', auth()->user()->id)->firstOrFail();
        $submission = null;

        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        if ($dosen->position == 'Ketua Jurusan' || $dosen->position == 'Ketua Prodi') {
            $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $id)->firstOrFail();
        } else {
            $submission = Submission::where('is_ready_to_be_processed', 1)->where('type_id', $type->id)->where('id', $id)->whereHas('verificators', function ($q) use ($dosen) {
                $q->where('user_id', $dosen->id);
            })->firstOrFail();
        }

        if (!($submission->status == Submission::STATUS_PENDING || $submission->status == Submission::STATUS_PROCESSED)) {
            return redirect()->back()->with('failed', 'Pengajuan sudah dikonfirmasi, tidak apat memperbaharui');
        }

        $verificator = Verificator::where('user_id', $dosen->id)->where('submission_id', $submission->id)->firstOrFail();

        $verificator->update([
            'status' => $request->status,
        ]);

        // $submission->update([
        //     'updated_at' => now(),
        // ]);

        if ($request->status == Submission::STATUS_REJECTED) {
            $submission_required_files_can_edit = SubmissionRequiredFile::where('submission_id', $submission->id)->whereHas('required_file', function ($q) {
                $q->where('is_verificator_can_edit', 1);
            })->get();

            foreach ($submission_required_files_can_edit as $submission_required_file) {
                $submission_required_file->update([
                    'status' => SubmissionRequiredFile::STATUS_REJECTED,
                ]);
            }
        }

        $details = 'Memperbarui status pengajuan dengan nilai id: '.$submission->id;

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

        // send to whatsapp
        if ($request->status !== 'pending') {
            $this->whatsappHelper->submissionStatusChangeByVerifikator($submission, $verificator, $request->status);
        }

        return redirect()->back()->with('success', 'Pengajuan berhasil diperbarui');
    }

    public function updateBypass(Request $request, $type_slug, $id)
    {
        $dosen = User::where('id', auth()->user()->id)->firstOrFail();
        $submission = Submission::where('id', $id)->firstOrFail();

        if (!($dosen->position == 'Ketua Jurusan' || $dosen->position == 'Ketua Prodi')) {
            return redirect()->back()->with('failed', 'Gagal melakukan update data, anda tidak memiliki akses');
        }

        $details = 'Melakukan update bypass pengajuan dengan nilai id: '.$submission->id;

        $valCheckbox = 0;
        if ($request->checkboxBypass == 'on') {
            $valCheckbox = 1;
        }

        $submission->update([
            'bypass_pengajuan_kaprodi' => $valCheckbox,
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        return redirect()->back()->with('success', 'Status bypass pengecekan seminar/sidang untuk pengajuan ini berhasil diperbarui');
    }

    public function showDiscussion($type_slug, $id)
    {
        $auth_dosen = User::where('id', auth()->user()->id)->firstOrFail();
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $id)->where('type_id', $type->id)->firstOrFail();

        $discussions = SubmissionConsultation::where('submission_id', $submission->id)->orderBy('created_at', 'desc')->get();

        $verificator = Verificator::where('submission_id', $id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->save();
        }

        $notifications = Notification::where('to_user_id', auth()->user()->id)->where('submission_id', $submission->id)->update(['is_read' => true]);

        $idSidangHasil = 0;
        if ($type_slug == 'sidang-akhir') {
            $idSpota = $submission->spota_submission_id;
            $submissionSidangHasil = Submission::where('spota_submission_id', $idSpota)->where('type_id', '2')->orderBy('id', 'desc')->first();
            if ($submissionSidangHasil != null) {
                $idSidangHasil = $submissionSidangHasil->id;
            }
        }

        $idSeminarProposal = 0;
        if ($type_slug == 'sidang-hasil') {
            $idSpota = $submission->spota_submission_id;
            $submissionSeminarProposal = Submission::where('spota_submission_id', $idSpota)->where('type_id', '1')->orderBy('id', 'desc')->first();
            if ($submissionSeminarProposal != null) {
                $idSeminarProposal = $submissionSeminarProposal->id;
            }
        }

        return view('panel.dosen.pengajuan.show.discussions', compact('type', 'submission', 'discussions', 'verificator', 'auth_dosen', 'idSidangHasil', 'idSeminarProposal'));
    }

    public function addDiscussion(Request $request, $type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $dosen = User::where('id', auth()->user()->id)->firstOrFail();

        $request->validate([
            'komentar' => 'required',
        ]);

        $submission = Submission::where('is_ready_to_be_processed', 1)->where('type_id', $type->id)->where('id', $id)->whereHas('verificators', function ($q) use ($dosen) {
            $q->where('user_id', $dosen->id);
        })->firstOrFail();

        if (!($submission->status == Submission::STATUS_PENDING || $submission->status == Submission::STATUS_PROCESSED)) {
            return redirect()->back()->with('failed', 'Pengajuan sudah dikonfirmasi, tidak bisa menambah komentar');
        }

        SubmissionConsultation::create([
            'submission_id' => $submission->id,
            'user_id' => $dosen->id,
            'description' => $request->komentar,
        ]);

        $details = 'Menambah komentar pada pengajuan dengan nilai id: '.$submission->id;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        $verificatorAddNewDiscussionNotificationQueue = new VerificatorAddNewDiscussionNotificationQueue($submission, $dosen->id, now()->format('Y-m-d H:i:s'));
        dispatch($verificatorAddNewDiscussionNotificationQueue)->delay(Carbon::now()->addSeconds(5))->onQueue('whatsapp_notification_queue');

        return redirect()->back()->with('success', 'Komentar berhasil ditambahkan');
    }

    public function showVerificators($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $auth_dosen = User::where('id', auth()->user()->id)->where('role', User::ROLE_DOSEN)->firstOrFail();

        if (!($auth_dosen->position == 'Ketua Jurusan' || $auth_dosen->position == 'Ketua Prodi')) {
            return redirect()->back()->with('failed', 'Anda tidak memiliki hak akses untuk mengubah verifikator');
        }

        $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();

        $verificator = Verificator::where('submission_id', $submission_id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->save();
        }

        $dosens = [];

        if ($auth_dosen->position == 'Ketua Jurusan' || $auth_dosen->position == 'Ketua Prodi') {
            $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();
            $dosens = User::where('role', User::ROLE_DOSEN)->get();
        } else {
            $submission = Submission::where('is_ready_to_be_processed', 1)->where('type_id', $type->id)->where('id', $submission_id)->whereHas('verificators', function ($q) use ($auth_dosen) {
                $q->where('user_id', $auth_dosen->id);
            })->firstOrFail();
        }

        $idSidangHasil = 0;
        if ($type_slug == 'sidang-akhir') {
            $idSpota = $submission->spota_submission_id;
            $submissionSidangHasil = Submission::where('spota_submission_id', $idSpota)->where('type_id', '2')->orderBy('id', 'desc')->first();
            if ($submissionSidangHasil != null) {
                $idSidangHasil = $submissionSidangHasil->id;
            }
        }

        $idSeminarProposal = 0;
        if ($type_slug == 'sidang-hasil') {
            $idSpota = $submission->spota_submission_id;
            $submissionSeminarProposal = Submission::where('spota_submission_id', $idSpota)->where('type_id', '1')->orderBy('id', 'desc')->first();
            if ($submissionSeminarProposal != null) {
                $idSeminarProposal = $submissionSeminarProposal->id;
            }
        }

        return view('panel.dosen.pengajuan.show.verificators', compact('type', 'submission', 'dosens', 'verificator', 'auth_dosen', 'idSidangHasil', 'idSeminarProposal'));
    }

    public function updateVerificators($type_slug, $submission_id, Request $request)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $dosen = User::where('id', auth()->user()->id)->where('role', User::ROLE_DOSEN)->firstOrFail();

        if (!($dosen->position == 'Ketua Jurusan' || $dosen->position == 'Ketua Prodi')) {
            return redirect()->back()->with('failed', 'Anda tidak memiliki hak akses untuk mengubah verifikator');
        }

        $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();

        if (!($submission->status == Submission::STATUS_PENDING || $submission->status == Submission::STATUS_PROCESSED)) {
            return redirect()->back()->with('failed', 'Pengajuan sudah dikonfirmasi, tidak bisa menambah komentar');
        }

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

                /* Update Penguji here*/
                $penguji1 = 0;
                $penguji2 = 0;
                $i = 0;
                foreach ($request->penguji_ids as $penguji_id) {
                    $dataPenguji = User::where('id', $penguji_id)->where('role', User::ROLE_DOSEN)->first();

                    if ($i == '0') {
                        $penguji1 = $dataPenguji->unique_id;
                    }

                    if ($i == '1') {
                        $penguji2 = $dataPenguji->unique_id;
                    }

                    ++$i;
                }

                $postPenguji = $this->spotaHelper->updatePengujiKeSpota($submission->spota_submission_id, $penguji1, $penguji2);
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

        $details = 'Memperbarui verifikator pada pengajuan dengan nilai id: '.$submission->id;

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

        return redirect()->back()->with('success', 'Verifikator berhasil diperbarui');
    }

    public function showRequiredFiles($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $auth_dosen = User::where('id', auth()->user()->id)->where('role', User::ROLE_DOSEN)->firstOrFail();

        $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $submission_id)->where('type_id', $type->id)->whereHas('verificators', function ($q) use ($auth_dosen) {
            $q->where('user_id', $auth_dosen->id);
        })->firstOrFail();

        $verificator = Verificator::where('submission_id', $submission_id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->save();
        }

        $idSidangHasil = 0;
        if ($type_slug == 'sidang-akhir') {
            $idSpota = $submission->spota_submission_id;
            $submissionSidangHasil = Submission::where('spota_submission_id', $idSpota)->where('type_id', '2')->orderBy('id', 'desc')->first();
            if ($submissionSidangHasil != null) {
                $idSidangHasil = $submissionSidangHasil->id;
            }
        }

        $idSeminarProposal = 0;
        if ($type_slug == 'sidang-hasil') {
            $idSpota = $submission->spota_submission_id;
            $submissionSeminarProposal = Submission::where('spota_submission_id', $idSpota)->where('type_id', '1')->orderBy('id', 'desc')->first();
            if ($submissionSeminarProposal != null) {
                $idSeminarProposal = $submissionSeminarProposal->id;
            }
        }

        return view('panel.dosen.pengajuan.show.required_files', compact('type', 'submission', 'verificator', 'auth_dosen', 'idSidangHasil', 'idSeminarProposal'));
    }

    public function showEvaluation($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $auth_dosen = User::where('id', auth()->user()->id)->where('role', User::ROLE_DOSEN)->firstOrFail();

        $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $submission_id)->where('type_id', $type->id)->whereHas('verificators', function ($q) use ($auth_dosen) {
            $q->where('user_id', $auth_dosen->id);
        })->firstOrFail();

        $verificator_pembimbings = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PEMBIMBING)->orderBy('created_at', 'asc')->get();
        $verificator_penguji = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PENGUJI)->orderBy('created_at', 'asc')->get();

        $verificators = $verificator_pembimbings->merge($verificator_penguji);
        $verificator = Verificator::where('submission_id', $submission_id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->save();

            if ($verificator->status == Verificator::STATUS_PENDING) {
                return redirect()->back()->with('failed', 'Anda belum memberikan persetujuan seminar/sidang dan penilaian untuk pengajuan ini');
            }
        }

        $idSidangHasil = 0;
        if ($type_slug == 'sidang-akhir') {
            $idSpota = $submission->spota_submission_id;
            $submissionSidangHasil = Submission::where('spota_submission_id', $idSpota)->where('type_id', '2')->orderBy('id', 'desc')->first();
            if ($submissionSidangHasil != null) {
                $idSidangHasil = $submissionSidangHasil->id;
            }
        }

        $idSeminarProposal = 0;
        if ($type_slug == 'sidang-hasil') {
            $idSpota = $submission->spota_submission_id;
            $submissionSeminarProposal = Submission::where('spota_submission_id', $idSpota)->where('type_id', '1')->orderBy('id', 'desc')->first();
            if ($submissionSeminarProposal != null) {
                $idSeminarProposal = $submissionSeminarProposal->id;
            }
        }

        return view('panel.dosen.pengajuan.show.evaluation', compact('type', 'submission', 'verificator', 'auth_dosen', 'verificators', 'idSidangHasil', 'idSeminarProposal'));
    }

    public function updateEvaluation($type_slug, $submission_id, Request $request)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $dosen = User::where('id', auth()->user()->id)->where('role', User::ROLE_DOSEN)->firstOrFail();

        $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $submission_id)->where('type_id', $type->id)->whereHas('verificators', function ($q) use ($dosen) {
            $q->where('user_id', $dosen->id);
        })->firstOrFail();

        $verificator = Verificator::where('user_id', $dosen->id)->where('submission_id', $submission->id)->firstOrFail();

        $verificator->update([
            'note' => $request->note,
        ]);

        if (
            $submission->format->required_values &&
            ($submission->status == 'pending' || $submission->status == 'processed')
        ) {
            foreach ($submission->format->required_values as $submission_format_required_value) {
                if ($request->required_values[$submission_format_required_value->id] === null) {
                    continue;
                }

                if ($request->required_values[$submission_format_required_value->id]) {
                    $request->validate([
                        'required_values.'.$submission_format_required_value->id => 'required|min:0|max:100|numeric',
                    ]);
                }

                $submission_required_value = $submission->required_values()->where('required_value_id', $submission_format_required_value->id)->where('verificator_id', $verificator->id)->first();
                if ($submission_required_value) {
                    $submission_required_value->update([
                        'value' => $request->required_values[$submission_format_required_value->id],
                    ]);
                } else {
                    SubmissionRequiredValue::create([
                        'submission_id' => $submission->id,
                        'value' => $request->required_values[$submission_format_required_value->id],
                        'required_value_id' => $submission_format_required_value->id,
                        'verificator_id' => $verificator->id,
                    ]);
                }
            }

            $total_average_score = 0;
            $totalVerifikator = 0;
            $verificators = Verificator::where('submission_id', $submission->id)->get();
            foreach ($verificators as $i => $verificator) {
                $verificator_submission_required_values = SubmissionRequiredValue::where('submission_id', $submission->id)->where('verificator_id', $verificator->id)->get();
                $total_score = 0;
                if ($verificator_submission_required_values->count() != $submission->format->required_values->count()) {
                    continue;
                //$total_average_score = 0;
                    //break;
                } else {
                    ++$totalVerifikator;
                    foreach ($verificator_submission_required_values as $verificator_submission_required_value) {
                        $total_score += $verificator_submission_required_value->value * ($verificator_submission_required_value->required_value->weight / 100);
                    }
                }
                $total_average_score += $total_score;
            }

            if ($total_average_score != 0) {
                //$total_average_score /= $verificators->count();
                $total_average_score /= $totalVerifikator;

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

        $details = 'Memperbarui penilaian pada pengajuan dengan nilai id: '.$submission->id;

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

        return redirect()->back()->with('success', 'Penilaian berhasil diperbarui');
    }

    public function makeResultSubmission($type_slug, $submission_id)
    {
        $auth_dosen = User::where('id', auth()->user()->id)->firstOrFail();
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();
        $student = User::where('id', $submission->student_id)->firstOrFail();

        $verificator = Verificator::where('submission_id', $submission_id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->save();
        }

        $idSidangHasil = 0;
        if ($type_slug == 'sidang-akhir') {
            $idSpota = $submission->spota_submission_id;
            $submissionSidangHasil = Submission::where('spota_submission_id', $idSpota)->where('type_id', '2')->orderBy('id', 'desc')->first();
            if ($submissionSidangHasil != null) {
                $idSidangHasil = $submissionSidangHasil->id;
            }
        }

        $idSeminarProposal = 0;
        if ($type_slug == 'sidang-hasil') {
            $idSpota = $submission->spota_submission_id;
            $submissionSeminarProposal = Submission::where('spota_submission_id', $idSpota)->where('type_id', '1')->orderBy('id', 'desc')->first();
            if ($submissionSeminarProposal != null) {
                $idSeminarProposal = $submissionSeminarProposal->id;
            }
        }

        return view('panel.dosen.pengajuan.show.make_result', compact(
            'student',
            'submission',
            'type',
            'verificator',
            'auth_dosen',
            'idSidangHasil',
            'idSeminarProposal'
        ));
    }

    public function makeResultSubmissionUpdate(Request $request, $type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $dosen = User::where('id', auth()->user()->id)->where('role', User::ROLE_DOSEN)->firstOrFail();

        $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $submission_id)->where('type_id', $type->id)->whereHas('verificators', function ($q) use ($dosen) {
            $q->where('user_id', $dosen->id);
        })->firstOrFail();

        $verificator = Verificator::where('user_id', $dosen->id)->where('submission_id', $submission->id)->firstOrFail();

        if ($verificator->type != Verificator::TYPE_PEMBIMBING) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk memutuskan hasil pengajuan');
        }

        if ($submission->verificator_id_who_submit_result != null && $submission->verificator_id_who_submit_result != $verificator->id) {
            return redirect()->back()->with('failed', 'Keputusan hasil sidang sudah ditetapkan oleh: '.$verificator->user->name);
        }

        $recomendation_repeated_schedule = $submission->recomendation_repeated_schedule;

        $result_hasil_sidang = $submission->result_status;
        $request->validate([
            'hasil_sidang' => 'required|in:'.Submission::RESULT_PENDING.','.Submission::RESULT_APPROVED.','.Submission::RESULT_REJECTED.','.Submission::RESULT_REPEATED.','.Submission::RESULT_APPROVED_WITH_FIXED,
        ]);

        if ($request->hasil_sidang && $request->hasil_sidang == Submission::RESULT_REPEATED) {
            $request->validate([
                'rekomendasi_waktu_sidang_ulang' => 'required|date_format:d-m-Y H:i',
            ]);

            $recomendation_repeated_schedule = Carbon::parse($request->rekomendasi_waktu_sidang_ulang)->format('Y-m-d H:i:s');
        }

        $result_hasil_sidang = $request->hasil_sidang;

        $submission->update([
            'result_status' => $result_hasil_sidang,
            'recomendation_repeated_schedule' => $recomendation_repeated_schedule,
            'verificator_id_who_submit_result' => $verificator->id,
            'updated_submit_result_at' => Carbon::now(),
            'next_title_recomendation' => $request->rekomendasi_perubahan_judul,
            'record_url' => $request->hasil_rekaman_sidang,
        ]);

        $details = 'Memperbarui hasil status seminar pada pengajuan dengan nilai id: '.$submission->id;

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

        return redirect()->back()->with('success', 'Hasil sidang berhasil diperbarui');
    }

    public function resultSubmission($type_slug, $submission_id)
    {
        $auth_dosen = User::where('id', auth()->user()->id)->firstOrFail();
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();
        $student = User::where('id', $submission->student_id)->firstOrFail();

        $verificator = Verificator::where('submission_id', $submission_id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->save();
        }

        $idSidangHasil = 0;
        if ($type_slug == 'sidang-akhir') {
            $idSpota = $submission->spota_submission_id;
            $submissionSidangHasil = Submission::where('spota_submission_id', $idSpota)->where('type_id', '2')->orderBy('id', 'desc')->first();
            if ($submissionSidangHasil != null) {
                $idSidangHasil = $submissionSidangHasil->id;
            }
        }

        $idSeminarProposal = 0;
        if ($type_slug == 'sidang-hasil') {
            $idSpota = $submission->spota_submission_id;
            $submissionSeminarProposal = Submission::where('spota_submission_id', $idSpota)->where('type_id', '1')->orderBy('id', 'desc')->first();
            if ($submissionSeminarProposal != null) {
                $idSeminarProposal = $submissionSeminarProposal->id;
            }
        }

        return view('panel.dosen.pengajuan.show.result', compact('student', 'submission', 'type', 'verificator', 'auth_dosen', 'idSidangHasil', 'idSeminarProposal'));
    }

    public function revisionSubmission($type_slug, $submission_id)
    {
        $auth_dosen = User::where('id', auth()->user()->id)->firstOrFail();
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();
        $student = User::where('id', $submission->student_id)->firstOrFail();

        $verificator = Verificator::where('submission_id', $submission_id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->save();
        }

        $idSidangHasil = 0;
        if ($type_slug == 'sidang-akhir') {
            $idSpota = $submission->spota_submission_id;
            $submissionSidangHasil = Submission::where('spota_submission_id', $idSpota)->where('type_id', '2')->orderBy('id', 'desc')->first();
            if ($submissionSidangHasil != null) {
                $idSidangHasil = $submissionSidangHasil->id;
            }
        }

        $idSeminarProposal = 0;
        if ($type_slug == 'sidang-hasil') {
            $idSpota = $submission->spota_submission_id;
            $submissionSeminarProposal = Submission::where('spota_submission_id', $idSpota)->where('type_id', '1')->orderBy('id', 'desc')->first();
            if ($submissionSeminarProposal != null) {
                $idSeminarProposal = $submissionSeminarProposal->id;
            }
        }

        return view('panel.dosen.pengajuan.show.revisi', compact('student', 'submission', 'type', 'verificator', 'auth_dosen', 'idSidangHasil', 'idSeminarProposal'));
    }

    public function revisionSubmissionUpdate(Request $request, $type_slug, $submission_id)
    {
        $request->validate([
            'status_final_revisi' => 'required|in:pending,approved,rejected',
        ]);

        $auth_dosen = User::where('id', auth()->user()->id)->firstOrFail();
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();

        $verificator = Verificator::where('submission_id', $submission_id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->final_status = $request->status_final_revisi;
            $verificator->save();

            // send notification whatsapp
            if ($request->status_final_revisi != 'pending') {
                $this->whatsappHelper->submissionRevisiStatusChangeByVerifikator($submission, $verificator, $request->status_final_revisi);
            }
        }

        $this->functionHelper->createNewNextSubmission($submission->id);

        return redirect()->back()->with('success', 'Berhasil memperbaharui status final revisi hasil pengajuan mahasiswa');
    }

    public function publicIndex()
    {
        $submissions = Submission::where('is_ready_to_be_processed', 1)->whereHas('format', function ($q) {
            $q->where('is_all_lecturers_can_consultations', true);
        })->orderBy('id', 'desc')->get();

        return view('panel.dosen.pengajuan-public.index', compact('submissions'));
    }

    public function publicShow($id)
    {
        $submission = Submission::whereHas('format', function ($q) {
            $q->where('is_all_lecturers_can_consultations', true);
        })->where('id', $id)->firstOrFail();

        return view('panel.dosen.pengajuan-public.show', compact('submission'));
    }

    public function publicAddComment(Request $request, $id)
    {
        $dosen = User::where('id', auth()->user()->id)->firstOrFail();

        $request->validate([
            'komentar' => 'required',
        ]);

        $submission = Submission::whereHas('format', function ($q) {
            $q->where('is_all_lecturers_can_consultations', true);
        })->where('id', $id)->firstOrFail();

        if (!($submission->status == Submission::STATUS_PENDING || $submission->status == Submission::STATUS_PROCESSED)) {
            return redirect()->back()->with('failed', 'Pengajuan sudah dikonfirmasi, tidak bisa menambah komentar');
        }

        SubmissionConsultation::create([
            'submission_id' => $submission->id,
            'user_id' => $dosen->id,
            'description' => $request->komentar,
        ]);

        // notification to student
        $student = User::where('role', User::ROLE_MAHASISWA)->where('id', $submission->student_id)->first();
        Notification::create([
            'action' => 'Memiliki diskusi baru pengajuan',
            'submission_id' => $submission->id,
            'from_user_id' => auth()->user()->id,
            'to_user_id' => $student->id,
            'url' => route('panel.mahasiswa.pengajuan.required_files.show', ['type_slug' => $submission->type->slug, 'id' => $submission->id]),
        ]);

        $details = 'Menambah komentar pada pengajuan dengan nilai id: '.$submission->id;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        return redirect()->back()->with('success', 'Komentar berhasil ditambahkan');
    }
}
