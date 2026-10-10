<?php

namespace App\Http\Controllers;

use App\Helpers\FunctionHelper;
use App\Helpers\SpotaHelper;
use App\Models\ActivitySubmissionLog;
use App\Models\ActivityUserLog;
use App\Models\Submission;
use App\Models\SubmissionPresence;
use App\Models\SubmissionRequiredValue;
use App\Models\User;
use App\Models\Verificator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
// use \App\Helpers\TemplateProcessor;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\TemplateProcessor;

class IndexController extends Controller
{
    private $functionHelper;

    public function __construct()
    {
        $this->functionHelper = new FunctionHelper();
    }

    public function index(Request $request)
    {
        $show_my_submissions = $request->tampilkan_data_milik_sendiri;

        $user = User::where('id', Auth::user()->id)->first();

        $q_submission_schedules = Submission::where('is_ready_to_be_processed', 1)->where('schedule', '!=', null)->whereHas('format', function ($q) {
            $q->where('is_need_trial_schedule', 1);
        });

        $statistic_year = $request->tahun_statistik ? $request->tahun_statistik : date('Y');

        if ($user->role === User::ROLE_ADMIN) {
            // get this year of month january count of submission models
            $submissions = [];
            $submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-01%')->count();
            $submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-02%')->count();
            $submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-03%')->count();
            $submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-04%')->count();
            $submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-05%')->count();
            $submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-06%')->count();
            $submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-07%')->count();
            $submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-08%')->count();
            $submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-09%')->count();
            $submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-10%')->count();
            $submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-11%')->count();
            $submissions[] = Submission::where('schedule', 'like', '%'.$statistic_year.'-12%')->count();

            $submission_schedules = $q_submission_schedules->get();

            return view('panel.admin.dashboard', compact('user', 'submissions', 'submission_schedules'));
        } elseif ($user->role === User::ROLE_DOSEN) {
            $total_pengajuan_dibimbing = Submission::where('is_ready_to_be_processed', 1)->whereHas('verificators', function ($q) {
                $q->where('user_id', Auth::user()->id);
                $q->where('type', 'Pembimbing');
            })->count();

            $total_pengajuan_diuji = Submission::where('is_ready_to_be_processed', 1)->whereHas('verificators', function ($q) {
                $q->where('user_id', Auth::user()->id);
                $q->where('type', 'Penguji');
            })->count();

            // get this year of month january count of submission models
            $submissions = [];

            if ($show_my_submissions === 'true') {
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-01%')->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-02%')->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-03%')->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-04%')->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-05%')->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-06%')->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-07%')->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-08%')->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-09%')->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-10%')->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-11%')->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-12%')->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                })->count();

                $q_submission_schedules->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                });
            } else {
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-01%')->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-02%')->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-03%')->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-04%')->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-05%')->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-06%')->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-07%')->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-08%')->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-09%')->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-10%')->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-11%')->count();
                $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-12%')->count();
            }

            $submission_schedules = $q_submission_schedules->get();

            return view('panel.dosen.dashboard', compact('user', 'submissions', 'total_pengajuan_dibimbing', 'total_pengajuan_diuji', 'submission_schedules'));
        } elseif ($user->role === User::ROLE_BAGIAN_ADMINISTRASI) {
            $submissions = [];
            $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-01%')->count();
            $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-02%')->count();
            $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-03%')->count();
            $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-04%')->count();
            $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-05%')->count();
            $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-06%')->count();
            $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-07%')->count();
            $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-08%')->count();
            $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-09%')->count();
            $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-10%')->count();
            $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-11%')->count();
            $submissions[] = Submission::where('is_ready_to_be_processed', 1)->where('schedule', 'like', '%'.$statistic_year.'-12%')->count();

            if ($show_my_submissions === 'true') {
                $q_submission_schedules->whereHas('verificators', function ($q) {
                    $q->where('user_id', Auth::user()->id);
                });
            }

            $submission_schedules = $q_submission_schedules->get();

            return view('panel.bagian-administrasi.dashboard', compact('user', 'submissions', 'submission_schedules'));
        } elseif ($user->role === User::ROLE_MAHASISWA) {
            $submissions = Submission::where('student_id', $user->id)->get();

            if ($show_my_submissions === 'true') {
                $q_submission_schedules->where('student_id', $user->id);
            }

            $submission_schedules = $q_submission_schedules->get();

            return view('panel.mahasiswa.dashboard', compact('user', 'submissions', 'submission_schedules'));
        } else {
            Auth::logout();

            return redirect()->route('login')->with('failed', 'Anda tidak memiliki hak akses');
        }
    }

    public function profile()
    {
        $user = User::where('id', Auth::user()->id)->first();

        return view('panel.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $this->validate($request, [
            'fullname' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'regex:/(62)[0-9]{8,13}/',
                'integer',
            ],
        ], [
            'phone_number.required' => 'Nomor Handphone Whatsapp harus diisi',
            'phone_number.regex' => 'Nomor Handphone Whatsapp harus diisi dengan format 62 dengan minimal 9 dan maksimal 13 angka',
            'phone_number.integer' => 'Nomor Handphone Whatsapp harus diisi dengan angka',
        ]);

        $user = User::where('id', Auth::user()->id)->first();
        $user->name = $request->fullname;
        $user->phone_number = $request->phone_number;
        $user->save();

        return redirect()->back()->with('success', 'Profile berhasil diperbarui');
    }

    public function updatePassword(Request $request)
    {
        $user = User::where('id', Auth::user()->id)->first();

        $this->validate($request, [
            'old_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!(Hash::check($request->get('old_password'), $user->password))) {
            return redirect()->back()->with('failed', 'Password lama kamu tidak benar, silahkan coba lagi.');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->back()->with('success', 'Password berhasil diperbarui');
    }

    public function updateAvatar(Request $request)
    {
        $user = User::where('id', Auth::user()->id)->first();

        $this->validate($request, [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $image_name = $user->profile_image;
        if ($request->has('profile_image')) {
            if ($user->profile_image != null) {
                Storage::disk('public')->delete('profiles/'.$user->profile_image);
            }
            $image_name = Str::slug($user->name).'.'.$request->profile_image->getClientOriginalExtension();
            $request->profile_image->storeAs('profiles', $image_name, 'public');
        }

        $user->profile_image = $image_name;
        $user->save();

        return redirect()->back()->with('success', 'Foto Profile berhasil diperbarui');
    }

    public function updateSignature(Request $request)
    {
        $user = User::where('id', Auth::user()->id)->first();

        if ($user->role == User::ROLE_MAHASISWA && $user->signature_status == User::SIGNATURE_APPROVED) {
            return redirect()->back()->with('failed', 'Tidak dapat mengubah tanda tangan karena sudah diverifikasi, jika terdapat kesalahan silahkan hubungi admin');
        }

        $image_name = $user->signature_image;
        $signature_status = User::SIGNATURE_PENDING;
        if ($request->has('tandatangan_image')) {
            $this->validate($request, [
                'tandatangan_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($user->signature_image != null) {
                Storage::disk('public')->delete('signatures/'.$user->signature_image);
            }
            $image_name = Str::slug($user->name).'.'.$request->tandatangan_image->getClientOriginalExtension();
            $request->tandatangan_image->storeAs('signatures', $image_name, 'public');
        }

        if ($user->role !== User::ROLE_MAHASISWA) {
            $signature_status = User::SIGNATURE_APPROVED;
        }

        $user->signature_status = $signature_status;
        $user->signature_image = $image_name;
        $user->save();

        return redirect()->back()->with('success', 'Tandatangan berhasil diperbarui');
    }

    // untuk seminar proposal
    public function submissionDownload($id)
    {
        Carbon::setLocale('id');

        $user = User::where('id', Auth::user()->id)->first();
        $submission = Submission::where('id', $id)->firstOrFail();
        $type = $submission->type;
        $student = $submission->student;
        $format_required_values = $submission->format->required_values;

        if ($user->role == User::ROLE_MAHASISWA) {
            return redirect()->back()->with('failed', 'Anda tidak memiliki hak akses');
        }

        $pembimbings = Verificator::where('type', 'Pembimbing')->where('submission_id', $submission->id)->get();
        $pengujis = Verificator::where('type', 'Penguji')->where('submission_id', $submission->id)->get();
        $index = 1;

        $submission_presences = [];

        $total_rata_rata_nilai_x_bobot = 0;

        if (
            ($user->role === User::ROLE_MAHASISWA && $submission->student_id !== $user->id) ||
            ($user->role === User::ROLE_DOSEN && $submission->verificators()->where('user_id', $user->id)->first() === null)
        ) {
            return redirect()->back()->with('failed', 'Anda tidak memiliki hak akses');
        }

        $templateDocument = new TemplateProcessor('storage/format_documents/'.$submission->format->document_format);

        // check pada template dokumen ada variabel ke
        if (in_array('ke', $templateDocument->getVariables())) {
            $ke = 1;
            if (Submission::where('type_id', $type->id)->where('student_id', $student->id)->count() > 1) {
                $all_submissions = Submission::where('type_id', $type->id)->where('student_id', $student->id)->get();
                foreach ($all_submissions as $all_submission) {
                    if ($all_submission->id == $submission->id) {
                        break;
                    }

                    ++$ke;
                }
            }

            $templateDocument->setValue('ke', $ke);
        }

        // check pada template dokumen ada variabel nama_mahasiswa
        if (in_array('nama_mahasiswa', $templateDocument->getVariables())) {
            $templateDocument->setValue('nama_mahasiswa', $submission->student->name); // meletakkan nama mahasiswa di dokumen dengan key nama_mahasiswa
        }

        // check pada template dokumen ada variabel nim_mahasiswa
        if (in_array('nim_mahasiswa', $templateDocument->getVariables())) {
            $templateDocument->setValue('nim_mahasiswa', $submission->student->unique_id); // meletakkan nama mahasiswa di dokumen dengan key nim_mahasiswa
        }

        // check pada template dokumen ada variabel judul_proposal
        if (in_array('judul', $templateDocument->getVariables())) {
            $templateDocument->setValue('judul', $submission->title); // meletakkan judul di dokumen dengan key judul
        }

        // check pada template dokumen ada variabel jumlah_sks
        if (in_array('jumlah_sks', $templateDocument->getVariables())) {
            $templateDocument->setValue('jumlah_sks', $submission->amount_sks_now); // meletakkan jumlah sks di dokumen dengan key jumlah_sks
        }

        // check pada template dokumen ada variabel ipk
        if (in_array('ipk', $templateDocument->getVariables())) {
            $templateDocument->setValue('ipk', $submission->ipk_now); // meletakkan ipk di dokumen dengan key ipk
        }

        // check pada template dokumen ada variabel tanggal
        if (in_array('tanggal', $templateDocument->getVariables())) {
            $templateDocument->setValue('tanggal', Carbon::parse($submission->updated_at)->locale('id')->format('d F Y')); // meletakkan tanggal pengajuan di dokumen dengan key tanggal
        }

        // check pada template dokumen ada variabel hari
        if (in_array('hari', $templateDocument->getVariables())) {
            $templateDocument->setValue('hari', $this->functionHelper->translateDayToIndonesian(Carbon::parse($submission->updated_at)->format('w'))); // meletakkan hari pengajuan di dokumen dengan key hari
        }

        if (in_array('bulan', $templateDocument->getVariables())) {
            $templateDocument->setValue('bulan', $this->functionHelper->translateMonthToIndonesian(Carbon::parse($submission->updated_at)->format('n'))); // meletakkan bulan pengajuan di dokumen dengan key bulan
        }

        if (in_array('tahun', $templateDocument->getVariables())) {
            $templateDocument->setValue('tahun', Carbon::parse($submission->updated_at)->format('Y')); // meletakkan tahun pengajuan di dokumen dengan key tahun
        }

        $tanggal_seminar_tanpa_hari = '';
        $tanggal_seminar_dengan_hari = '';

        $hari_seminar = $this->functionHelper->translateDayToIndonesian($submission->schedule ? Carbon::parse($submission->schedule)->format('w') : '');
        $tanggal_seminar = $submission->schedule ? Carbon::parse($submission->schedule)->format('d') : '';
        $bulan_seminar_angka = $submission->schedule ? Carbon::parse($submission->schedule)->format('m') : '';
        $bulan_seminar = $this->functionHelper->translateMonthToIndonesian($submission->schedule ? Carbon::parse($submission->schedule)->format('n') : '');
        $tahun_seminar = $submission->schedule ? Carbon::parse($submission->schedule)->format('Y') : '';

        $tanggal_seminar_dengan_hari = $hari_seminar.' '.$bulan_seminar.' '.$tahun_seminar;
        $tanggal_seminar_tanpa_hari = $tanggal_seminar.' '.$bulan_seminar.' '.$tahun_seminar;
        $tanggalSeminarYmd = $tahun_seminar.'-'.$bulan_seminar_angka.'-'.$tanggal_seminar;

        if (in_array('hari_seminar', $templateDocument->getVariables())) {
            $templateDocument->setValue('hari_seminar', $hari_seminar); // meletakkan hari_seminar pengajuan di dokumen dengan key hari_seminar
        }

        if (in_array('bulan_seminar', $templateDocument->getVariables())) {
            $templateDocument->setValue('bulan_seminar', $bulan_seminar); // meletakkan bulan_seminar pengajuan di dokumen dengan key bulan_seminar
        }

        if (in_array('tahun_seminar', $templateDocument->getVariables())) {
            $templateDocument->setValue('tahun_seminar', $tahun_seminar); // meletakkan tahun_seminar pengajuan di dokumen dengan key tahun_seminar
        }

        if (in_array('tanggal_seminar_dengan_hari', $templateDocument->getVariables())) {
            $templateDocument->setValue('tanggal_seminar_dengan_hari', $tanggal_seminar_dengan_hari); // meletakkan tanggal seminar di dokumen dengan key tanggal_seminar_dengan_hari
        }

        if (in_array('tanggal_seminar_tanpa_hari', $templateDocument->getVariables())) {
            $templateDocument->setValue('tanggal_seminar_tanpa_hari', $tanggal_seminar_tanpa_hari); // meletakkan tanggal seminar di dokumen dengan key tanggal_seminar_tanpa_hari
        }

        if (in_array('waktu_seminar', $templateDocument->getVariables())) {
            $templateDocument->setValue('waktu_seminar', $submission->schedule ? Carbon::parse($submission->schedule)->format('H:i') : ''); // meletakkan waktu seminar di dokumen dengan key waktu_seminar
        }

        // menampilkan semua data pembimbings
        foreach ($pembimbings as $pembimbing) {
            if (in_array('nama_pembimbing#'.$index, $templateDocument->getVariables())) {
                $templateDocument->setValue('nama_pembimbing#'.$index, $pembimbing->user->name); // meletakkan nama verificator di dokumen dengan key nama_pembimbing
            }

            if (in_array('nip_pembimbing#'.$index, $templateDocument->getVariables())) {
                $templateDocument->setValue('nip_pembimbing#'.$index, $pembimbing->user->unique_id);
            }

            if (in_array('ttd_pembimbing#'.$index, $templateDocument->getVariables())) {
                $templateDocument->setImageValue('ttd_pembimbing#'.$index, $pembimbing->user->signature_image ? asset('/storage/signatures/'.$pembimbing->user->signature_image) : asset('/img/image_not_found.png'));
            }

            if (in_array('final_revisi_ttd_pembimbing#'.$index, $templateDocument->getVariables())) {
                if ($pembimbing->final_status == 'approved') {
                    $templateDocument->setImageValue('final_revisi_ttd_pembimbing#'.$index, $pembimbing->user->signature_image ? asset('/storage/signatures/'.$pembimbing->user->signature_image) : asset('/img/image_not_found.png'));
                } else {
                    $templateDocument->setValue('final_revisi_ttd_pembimbing#'.$index, '');
                }
            }

            if (in_array('catatan_pembimbing#'.$index, $templateDocument->getVariables())) {
                $pembimbing_note = htmlspecialchars($pembimbing->note);
                $pembimbing_note = str_replace("\n", '</w:t><w:br/><w:t>', $pembimbing_note);
                $templateDocument->setValue('catatan_pembimbing#'.$index, $pembimbing_note);
            }

            // total nilai yang diberikan pembimbing
            $submission_required_values = SubmissionRequiredValue::where('verificator_id', $pembimbing->id)->where('submission_id', $submission->id)->get();
            if (in_array('total_nilai#pembimbing#'.$index, $templateDocument->getVariables())) {
                $templateDocument->setValue('total_nilai#pembimbing#'.$index, $submission_required_values->sum('value'));
            }

            if (in_array('total_nilai_x_bobot#pembimbing#'.$index, $templateDocument->getVariables())) {
                $total_nilai_x_bobot = 0;

                foreach ($submission_required_values as $submission_required_value) {
                    $total_nilai_x_bobot += $submission_required_value->value * $submission_required_value->required_value->weight / 100;
                }

                $templateDocument->setValue('total_nilai_x_bobot#pembimbing#'.$index, $total_nilai_x_bobot);

                $total_rata_rata_nilai_x_bobot += $total_nilai_x_bobot;
            }

            $submission_presences[] = [
                'name' => $pembimbing->user->name,
                'unique_id' => $pembimbing->user->unique_id,
                'keterangan' => 'Pembimbing '.$index,
                'signature_image' => $pembimbing->user->signature_image ? asset('/storage/signatures/'.$pembimbing->user->signature_image) : asset('/img/image_not_found.png'),
            ];

            ++$index;
        }

        $index = 1;
        // menampilkan semua data pengujis
        foreach ($pengujis as $penguji) {
            if (in_array('nama_penguji#'.$index, $templateDocument->getVariables())) {
                $templateDocument->setValue('nama_penguji#'.$index, $penguji->user->name); // meletakkan nama verificator di dokumen dengan key nama_penguji
            }

            if (in_array('nip_penguji#'.$index, $templateDocument->getVariables())) {
                $templateDocument->setValue('nip_penguji#'.$index, $penguji->user->unique_id);
            }

            if (in_array('ttd_penguji#'.$index, $templateDocument->getVariables())) {
                $templateDocument->setImageValue('ttd_penguji#'.$index, $penguji->user->signature_image ? asset('/storage/signatures/'.$penguji->user->signature_image) : asset('/img/image_not_found.png'));
            }

            if (in_array('ttd_penguji#'.$index, $templateDocument->getVariables())) {
                $templateDocument->setImageValue('ttd_penguji#'.$index, $penguji->user->signature_image ? asset('/storage/signatures/'.$penguji->user->signature_image) : asset('/img/image_not_found.png'));
            }

            if (in_array('final_revisi_ttd_penguji#'.$index, $templateDocument->getVariables())) {
                if ($penguji->final_status == 'approved') {
                    $templateDocument->setImageValue('final_revisi_ttd_penguji#'.$index, $penguji->user->signature_image ? asset('/storage/signatures/'.$penguji->user->signature_image) : asset('/img/image_not_found.png'));
                } else {
                    $templateDocument->setValue('final_revisi_ttd_penguji#'.$index, '');
                }
            }

            if (in_array('catatan_penguji#'.$index, $templateDocument->getVariables())) {
                $penguji_note = htmlspecialchars($penguji->note);
                $penguji_note = str_replace("\n", '</w:t><w:br/><w:t>', $penguji_note);
                $templateDocument->setValue('catatan_penguji#'.$index, $penguji_note);
            }

            // total nilai yang diberikan penguji
            $submission_required_values = SubmissionRequiredValue::where('verificator_id', $penguji->id)->where('submission_id', $submission->id)->get();
            if (in_array('total_nilai#penguji#'.$index, $templateDocument->getVariables())) {
                $templateDocument->setValue('total_nilai#penguji#'.$index, $submission_required_values->sum('value'));
            }

            if (in_array('total_nilai_x_bobot#penguji#'.$index, $templateDocument->getVariables())) {
                $total_nilai_x_bobot = 0;

                foreach ($submission_required_values as $submission_required_value) {
                    $total_nilai_x_bobot += $submission_required_value->value * $submission_required_value->required_value->weight / 100;
                }

                $templateDocument->setValue('total_nilai_x_bobot#penguji#'.$index, $total_nilai_x_bobot);

                $total_rata_rata_nilai_x_bobot += $total_nilai_x_bobot;
            }

            $submission_presences[] = [
                'name' => $penguji->user->name,
                'unique_id' => $penguji->user->unique_id,
                'keterangan' => 'Penguji '.$index,
                'signature_image' => $penguji->user->signature_image ? asset('/storage/signatures/'.$penguji->user->signature_image) : asset('/img/image_not_found.png'),
            ];

            ++$index;
        }

        // menampilkan semua nama aspek penilaian dari format penilaian yang ada
        foreach ($format_required_values as $format_required_value) {
            // cek apakah variabel key#nama dari format penialain ada di template dokumen
            if (in_array($format_required_value->key.'#nama', $templateDocument->getVariables())) {
                $templateDocument->setValue($format_required_value->key.'#nama', $format_required_value->name);
            }

            // cek apakah variabel key#bobot dari format penialain ada di template dokumen
            if (in_array($format_required_value->key.'#bobot', $templateDocument->getVariables())) {
                $templateDocument->setValue($format_required_value->key.'#bobot', $format_required_value->weight.'%');
            }

            $index = 1;
            // looping semua pembimbing untuk mendapatkan nilai berdasarkan key format penilaian
            foreach ($pembimbings as $pembimbing) {
                // cek apakah variabel key#pembimbing#1#nilai dari format penialain ada di template dokumen
                $submission_required_value = SubmissionRequiredValue::where('required_value_id', $format_required_value->id)->where('verificator_id', $pembimbing->id)->where('submission_id', $submission->id)->first();

                if (in_array($format_required_value->key.'#pembimbing#'.$index.'#nilai', $templateDocument->getVariables())) {
                    $templateDocument->setValue($format_required_value->key.'#pembimbing#'.$index.'#nilai', $submission_required_value ? $submission_required_value->value : 'Belum dinilai');
                }

                if (in_array($format_required_value->key.'#pembimbing#'.$index.'#nilai_x_bobot', $templateDocument->getVariables())) {
                    $templateDocument->setValue($format_required_value->key.'#pembimbing#'.$index.'#nilai_x_bobot', $submission_required_value ? $submission_required_value->value * $format_required_value->weight / 100 : 'Belum dinilai');
                }

                ++$index;
            }

            $index = 1;
            foreach ($pengujis as $penguji) {
                // cek apakah variabel key#penguji#1#nilai dari format penialain ada di template dokumen
                $submission_required_value = SubmissionRequiredValue::where('required_value_id', $format_required_value->id)->where('verificator_id', $penguji->id)->where('submission_id', $submission->id)->first();

                if (in_array($format_required_value->key.'#penguji#'.$index.'#nilai', $templateDocument->getVariables())) {
                    $templateDocument->setValue($format_required_value->key.'#penguji#'.$index.'#nilai', $submission_required_value ? $submission_required_value->value : 'Belum dinilai');
                }

                if (in_array($format_required_value->key.'#penguji#'.$index.'#nilai_x_bobot', $templateDocument->getVariables())) {
                    $templateDocument->setValue($format_required_value->key.'#penguji#'.$index.'#nilai_x_bobot', $submission_required_value ? $submission_required_value->value * $format_required_value->weight / 100 : 'Belum dinilai');
                }

                ++$index;
            }
        }

        // kehadiran mahasiswa yang presentasi
        $submission_presences[] = [
            'name' => $submission->student->name,
            'unique_id' => $submission->student->unique_id,
            'keterangan' => 'Mahasiswa',
            'signature_image' => '',
        ];

        // kehadiran
        if (in_array('no_kehadiran', $templateDocument->getVariables())) {
            $submission_presences_from_database = SubmissionPresence::where('submission_id', $submission->id)->get();

            foreach ($submission_presences_from_database as $submission_presence) {
                $submission_presences[] = [
                    'name' => $submission_presence->name,
                    'unique_id' => $submission_presence->unique_id,
                    'keterangan' => $submission_presence->keterangan,
                    'signature_image' => '',
                ];
            }

            $templateDocument->cloneRow('no_kehadiran', count($submission_presences));

            foreach ($submission_presences as $i => $submission_presence) {
                $templateDocument->setValue('no_kehadiran#'.($i + 1), $i + 1);
                $templateDocument->setValue('nama_peserta_kehadiran#'.($i + 1), $submission_presence['name']);
                $templateDocument->setValue('unique_id_peserta_kehadiran#'.($i + 1), $submission_presence['unique_id']);
                $templateDocument->setValue('keterangan_peserta_kehadiran#'.($i + 1), $submission_presence['keterangan']);
                if ($submission_presence['signature_image']) {
                    $templateDocument->setImageValue('ttd_peserta_kehadiran#'.($i + 1), $submission_presence['signature_image']);
                } else {
                    $templateDocument->setValue('ttd_peserta_kehadiran#'.($i + 1), '');
                }
            }
        }

        $grade_value = $submission->score ?: 'Belum dinilai';
        $grade_word = $submission->grade ?: 'Belum ada';

        $recomendation_repeated_schedule = $submission->recomendation_repeated_schedule;

        // nilai akhir
        if (in_array('nilai_akhir', $templateDocument->getVariables())) {
            $templateDocument->setValue('nilai_akhir', $grade_value);
        }

        if (in_array('grade_akhir', $templateDocument->getVariables())) {
            $templateDocument->setValue('grade_akhir', $grade_word);
        }

        if (in_array('hasil_keputusan', $templateDocument->getVariables())) {
            if ($grade_value == 'Belum dinilai') {
                $templateDocument->setValue('hasil_keputusan', 'Belum dinilai');
            } else {
                if ($grade_value >= 60) {
                    $templateDocument->setValue('hasil_keputusan', 'LULUS');
                } else {
                    $templateDocument->setValue('hasil_keputusan', 'TIDAK LULUS');
                }
            }
        }

        // status sidang
        if ($submission->result_status == Submission::RESULT_APPROVED) {
            if (in_array('pilihan_hasil_sidang_diterima_dengan_nilai', $templateDocument->getVariables())) {
                $text = 'Diterima,  dengan nilai :   '.$grade_word.'('.$grade_value.')';

                $templateDocument->setValue('pilihan_hasil_sidang_diterima_dengan_nilai', $text);
            }

            if (in_array('pilihan_hasil_sidang_diterima_tanpa_nilai', $templateDocument->getVariables())) {
                $templateDocument->setValue('pilihan_hasil_sidang_diterima_tanpa_nilai', 'Diterima');
            }

            // strikethrough
            if (in_array('pilihan_hasil_sidang_diterima_dengan_perbaikan', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diterima, dengan perbaikan', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diterima_dengan_perbaikan', $text);
            }

            if (in_array('pilihan_hasil_sidang_diulang', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diulang', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diulang', $text);
            }

            if (in_array('pilihan_hasil_sidang_diulang_dengan_rekomendasi_tanggal', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diulang dengan rekomendasi waktu', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diulang_dengan_rekomendasi_tanggal', $text);
            }

            if (in_array('pilihan_hasil_sidang_diulang_dengan_nilai_dan_rekomendasi_tanggal', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diulang,  dengan rekomendasi waktu seminar '.$submission->type->name.' Ulang : '.$recomendation_repeated_schedule ?: '-', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diulang_dengan_nilai_dan_rekomendasi_tanggal', $text);
            }

            if (in_array('pilihan_hasil_sidang_ditolak', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Ditolak (50-59)', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_ditolak', $text);
            }

            if (in_array('pilihan_hasil_sidang_ditolak_dengan_nilai', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Ditolak (0-49)', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_ditolak_dengan_nilai', $text);
            }
        } elseif ($submission->result_status == Submission::RESULT_APPROVED_WITH_FIXED) {
            if (in_array('pilihan_hasil_sidang_diterima_dengan_perbaikan', $templateDocument->getVariables())) {
                $templateDocument->setValue('pilihan_hasil_sidang_diterima_dengan_perbaikan', 'Diterima dengan perbaikan');
            }

            if ($submission->type->name == 'Proposal') {
                if (in_array('pilihan_hasil_sidang_diterima_dengan_nilai', $templateDocument->getVariables())) {
                    $text = 'Diterima,  dengan nilai :   '.$grade_word.'('.$grade_value.')';

                    $templateDocument->setValue('pilihan_hasil_sidang_diterima_dengan_nilai', $text);
                }

                if (in_array('pilihan_hasil_sidang_diterima_tanpa_nilai', $templateDocument->getVariables())) {
                    $templateDocument->setValue('pilihan_hasil_sidang_diterima_tanpa_nilai', 'Diterima');
                }
            } else {
                // strikethrough
                if (in_array('pilihan_hasil_sidang_diterima_dengan_nilai', $templateDocument->getVariables())) {
                    $text = new TextRun();
                    $text->addText('Diterima,  dengan nilai :   A(80-100) / B+(75-79) /B(70-74) /C+(65-69) / C(60-64)', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                    $templateDocument->setComplexValue('pilihan_hasil_sidang_diterima_dengan_nilai', $text);
                }

                if (in_array('pilihan_hasil_sidang_diterima', $templateDocument->getVariables())) {
                    $text = new TextRun();
                    $text->addText('Diterima', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                    $templateDocument->setComplexValue('pilihan_hasil_sidang_diterima', $text);
                }
            }

            if (in_array('pilihan_hasil_sidang_diulang', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diulang', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diulang', $text);
            }

            if (in_array('pilihan_hasil_sidang_diulang_dengan_rekomendasi_tanggal', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diulang dengan rekomendasi waktu', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diulang_dengan_rekomendasi_tanggal', $text);
            }

            if (in_array('pilihan_hasil_sidang_diulang_dengan_nilai_dan_rekomendasi_tanggal', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diulang (50-59),  dengan rekomendasi waktu seminar '.$submission->type->name.' Ulang : '.$recomendation_repeated_schedule ?: '-', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diulang_dengan_nilai_dan_rekomendasi_tanggal', $text);
            }

            if (in_array('pilihan_hasil_sidang_ditolak', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Ditolak', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_ditolak', $text);
            }

            if (in_array('pilihan_hasil_sidang_ditolak_dengan_nilai', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Ditolak (0-49)', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_ditolak_dengan_nilai', $text);
            }
        } elseif ($submission->result_status == Submission::RESULT_REPEATED) {
            if (in_array('pilihan_hasil_sidang_diulang_dengan_rekomendasi_tanggal', $templateDocument->getVariables())) {
                $templateDocument->setValue('pilihan_hasil_sidang_diulang_dengan_rekomendasi_tanggal', 'Diulang dengan rekomendasi waktu: '.$recomendation_repeated_schedule ?: '-');
            }

            if (in_array('pilihan_hasil_sidang_diulang', $templateDocument->getVariables())) {
                $templateDocument->setValue('pilihan_hasil_sidang_diulang', 'Diulang');
            }

            if (in_array('pilihan_hasil_sidang_diulang_dengan_nilai_dan_rekomendasi_tanggal', $templateDocument->getVariables())) {
                $text = 'Diulang (50-59),  dengan rekomendasi waktu seminar '.$submission->type->name.' Ulang : '.$recomendation_repeated_schedule ?: '-';
                $templateDocument->setValue('pilihan_hasil_sidang_diulang_dengan_nilai_dan_rekomendasi_tanggal', $text);
            }

            // strikethrough
            if (in_array('pilihan_hasil_sidang_diterima_dengan_nilai', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diterima,  dengan nilai :   A(80-100) / B+(75-79) /B(70-74) /C+(65-69) / C(60-64)', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diterima_dengan_nilai', $text);
            }

            if (in_array('pilihan_hasil_sidang_diterima', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diterima', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diterima', $text);
            }

            if (in_array('pilihan_hasil_sidang_diterima_dengan_perbaikan', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diterima, dengan perbaikan', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diterima_dengan_perbaikan', $text);
            }

            if (in_array('pilihan_hasil_sidang_ditolak', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Ditolak', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_ditolak', $text);
            }

            if (in_array('pilihan_hasil_sidang_ditolak_dengan_nilai', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Ditolak (0-49)', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_ditolak_dengan_nilai', $text);
            }
        } elseif ($submission->result_status == Submission::RESULT_REJECTED) {
            if (in_array('pilihan_hasil_sidang_ditolak', $templateDocument->getVariables())) {
                $templateDocument->setValue('pilihan_hasil_sidang_ditolak', 'Ditolak');
            }

            if (in_array('pilihan_hasil_sidang_ditolak_dengan_nilai', $templateDocument->getVariables())) {
                $templateDocument->setValue('pilihan_hasil_sidang_ditolak_dengan_nilai', 'Ditolak (0-49)');
            }

            // strikethrough
            if (in_array('pilihan_hasil_sidang_diterima_dengan_nilai', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diterima,  dengan nilai :   A(80-100) / B+(75-79) /B(70-74) /C+(65-69) / C(60-64)', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diterima_dengan_nilai', $text);
            }

            if (in_array('pilihan_hasil_sidang_diterima', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diterima', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diterima', $text);
            }

            if (in_array('pilihan_hasil_sidang_diterima_dengan_perbaikan', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diterima, dengan perbaikan', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diterima_dengan_perbaikan', $text);
            }

            if (in_array('pilihan_hasil_sidang_diulang', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diulang', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diulang', $text);
            }

            if (in_array('pilihan_hasil_sidang_diulang_dengan_rekomendasi_tanggal', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diulang dengan rekomendasi waktu', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diulang_dengan_rekomendasi_tanggal', $text);
            }

            if (in_array('pilihan_hasil_sidang_diulang_dengan_nilai_dan_rekomendasi_tanggal', $templateDocument->getVariables())) {
                $text = new TextRun();
                $text->addText('Diulang (50-59),  dengan rekomendasi waktu seminar '.$submission->type->name.' Ulang : '.$recomendation_repeated_schedule ?: '-', ['strikethrough' => true, 'size' => 10, 'name' => 'Times New Roman']);
                $templateDocument->setComplexValue('pilihan_hasil_sidang_diulang_dengan_nilai_dan_rekomendasi_tanggal', $text);
            }
        } else {
            if (in_array('pilihan_hasil_sidang_diterima_dengan_nilai', $templateDocument->getVariables())) {
                $templateDocument->setValue('pilihan_hasil_sidang_diterima_dengan_nilai', 'Diterima,  dengan nilai :   A(80-100) / B+(75-79) /B(70-74) /C+(65-69) / C(60-64)');
            }

            if (in_array('pilihan_hasil_sidang_diterima', $templateDocument->getVariables())) {
                $templateDocument->setValue('pilihan_hasil_sidang_diterima', 'Diterima');
            }

            if (in_array('pilihan_hasil_sidang_diterima_dengan_perbaikan', $templateDocument->getVariables())) {
                $templateDocument->setValue('pilihan_hasil_sidang_diterima_dengan_perbaikan', 'Diterima dengan perbaikan');
            }

            if (in_array('pilihan_hasil_sidang_diulang', $templateDocument->getVariables())) {
                $templateDocument->setValue('pilihan_hasil_sidang_diulang', 'Diulang');
            }

            if (in_array('pilihan_hasil_sidang_diulang_dengan_rekomendasi_tanggal', $templateDocument->getVariables())) {
                $templateDocument->setValue('pilihan_hasil_sidang_diulang_dengan_rekomendasi_tanggal', 'Diulang dengan rekomendasi waktu');
            }

            if (in_array('pilihan_hasil_sidang_diulang_dengan_nilai_dan_rekomendasi_tanggal', $templateDocument->getVariables())) {
                $text = 'Diulang (50-59),  dengan rekomendasi waktu seminar '.$submission->type->name.' Ulang : ';
                $templateDocument->setValue('pilihan_hasil_sidang_diulang_dengan_nilai_dan_rekomendasi_tanggal', $text);
            }

            if (in_array('pilihan_hasil_sidang_ditolak', $templateDocument->getVariables())) {
                $templateDocument->setValue('pilihan_hasil_sidang_ditolak', 'Ditolak');
            }

            if (in_array('pilihan_hasil_sidang_ditolak_dengan_nilai', $templateDocument->getVariables())) {
                $templateDocument->setValue('pilihan_hasil_sidang_ditolak_dengan_nilai', 'Ditolak (0-49)');
            }
        }

        // perhitungan semua total
        if (in_array('total_bobot', $templateDocument->getVariables())) {
            $templateDocument->setValue('total_bobot', $format_required_values->sum('weight').'%');
        }

        $total_rata_rata_nilai_x_bobot = $total_rata_rata_nilai_x_bobot / (count($pembimbings) + count($pengujis));
        if (in_array('total_rata_rata_nilai_x_bobot', $templateDocument->getVariables())) {
            $templateDocument->setValue('total_rata_rata_nilai_x_bobot', $total_rata_rata_nilai_x_bobot);
        }

        $namaDekan = '';
        $nipDekan = '';
        $namaWD1 = '';
        $nipWD1 = '';

        if (strtotime($tanggalSeminarYmd) < strtotime('2022-09-22')) {
            $namaDekan = 'R.M. RUSTAMAJI';
            $nipDekan = '196801161994031003';
            $namaWD1 = 'Dr.-Ing. Ir. Slamet Widodo, M.T.,IPM';
            $nipWD1 = '19671223 199203 1002';
        } else {
            $namaDekan = 'SLAMET WIDODO';
            $nipDekan = '196712231992031002';
            $namaWD1 = 'Dr. Ir. Yopa Eka Prawatya, S.T., M.Eng., IPM.';
            $nipWD1 = '19850408 201012 1009';
        }

        if (in_array('nama_dekanX', $templateDocument->getVariables())) {
            $templateDocument->setValue('nama_dekanX', $namaDekan);
        }

        if (in_array('nip_dekanX', $templateDocument->getVariables())) {
            $templateDocument->setValue('nip_dekanX', $nipDekan);
        }

        if (in_array('nama_WD1X', $templateDocument->getVariables())) {
            $templateDocument->setValue('nama_WD1X', $namaWD1);
        }

        if (in_array('nip_WD1X', $templateDocument->getVariables())) {
            $templateDocument->setValue('nip_WD1X', $nipWD1);
        }

        $fileName = 'Hasil Penilaian - '.$submission->type->name.' - '.$submission->student->unique_id.' - '.$submission->student->name;
        $templateDocument->saveAs($fileName.'.docx');

        return response()->download($fileName.'.docx')->deleteFileAfterSend(true);
    }

    public function userLog($id)
    {
        $user = User::where('id', $id)->firstOrFail();
        $logs = ActivityUserLog::where('user_id', $id)->orderBy('id', 'desc')->get();

        return view('panel.log.user', compact('logs', 'user'));
    }

    public function submissionLog($id)
    {
        $submission = Submission::where('id', $id)->firstOrFail();
        $userAuth = User::where('id', auth()->user()->id)->first();

        if ($userAuth->role == User::ROLE_MAHASISWA && $userAuth->id != $submission->student_id) {
            return redirect()->back()->with('failed', 'Anda tidak memiliki akses ke halaman ini');
        }

        $logs = ActivitySubmissionLog::where('submission_id', $id)->orderBy('id', 'desc')->get();

        return view('panel.log.submission', compact('logs', 'submission'));
    }

    public function pengajuanSpotaTanpaPenguji()
    {
        $spotaHelper = new SpotaHelper();
        $submissions = $spotaHelper->submissionWithoutPenguji();

        return view('pengajuan-tanpa-penguji', compact('submissions'));
    }

    public function syncSpotaUserData()
    {
        $spotaHelper = new SpotaHelper();
        $spotaHelper->syncStudentData();
        $spotaHelper->syncLecturerData();

        return redirect()->back()->with('success', 'Data berhasil disinkronisasi');
    }
}
