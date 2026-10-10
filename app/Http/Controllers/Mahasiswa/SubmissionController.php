<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Helpers\FunctionHelper;
use App\Helpers\GoogleDriveHelper;
use App\Helpers\WhatsappHelper;
use App\Http\Controllers\Controller;
use App\Jobs\DeleteFile;
use App\Jobs\StudentAddNewDiscussionNotificationQueue;
use App\Jobs\UploadUpdateFile;
use App\Jobs\UploadUpdateFinalDocumentFile;
use App\Models\ActivitySubmissionLog;
use App\Models\ActivityUserLog;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\SubmissionConsultation;
use App\Models\SubmissionRequiredFile;
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
    private $whatsappHelper;

    public function __construct()
    {
        $this->functionHelper = new FunctionHelper();
        $this->googleDriveHelper = new GoogleDriveHelper();

        $wa_api_key = Setting::where('key', 'wa_api_key')->firstOrFail();
        $wa_sender_phone_number = Setting::where('key', 'wa_sender_phone_number')->firstOrFail();
        $this->whatsappHelper = new WhatsappHelper($wa_api_key->value, $wa_sender_phone_number->value);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type_slug)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();

        // check if user is authorized to access this page
        $types = Type::whereHas('mahasiswaTypeAccessPermissions', function ($query) use ($type) {
            $query->where('mahasiswa_id', auth()->user()->id);
            $query->where('type_id', $type->id);
        })->first();

        // if not authorized, redirect to home
        if ($types === null) {
            return redirect()->route('panel.dashboard')->with('failed', 'Anda tidak memiliki akses ke halaman ini');
        }

        $submissions = Submission::where('type_id', $type->id)->orderBy('id', 'desc')->where('student_id', auth()->user()->id)->get();
        $submission_pending_count = Submission::where('type_id', $type->id)->where('student_id', auth()->user()->id)->where('status', 'pending')->count();
        $submission_rejected_count = Submission::where('type_id', $type->id)->where('student_id', auth()->user()->id)->where('status', 'rejected')->count();
        $submission_approved_count = Submission::where('type_id', $type->id)->where('student_id', auth()->user()->id)->where('status', 'approved')->count();
        $submission_processed_count = Submission::where('type_id', $type->id)->where('student_id', auth()->user()->id)->where('status', 'processed')->count();

        return view('panel.mahasiswa.pengajuan.index', compact('type', 'submissions', 'submission_pending_count', 'submission_rejected_count', 'submission_approved_count', 'submission_processed_count'));
    }

    public function updateFile(Request $request, $type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $student = User::where('id', auth()->user()->id)->firstOrFail();
        $submission = Submission::where('student_id', $student->id)->where('type_id', $type->id)->where('id', $id)->firstOrFail();
        $format = $submission->format;

        if (!($submission->status == Submission::STATUS_PROCESSED || $submission->status == Submission::STATUS_PENDING)) {
            return redirect()->back()->with('failed', 'Pengajuan sudah dikonfirmasi, tidak bisa menambah file');
        }

        foreach ($format->required_files as $format_required_file) {
            if ($request->hasFile($format_required_file->id)) {
                $submission_required_file = SubmissionRequiredFile::where('required_file_id', $format_required_file->id)->where('submission_id', $submission->id)->first();
                if ($submission_required_file && $submission_required_file->status === 'approved') {
                    return redirect()->back()->with('failed', 'Berkas wajib yang sudah disetujui tidak bisa diubah, jika ingin mengubah harap hubungi admin / Bagian Administrasi');
                }

                $folder = uniqid().'-'.now()->timestamp;
                $file_name = $student->unique_id.'-'.Str::slug($student->name).'-'.$type->slug.'-'.Str::slug($format_required_file->name).'-'.uniqid().'.'.$request->file($format_required_file->id)->getClientOriginalExtension();
                $request->file($format_required_file->id)->storeAs('submissions/tmp/'.$folder, $file_name, 'public');

                TemporaryFile::create([
                    'folder' => $folder,
                    'filename' => $file_name,
                ]);

                return response()->json($folder);
            }
        }

        return response()->json(['error' => 'Tidak ada file yang diupload'], 422);
    }

    public function showRequiredFiles($type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('student_id', auth()->user()->id)->where('id', $id)->where('type_id', $type->id)->firstOrFail();

        $notifications = Notification::where('to_user_id', auth()->user()->id)->where('submission_id', $submission->id)->update(['is_read' => true]);

        $banyakKehadiran = 0;

        switch ($type->id) {
            case '1':
                $resultKehadiran = \DB::select("SELECT * FROM submission_presences LEFT JOIN (SELECT id, type_id FROM submissions) t ON submission_presences.submission_id = t.id WHERE type_id = '1' AND submission_presences.unique_id = ?", [auth()->user()->unique_id]);
                $banyakKehadiran = sizeof($resultKehadiran);
                break;
            case '3':
                $resultKehadiran = \DB::select("SELECT * FROM submission_presences LEFT JOIN (SELECT id, type_id FROM submissions) t ON submission_presences.submission_id = t.id WHERE type_id = '3' AND submission_presences.unique_id = ?", [auth()->user()->unique_id]);
                $banyakKehadiran = sizeof($resultKehadiran);
                break;
        }

        return view('panel.mahasiswa.pengajuan.show.required_files', compact('type', 'submission', 'banyakKehadiran'));
    }

    public function updateRequiredFiles(Request $request, $type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $student = User::where('id', auth()->user()->id)->firstOrFail();
        $submission = Submission::where('student_id', $student->id)->where('id', $id)->where('type_id', $type->id)->firstOrFail();
        $format = $submission->format;

        if (!($submission->status == Submission::STATUS_PENDING || $submission->status == Submission::STATUS_PROCESSED)) {
            return redirect()->back()->with('failed', 'Pengajuan tidak dapat diubah');
        }

        $submission->update([
            'status' => Submission::STATUS_PENDING,
            // 'updated_at' => now(),
        ]);

        // if $format && required files more than one
        if ($format && $format->required_files->count() > 0) {
            foreach ($format->required_files as $format_required_file) {
                $request_required_file_id = str_replace('"', '', $request->input($format_required_file->id));

                if ($request_required_file_id) {
                    $submission_required_file = SubmissionRequiredFile::where('required_file_id', $format_required_file->id)->where('submission_id', $submission->id)->first();
                    if ($submission_required_file && $submission_required_file->status === 'approved') {
                        return redirect()->back()->with('failed', 'Berkas wajib yang sudah disetujui tidak bisa diubah, jika ingin mengubah harap hubungi admin / tata usaha');
                    }

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
                        'status' => 'pending',
                    ]);

                    if ($temporaryFile) {
                        // do queue jobs to upload to google drive
                        $uploadUpdateFile = new UploadUpdateFile($submission_required_file);
                        dispatch($uploadUpdateFile);

                        $temporaryFile->delete();
                    }
                }
            }
        }

        if ($submission->is_ready_to_be_processed) {
            // notification to all admin where the status was active
            $admins = User::where('role', User::ROLE_ADMIN)->where('status', 'active')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'action' => 'Memperbarui Berkas Pengajuan',
                    'submission_id' => $submission->id,
                    'from_user_id' => auth()->user()->id,
                    'to_user_id' => $admin->id,
                    'url' => route('panel.admin.pengajuan.show', ['type_slug' => $type->slug, 'id' => $submission->id]),
                ]);
            }

            // notification to all Bagian Administrasi
            $tata_usahas = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->where('status', 'active')->get();
            foreach ($tata_usahas as $tata_usaha) {
                Notification::create([
                    'action' => 'Memperbarui Berkas Pengajuan',
                    'submission_id' => $submission->id,
                    'from_user_id' => auth()->user()->id,
                    'to_user_id' => $tata_usaha->id,
                    'url' => route('panel.bagian-administrasi.pengajuan.required_files.show', ['type_slug' => $type->slug, 'id' => $submission->id]),
                ]);
            }

            $submission_required_files_can_edit_rejected_count = SubmissionRequiredFile::where('submission_id', $submission->id)->where('status', SubmissionRequiredFile::STATUS_REJECTED)->whereHas('required_file', function ($q) {
                $q->where('is_verificator_can_edit', 1);
            })->count();
            $verificator_whose_rejecteds = Verificator::where('submission_id', $submission->id)->where('status', Verificator::STATUS_REJECTED)->get();

            if ($submission_required_files_can_edit_rejected_count == 0 && $verificator_whose_rejecteds->count() > 0) {
                foreach ($verificator_whose_rejecteds as $verificator_whose_rejected) {
                    $verificator_whose_rejected->update([
                        'status' => Verificator::STATUS_PENDING,
                    ]);
                }

                // notif to verificator about files has been reuploaded
                $this->whatsappHelper->notificationToRejectedVerificatorThatFileHasBeenReuploaded($submission);
            }
        }

        $details = 'Memperbarui Berkas pengajuan dengan nilai id: '.$submission->id;

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

        if (
            !(
                SubmissionRequiredFile::where('url', '!=', null)->where('submission_id', $submission->id)->count() !== SubmissionRequiredFile::where('submission_id', $submission->id)->count()
                || $student->signature_image === null
            )
        ) {
            if ($submission->is_ready_to_be_processed) {
                $this->whatsappHelper->submission_files_pending_by_student($submission);
            }

            return redirect()->route('panel.mahasiswa.pengajuan.finish.show', [
                'type_slug' => $type->slug,
                'id' => $submission->id,
            ])->with('success', 'Berhasil upload semua berkas, menu ajukan proses telah dapat diakses');
        }

        return redirect()->back()->with('success', 'Berhasil simpan berkas');
    }

    public function finishSubmissionShow($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $student = User::where('id', auth()->user()->id)->first();
        $submission = Submission::where('student_id', $student->id)->where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();

        if (
            SubmissionRequiredFile::where('url', '!=', null)->where('submission_id', $submission->id)->count() !== SubmissionRequiredFile::where('submission_id', $submission->id)->count()
            || $student->signature_image === null
        ) {
            return redirect()->back()->with('failed', 'Berkas belum lengkap');
        }

        return view('panel.mahasiswa.pengajuan.show.finish', compact('student', 'submission', 'type'));
    }

    public function finishSubmissionUpdate(Request $request, $type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $student = User::where('id', auth()->user()->id)->first();
        $submission = Submission::where('student_id', $student->id)->where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();

        $type = Type::where('slug', $type_slug)->firstOrFail();
        $student = User::where('id', auth()->user()->id)->firstOrFail();
        $submission = Submission::where('student_id', $student->id)->where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();
        $format = $submission->format;

        if (!($submission->status == 'pending' || $submission->status == 'processed' || $submission->is_ready_to_be_processed)) {
            return redirect()->back()->with('failed', 'Pengajuan tidak dapat diubah');
        }

        $request->validate([
            'setuju_dengan_syarat_dan_ketentuan' => 'required',
        ]);

        if ($submission->format->is_need_trial_schedule && $request->permintaan_jadwal_pelaksanaan_seminar) {
            $request->validate([
                'permintaan_jadwal_pelaksanaan_seminar' => 'required|date_format:d-m-Y H:i|after: 5 days',
            ]);

            $schedule_request = Carbon::parse($request->permintaan_jadwal_pelaksanaan_seminar)->format('Y-m-d H:i:s');

            $submission->update([
                'schedule_request' => $schedule_request,
            ]);
        }

        $submission->is_ready_to_be_processed = $request->setuju_dengan_syarat_dan_ketentuan == 'true' ? 1 : 0;
        $submission->ready_at = Carbon::now();
        $submission->save();

        if ($submission->is_ready_to_be_processed == 1) {
            $this->whatsappHelper->requestApproval($submission);
        }

        // notification to all admin where the status was active
        $admins = User::where('role', User::ROLE_ADMIN)->where('status', 'active')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'action' => 'Mahasiswa telah mengubah status pengajuan menjadi siap untuk diproses',
                'submission_id' => $submission->id,
                'from_user_id' => auth()->user()->id,
                'to_user_id' => $admin->id,
                'url' => route('panel.admin.pengajuan.show', ['type_slug' => $type->slug, 'id' => $submission->id]),
            ]);
        }

        // notification to all Bagian Administrasi
        $tata_usahas = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->where('status', 'active')->get();
        foreach ($tata_usahas as $tata_usaha) {
            Notification::create([
                'action' => 'Mahasiswa telah mengubah status pengajuan menjadi siap untuk diproses',
                'submission_id' => $submission->id,
                'from_user_id' => auth()->user()->id,
                'to_user_id' => $tata_usaha->id,
                'url' => route('panel.bagian-administrasi.pengajuan.required_files.show', ['type_slug' => $type->slug, 'id' => $submission->id]),
            ]);
        }

        $details = 'Mahasiswa telah mengubah status pengajuan menjadi siap untuk diproses dengan nilai id: '.$submission->id;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        $this->whatsappHelper->submission_files_pending_by_student($submission);

        $submission->update([
            'status' => Submission::STATUS_PROCESSED,
        ]);

        return redirect()->back()->with('success', 'Berhasil mengubah status pengajuan menjadi siap untuk diproses, silahkan menunggu konfirmasi dari bagian administrasi');
    }

    public function resultSubmission($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $student = User::where('id', auth()->user()->id)->first();
        $submission = Submission::where('student_id', $student->id)->where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();

        if (
            !(
                $submission->is_ready_to_be_processed &&
                Verificator::where('submission_id', $submission->id)->where('status', 'approved')->count() == Verificator::where('submission_id', $submission->id)->count()
            )
        ) {
            return redirect()->back()->with('failed', 'Pengajuan anda belum memiliki hasil');
        }

        return view('panel.mahasiswa.pengajuan.show.result', compact('student', 'submission', 'type'));
    }

    public function revisiSubmission($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $student = User::where('id', auth()->user()->id)->first();
        $submission = Submission::where('student_id', $student->id)->where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();

        if (
            !(
                $submission->is_ready_to_be_processed &&
                Verificator::where('submission_id', $submission->id)->where('status', 'approved')->count() == Verificator::where('submission_id', $submission->id)->count()
            )
        ) {
            return redirect()->back()->with('failed', 'Pengajuan anda belum memiliki hasil');
        }

        return view('panel.mahasiswa.pengajuan.show.revisi', compact(
            'student',
            'submission',
            'type'
        ));
    }

    public function uploadFileFinalDocument(Request $request, $type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $student = User::where('id', auth()->user()->id)->firstOrFail();
        $submission = Submission::where('student_id', $student->id)->where('type_id', $type->id)->where('id', $submission_id)->firstOrFail();

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

    public function revisiSubmissionUpdate(Request $request, $type_slug, $submission_id)
    {
        $student = User::where('id', auth()->user()->id)->firstOrFail();
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('student_id', $student->id)->where('type_id', $type->id)->where('id', $submission_id)->firstOrFail();

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
            if ($temporaryFile) {
                // move storage from tmp to submissions
                Storage::move('public/submissions/tmp/'.$temporaryFile->folder.'/'.$temporaryFile->filename, 'public/submissions/'.$temporaryFile->filename);
                $file_url = asset('storage/submissions/'.$temporaryFile->filename);
                $file_name = $temporaryFile->filename;
            }
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

            if ($temporaryFile) {
                $temporaryFile->delete();
            }
        }

        // updated verificator who rejected to pending
        $rejected_verificators = Verificator::where('submission_id', $submission->id)->where('final_status', Verificator::FINAL_STATUS_REJECTED)->get();
        foreach ($rejected_verificators as $rejected_verificator) {
            $rejected_verificator->update([
                'final_status' => Verificator::FINAL_STATUS_PENDING,
            ]);
        }

        $this->whatsappHelper->requestApprovalRevision($submission);

        return redirect()->back()->with('success', 'Berhasil upload berkas final revisi');
    }

    public function showDiscussion($type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $student = User::where('id', auth()->user()->id)->firstOrFail();
        $submission = Submission::where('student_id', $student->id)->where('type_id', $type->id)->where('id', $id)->firstOrFail();

        if ($submission->is_ready_to_be_processed === false) {
            return redirect()->back()->with('failed', 'Dapat diakses setelah anda melakukan aksi ajukan pengajuan');
        }

        $discussions = SubmissionConsultation::where('submission_id', $submission->id)->orderBy('created_at', 'desc')->get();

        return view('panel.mahasiswa.pengajuan.show.discussions', compact('type', 'submission', 'discussions'));
    }

    public function addDiscussion(Request $request, $type_slug, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $student = User::where('id', auth()->user()->id)->firstOrFail();

        $request->validate([
            'komentar' => 'required',
        ]);

        $submission = Submission::where('student_id', $student->id)->where('type_id', $type->id)->where('id', $id)->firstOrFail();

        if ($submission->is_ready_to_be_processed === false) {
            return redirect()->back()->with('failed', 'Dapat diakses setelah anda melakukan aksi ajukan pengajuan');
        }

        if (!($submission->status == Submission::STATUS_PROCESSED || $submission->status == Submission::STATUS_PENDING)) {
            return redirect()->back()->with('failed', 'Pengajuan sudah dikonfirmasi, tidak bisa menambah komentar');
        }

        SubmissionConsultation::create([
            'submission_id' => $submission->id,
            'user_id' => $student->id,
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

        $studentAddNewDiscussionNotificationQueue = new StudentAddNewDiscussionNotificationQueue($submission, now()->format('Y-m-d H:i:s'));
        dispatch($studentAddNewDiscussionNotificationQueue)->delay(now()->addSeconds(5))->onQueue('whatsapp_notification_queue');

        return redirect()->back()->with('success', 'Komentar berhasil ditambahkan');
    }
}
