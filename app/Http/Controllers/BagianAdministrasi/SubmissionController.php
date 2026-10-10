<?php

namespace App\Http\Controllers\BagianAdministrasi;

use App\Helpers\FunctionHelper;
use App\Helpers\SpotaHelper;
use App\Helpers\WhatsappHelper;
use App\Http\Controllers\Controller;
use App\Jobs\BagianAdministrasiAddNewDiscussionNotificationQueue;
use App\Jobs\SubmissionScheduleReminder;
use App\Jobs\SubmissionSetSchedule;
use App\Models\ActivitySubmissionLog;
use App\Models\ActivityUserLog;
use App\Models\Format;
use App\Models\Notification;
use App\Models\RequiredValue;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\SubmissionConsultation;
use App\Models\Type;
use App\Models\User;
use App\Models\Verificator;
use Carbon\Carbon;
use Google\Service\ServiceControl\Auth;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    private $functionHelper;
    private $whatsappHelper;
    private $spotaHelper;

    public function __construct()
    {
        $this->functionHelper = new FunctionHelper();

        $wa_api_key = Setting::where('key', 'wa_api_key')->firstOrFail();
        $wa_sender_phone_number = Setting::where('key', 'wa_sender_phone_number')->firstOrFail();
        $this->whatsappHelper = new WhatsappHelper($wa_api_key->value, $wa_sender_phone_number->value);

        $this->spotaHelper = new SpotaHelper();
    }

    public function index(Request $request, $type_slug)
    {
        $search = $request->cari;
        $status = $request->status_pengajuan;
        $result_status = $request->hasil_keputusan;
        $angkatan = $request->angkatan;
        $status_lihat = $request->status_lihat;
        $sort_by = $request->urut_berdasarkan ?: 'asc';
        $jadwal_seminar = $request->jadwal_seminar;
        $tim = $request->tim;

        $type = Type::where('slug', $type_slug)->firstOrFail();

        $q_submissions = Submission::with(['student', 'skill_group'])->where('is_ready_to_be_processed', 1);

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
            $q_submissions->orderBy('ready_at', $sort_by);
        });

        $q_submissions->when($jadwal_seminar, function ($q) use ($jadwal_seminar) {
            if ($jadwal_seminar == 'Belum ditentukan') {
                $q->where('schedule', null);
            } elseif ($jadwal_seminar == 'Telah ditentukan') {
                $q->where('schedule', '!=', null);
            }
        });

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

        $q_submissions->when($status_lihat, function ($q) use ($status_lihat) {
            if ($status_lihat == 'Belum Dilihat') {
                $q->where('is_read_by_tata_usaha', 0);
            } elseif ($status_lihat == 'Telah Dilihat') {
                $q->where('is_read_by_tata_usaha', 1);
            }
        });

        $submissions = $q_submissions->where('type_id', $type->id)->paginate(10);
        $submissions->appends(request()->query());

        return view('panel.bagian-administrasi.pengajuan.index', compact('type', 'submissions'));
    }

    public function showRequiredFiles($type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $id)->where('is_ready_to_be_processed', 1)->where('type_id', $type->id)->whereHas('format', function ($q) {
            $q->where('is_tata_usaha_required', 1);
        })->firstOrFail();

        $submission->update([
            'is_read_by_tata_usaha' => 1,
        ]);

        $notifications = Notification::where('to_user_id', auth()->user()->id)->where('submission_id', $submission->id)->update(['is_read' => true]);

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

        return view('panel.bagian-administrasi.pengajuan.show.required_files', compact('type', 'submission', 'banyakKehadiran'));
    }

    public function updateRequiredFiles($type_slug, $id, Request $request)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $id)->where('is_ready_to_be_processed', 1)->where('type_id', $type->id)->firstOrFail();
        $student = User::where('id', $submission->student_id)->where('role', User::ROLE_MAHASISWA)->firstOrFail();

        $request->validate([
            'status_tandatangan' => 'required|in:approved,rejected,pending',
        ]);

        // check if required_file_status has rejected
        $hasRejectedFile = false;

        if ($request->required_file_status) {
            foreach ($request->required_file_status as $required_file_id => $value) {
                if ($value === 'rejected') {
                    $hasRejectedFile = true;
                }
            }
        }

        if ($request->status_tandatangan == 'rejected') {
            $hasRejectedFile = true;
        }

        if ($hasRejectedFile) {
            $request->validate([
                'catatan_penolakan' => 'required',
            ]);
        }

        if ($request->required_file_status) {
            foreach ($request->required_file_status as $required_file_id => $value) {
                $submission->required_files()->where('required_file_id', $required_file_id)->first()->update([
                    'status' => $value,
                ]);
            }
        }

        $student->update([
            'signature_status' => $request->status_tandatangan,
        ]);

        $message_feedback = 'Berhasil perbarui file yang diperlukan';

        if ($submission->required_files()->where('status', 'rejected')->count() > 0 || $request->status_tandatangan == 'rejected') {
            $is_success = $this->whatsappHelper->submission_files_status_changed_by_staff_administrator($submission, $request->catatan_penolakan);

            if ($is_success == true) {
                $message_feedback = 'Berhasil perbarui status validasi berkas dan mengirimkan notifikasi serta catatan penolakan pada berkas yang ditolak ke mahasiswa.';
            } else {
                $message_feedback = 'Berhasil perbarui status validasi berkas namun, gagal mengirimkan notifikasi catatan penolakan pada berkas yang ditolak ke mahasiswa. Harap hubungi admin.';
            }
        }

        // notification to student
        $student = User::where('role', User::ROLE_MAHASISWA)->where('id', $submission->student_id)->first();
        Notification::create([
            'action' => 'Memperbarui Status Berkas Pengajuan',
            'submission_id' => $submission->id,
            'from_user_id' => auth()->user()->id,
            'to_user_id' => $student->id,
            'url' => route('panel.mahasiswa.pengajuan.required_files.show', ['type_slug' => $type->slug, 'id' => $submission->id]),
        ]);

        $details = 'Memperbarui Status Berkas Pengajuan dengan niai id: '.$submission->id;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        if ($hasRejectedFile) {
            SubmissionConsultation::create([
                'submission_id' => $submission->id,
                'user_id' => auth()->user()->id,
                'description' => $request->catatan_penolakan,
            ]);

            $verificators = Verificator::where('submission_id', $submission->id)->get();
            foreach ($verificators as $verificator) {
                // notification to verificator
                Notification::create([
                    'action' => 'Memiliki diskusi baru pada pengajuan',
                    'submission_id' => $submission->id,
                    'from_user_id' => auth()->user()->id,
                    'to_user_id' => $verificator->user_id,
                    'url' => route('panel.dosen.pengajuan.required_files.show', ['type_slug' => $submission->type->slug, 'id' => $submission->id]),
                ]);
            }

            $details = 'Menambah catatan penolakan berkas pada pengajuan dengan nilai id: '.$submission->id;

            ActivityUserLog::create([
                'user_id' => auth()->user()->id,
                'details' => $details,
            ]);

            ActivitySubmissionLog::create([
                'submission_id' => $submission->id,
                'details' => $details,
                'user_id' => auth()->user()->id,
            ]);
        }

        $this->functionHelper->createNewNextSubmission($submission->id);

        return redirect()->back()->with('success', $message_feedback);
    }

    public function showSchedule($type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $id)->where('is_ready_to_be_processed', 1)->where('type_id', $type->id)->whereHas('format', function ($q) {
            $q->where('is_tata_usaha_required', 1);
        })->firstOrFail();

        $submission->update([
            'is_read_by_tata_usaha' => 1,
        ]);

        $notifications = Notification::where('to_user_id', auth()->user()->id)->where('submission_id', $submission->id)->update(['is_read' => true]);

        return view('panel.bagian-administrasi.pengajuan.show.schedule', compact('type', 'submission'));
    }

    public function updateSchedule($type_slug, $id, Request $request)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $id)->where('is_ready_to_be_processed', 1)->where('type_id', $type->id)->firstOrFail();

        $request->validate([
            'jumlah_sks' => 'required',
            'ipk' => 'required',
            'ruangan' => 'required',
        ]);

        $submission_schedule = $request->jadwal;
        $schedule_id = $submission->schedule_id;
        $room = $request->ruangan;

        if ($request->jadwal_sidang) {
            $request->validate([
                'jadwal_sidang' => 'required|date_format:d-m-Y H:i',
            ]);

            $submission_schedule = Carbon::parse($request->jadwal_sidang)->format('Y-m-d H:i:s');

            if ($submission_schedule || $submission->schedule_id) {
                $sync_schedule = $this->spotaHelper->syncSchedule($submission, $submission_schedule, $room, $schedule_id);
                if ($sync_schedule['status'] !== true) {
                    return redirect()->back()->with('failed', $sync_schedule['msg']);
                } else {
                    if (array_key_exists('id', $sync_schedule)) {
                        $schedule_id = $sync_schedule['id'];
                    }
                }
            }
        } elseif ($submission_schedule === null && $schedule_id !== null) {
            $sync_schedule = $this->spotaHelper->syncSchedule($submission, $submission_schedule, $room, $schedule_id);
            if ($sync_schedule['status'] !== true) {
                return redirect()->back()->with('failed', $sync_schedule['msg']);
            } else {
                $schedule_id = null;
            }
        }

        $submission->update([
            'schedule' => $submission_schedule,
            'room' => $room,
            'amount_sks_now' => $request->jumlah_sks,
            'ipk_now' => $request->ipk,
            'schedule_id' => $schedule_id,
        ]);

        // notification to student
        $student = User::where('role', User::ROLE_MAHASISWA)->where('id', $submission->student_id)->first();
        Notification::create([
            'action' => 'Memperbarui Jadwal Sidang',
            'submission_id' => $submission->id,
            'from_user_id' => auth()->user()->id,
            'to_user_id' => $student->id,
            'url' => route('panel.mahasiswa.pengajuan.required_files.show', ['type_slug' => $type->slug, 'id' => $submission->id]),
        ]);

        $details = 'Memperbarui jadwal Sidang Pengajuan dengan niai id: '.$submission->id;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        $message = 'Berhasil perbarui informasi jadwal seminar';

        $now = Carbon::now();
        $different_time = $now->diffInMinutes(Carbon::parse($submission_schedule));

        if ($submission->schedule !== null && $submission_schedule > $now) {
            // if ($submission->schedule !== $latest_submission_schedule) {
            //     $submission_set_schedule = new SubmissionSetSchedule($submission);
            //     dispatch($submission_set_schedule);
            // }

            $submission_schedule_reminder = new SubmissionScheduleReminder($submission, $submission->schedule);

            if ($different_time > 1440) {
                dispatch($submission_schedule_reminder)->delay(Carbon::parse($submission->schedule)->subDays(1))->onQueue('whatsapp_notification_queue');
                dispatch($submission_schedule_reminder)->delay(Carbon::parse($submission->schedule)->subHours(1))->onQueue('whatsapp_notification_queue');
            }

            if ($different_time > 60 && $different_time < 1440) {
                dispatch($submission_schedule_reminder)->delay(Carbon::parse($submission->schedule)->subHours(1))->onQueue('whatsapp_notification_queue');
            }

            // if ($different_time > 0 && $different_time < 60) {
            //     if ($submission->schedule == $latest_submission_schedule) {
            //         $submission_set_schedule = new SubmissionSetSchedule($submission);
            //         dispatch($submission_set_schedule);
            //     }
            // }

            $message = 'Berhasil perbarui informasi jadwal seminar dan notifikasi secara whatsapp akan dikirimkan ke tim dan mahasiswa';
        }

        $this->functionHelper->createNewNextSubmission($submission->id);

        return redirect()->back()->with('success', $message);
    }

    public function showEvaluation($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('is_ready_to_be_processed', 1)->where('type_id', $type->id)->whereHas('format', function ($q) {
            $q->where('is_tata_usaha_required', 1);
        })->firstOrFail();

        $submission->update([
            'is_read_by_tata_usaha' => 1,
        ]);

        $verificator_pembimbings = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PEMBIMBING)->orderBy('created_at', 'asc')->get();
        $verificator_penguji = Verificator::where('submission_id', $submission->id)->where('type', Verificator::TYPE_PENGUJI)->orderBy('created_at', 'asc')->get();

        $verificators = $verificator_pembimbings->merge($verificator_penguji);
        $notifications = Notification::where('to_user_id', auth()->user()->id)->where('submission_id', $submission->id)->update(['is_read' => true]);

        // get all required_values
        $format = Format::where('type_id', $type->id)->where('id', $submission->format_id)->first();
        $required_values = RequiredValue::where('format_id', $format->id)->first();

        return view('panel.bagian-administrasi.pengajuan.show.evaluation', compact('type', 'submission', 'verificators', 'format', 'required_values'));
    }

    public function requestPembimbing($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('type_id', $type->id)->where('id', $submission_id)->firstOrFail();

        $pembimbings = Verificator::where('submission_id', $submission->id)->where('type', 'Pembimbing')->get();
        if ($pembimbings->count() > 0) {
            return redirect()->back()->with('failed', 'Pembimbing sudah ditetapkan');
        }

        $is_success = $this->whatsappHelper->requestVerificator($submission, 'Pembimbing');

        if ($is_success == true) {
            return redirect()->back()->with('success', 'Berhasil melakukan request pembimbing.');
        } else {
            return redirect()->back()->with('failed', 'Gagal melakukan request pembimbing, harap hubungi admin.');
        }
    }

    public function requestPenguji($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('type_id', $type->id)->where('id', $submission_id)->firstOrFail();

        $pengujis = Verificator::where('submission_id', $submission->id)->where('type', 'Penguji')->get();
        if ($pengujis->count() > 0) {
            return redirect()->back()->with('failed', 'Penguji sudah ditetapkan');
        }

        $is_success = $this->whatsappHelper->requestVerificator($submission, 'Penguji');

        if ($is_success == true) {
            return redirect()->back()->with('success', 'Berhasil melakukan request penguji.');
        } else {
            return redirect()->back()->with('failed', 'Gagal melakukan request penguji, harap hubungi admin.');
        }
    }

    public function bulkSchedule($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('type_id', $type->id)->where('id', $submission_id)->firstOrFail();

        if ($submission->schedule == null) {
            return redirect()->back()->with('failed', 'Jadwal sidang belum ditentukan, silahkan tentukan terlebih dahulu.');
        }

        $is_success = $this->whatsappHelper->bulkSchedule($submission);

        if ($is_success == true) {
            return redirect()->back()->with('success', 'Berhasil melakukan pengiriman jadwal ke semua pihak.');
        } else {
            return redirect()->back()->with('failed', 'Gagal melakukan pengiriman jadwal ke semua pihak, harap hubungi admin.');
        }
    }

    public function requestApproval($type_slug, $submission_id, $verificator_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('type_id', $type->id)->where('id', $submission_id)->firstOrFail();
        $verificator = Verificator::where('id', $verificator_id)->where('submission_id', $submission->id)->firstOrFail();

        if ($verificator->status !== 'pending') {
            return redirect()->back()->with('failed', 'Verificator sudah menetapkan status persetujuan');
        }

        $is_success = $this->whatsappHelper->requestApproval($submission, $verificator);

        if ($is_success == true) {
            return redirect()->back()->with('success', 'Berhasil melakukan request persetujuan pengajuan mahasiswa ke verifikator.');
        } else {
            return redirect()->back()->with('failed', 'Gagal melakukan request persetujuan pengajuan mahasiswa ke verifikator, harap hubungi admin.');
        }
    }

    public function resultSubmission($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();
        $student = User::where('id', $submission->student_id)->firstOrFail();

        $submission->update([
            'is_read_by_tata_usaha' => 1,
        ]);

        $notifications = Notification::where('to_user_id', auth()->user()->id)->where('submission_id', $submission->id)->update(['is_read' => true]);

        return view('panel.bagian-administrasi.pengajuan.show.result', compact('student', 'submission', 'type'));
    }

    public function revisiSubmission($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();
        $student = User::where('id', $submission->student_id)->firstOrFail();

        $submission->update([
            'is_read_by_tata_usaha' => 1,
        ]);

        $notifications = Notification::where('to_user_id', auth()->user()->id)->where('submission_id', $submission->id)->update(['is_read' => true]);

        return view('panel.bagian-administrasi.pengajuan.show.revisi', compact(
            'student',
            'submission',
            'type'
        ));
    }

    public function showDiscussion($type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('type_id', $type->id)->where('id', $id)->firstOrFail();

        if ($submission->is_ready_to_be_processed === false) {
            return redirect()->back()->with('failed', 'Dapat diakses setelah anda melakukan aksi ajukan pengajuan');
        }

        $discussions = SubmissionConsultation::where('submission_id', $submission->id)->orderBy('created_at', 'desc')->get();

        return view('panel.bagian-administrasi.pengajuan.show.discussions', compact('type', 'submission', 'discussions'));
    }

    public function addDiscussion(Request $request, $type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();

        $request->validate([
            'komentar' => 'required',
        ]);

        $submission = Submission::where('type_id', $type->id)->where('id', $id)->firstOrFail();

        if ($submission->is_ready_to_be_processed === false) {
            return redirect()->back()->with('failed', 'Dapat diakses setelah anda melakukan aksi ajukan pengajuan');
        }

        if (!($submission->status == Submission::STATUS_PROCESSED || $submission->status == Submission::STATUS_PENDING)) {
            return redirect()->back()->with('failed', 'Pengajuan sudah dikonfirmasi, tidak bisa menambah komentar');
        }

        SubmissionConsultation::create([
            'submission_id' => $submission->id,
            'user_id' => auth()->user()->id,
            'description' => $request->komentar,
        ]);

        $verificators = Verificator::where('submission_id', $submission->id)->get();
        foreach ($verificators as $verificator) {
            // notification to verificator
            Notification::create([
                'action' => 'Memiliki diskusi baru pada pengajuan',
                'submission_id' => $submission->id,
                'from_user_id' => auth()->user()->id,
                'to_user_id' => $verificator->user_id,
                'url' => route('panel.dosen.pengajuan.required_files.show', ['type_slug' => $submission->type->slug, 'id' => $submission->id]),
            ]);
        }

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

        $bagianAdministrasiAddNewDiscussionQueue = new BagianAdministrasiAddNewDiscussionNotificationQueue($submission, now()->format('Y-m-d H:i:s'));
        dispatch($bagianAdministrasiAddNewDiscussionQueue)->delay(now()->addSeconds(5))->onQueue('whatsapp_notification_queue');

        return redirect()->back()->with('success', 'Komentar berhasil ditambahkan');
    }
}
