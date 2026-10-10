<?php

namespace App\Http\Controllers;

use App\Helpers\FunctionHelper;
use App\Helpers\WhatsappHelper;
use App\Models\Submission;
use App\Models\SubmissionPresence;
use App\Models\SubmissionRequiredValue;
use App\Models\Type;
use App\Models\Verificator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\TemplateProcessor;

class SubmissionPresenceController extends Controller
{
    private $functionHelper;
    private $whatsappHelper;

    public function __construct()
    {
        $this->functionHelper = new FunctionHelper();
        $this->whatsappHelper = new WhatsappHelper('', '6285171655161');
    }

    public function index($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();

        if ($submission->presence_public_url_status != 'active') {
            return 'URL untuk mengisi presensi tidak aktif';
        }

        if ($submission->presence_public_url_open_datetime > now() || $submission->presence_public_url_open_datetime == null) {
            $text_return = 'URL untuk mengisi presensi belum aktif';
            if ($submission->presence_public_url_open_datetime != null) {
                $text_return .= ', akan dibuka pada: '.$submission->presence_public_url_open_datetime;
            }

            return $text_return;
        }

        if ($submission->presence_public_url_close_datetime < now() || $submission->presence_public_url_close_datetime == null) {
            $text_return = 'URL untuk mengisi presensi sudah tidak aktif';
            if ($submission->presence_public_url_close_datetime != null) {
                $text_return .= ', telah ditutup pada: '.$submission->presence_public_url_close_datetime;
            }

            return $text_return;
        }

        return view('submission-presence', compact(
            'submission',
            'type'
        ));
    }

    public function store(Request $request, $type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $submission = Submission::where('id', $submission_id)->where('type_id', $type->id)->firstOrFail();

        if ($submission->presence_public_url_status != 'active') {
            return 'URL untuk mengisi presensi tidak aktif';
        }

        if ($submission->presence_public_url_open_datetime > now() || $submission->presence_public_url_open_datetime == null) {
            $text_return = 'URL untuk mengisi presensi belum aktif';
            if ($submission->presence_public_url_open_datetime != null) {
                $text_return .= ', akan dibuka pada: '.$submission->presence_public_url_open_datetime;
            }

            return $text_return;
        }

        if ($submission->presence_public_url_close_datetime < now() || $submission->presence_public_url_close_datetime == null) {
            $text_return = 'URL untuk mengisi presensi sudah tidak aktif';
            if ($submission->presence_public_url_close_datetime != null) {
                $text_return .= ', telah ditutup pada: '.$submission->presence_public_url_close_datetime;
            }

            return $text_return;
        }

        $request->validate([
            'nama' => 'required',
            'nim_nip_nidk' => 'required',
            'keterangan' => 'required|in:mahasiswa,dosen,lainnya',
        ]);

        $unique_ids = [];
        $unique_ids[] = $submission->student->unique_id;

        $pengujis = Verificator::where('submission_id', $submission_id)->where('type', Verificator::TYPE_PENGUJI)->get();
        foreach ($pengujis as $penguji) {
            $unique_ids[] = $penguji->user->unique_id;
        }

        $pembimbings = Verificator::where('submission_id', $submission_id)->where('type', Verificator::TYPE_PEMBIMBING)->get();
        foreach ($pembimbings as $pembimbing) {
            $unique_ids[] = $pembimbing->user->unique_id;
        }

        $submission_presences = SubmissionPresence::where('submission_id', $submission_id)->get();

        // check if request nim_nip_nidk was in SubmissionPresence
        foreach ($unique_ids as $unique_id) {
            if ($request->nim_nip_nidk == $unique_id) {
                return redirect()->back()->with('failed', 'nim nip nidk sudah ada sebelumnya.');
            }
        }

        foreach ($submission_presences as $submission_presence) {
            if ($request->nim_nip_nidk == $submission_presence->unique_id) {
                return redirect()->back()->with('failed', 'nim nip nidk sudah ada sebelumnya.');
            }
        }

        SubmissionPresence::create([
            'submission_id' => $submission_id,
            'name' => $request->nama,
            'unique_id' => $request->nim_nip_nidk,
            'keterangan' => $request->keterangan,
            'input_by_user' => 'Publik',
        ]);

        return redirect()->back()->with('success', 'Presensi berhasil disimpan');
    }

    public function tesWA()
    {
        $res = $this->whatsappHelper->sendMessage('6282154036511', 'tes edoxid');
        var_dump($res);
        echo 12312;
    }

    public function submissionDownloadForce($id)
    {
        Carbon::setLocale('id');

        $submission = Submission::where('id', $id)->firstOrFail();
        $type = $submission->type;
        $student = $submission->student;
        $format_required_values = $submission->format->required_values;

        $pembimbings = Verificator::where('type', 'Pembimbing')->where('submission_id', $submission->id)->get();
        $pengujis = Verificator::where('type', 'Penguji')->where('submission_id', $submission->id)->get();
        $index = 1;

        $submission_presences = [];

        $total_rata_rata_nilai_x_bobot = 0;

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
        $bulan_seminar = $this->functionHelper->translateMonthToIndonesian($submission->schedule ? Carbon::parse($submission->schedule)->format('n') : '');
        $bulan_seminar_angka = $submission->schedule ? Carbon::parse($submission->schedule)->format('m') : '';
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
}
