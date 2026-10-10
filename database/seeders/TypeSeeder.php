<?php

namespace Database\Seeders;

use App\Models\Format;
use App\Models\GetTypeAccessPermission;
use App\Models\RequiredFile;
use App\Models\RequiredValue;
use App\Models\Type;
use App\Models\User;
use Illuminate\Database\Seeder;

use function PHPSTORM_META\map;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            [
                'name' => 'Proposal',
                'slug' => 'proposal',
                'color' => '#3D9400',
                'description' => '',
                'status' => 'active',
            ],
            [
                'name' => 'Sidang Hasil',
                'slug' => 'sidang-hasil',
                'color' => '#FFB848',
                'description' => '',
                'status' => 'active',
            ],
            [
                'name' => 'Sidang Akhir',
                'slug' => 'sidang-akhir',
                'color' => '#E6674A',
                'description' => '',
                'status' => 'active',
            ]
        ];

        $formats = [
            [
                'name' => 'Format 1',
                'document_format' => 'laporan-proposal-skripsi-seminar-proposal-format-1.docx',
                'document_format_name' => 'format-pengajuan-seminar-proposal.docx',
                'document_format_size' => 104356,
                'is_primary' => 1,
                'is_pembimbing_required' => 1,
                'is_penguji_required' => 0,
                'is_tata_usaha_required' => 1,
                'is_skill_group_required' => 1,
                'is_need_trial_schedule' => 1,
                'is_all_lecturers_can_consultations' => 0,
                'if_approved_next_submission_using_previous_verificators' => 1,
                'type_id' => 1,
                'is_pembimbing_need_to_change_trial_status' => 1,
                'is_penguji_need_to_change_trial_status' => 0,
            ],
            [
                'name' => 'Format 1',
                'document_format' => 'laporan-hasil-proposal-skripsi-sidang-tertutup-format-1.docx',
                'document_format_name' => 'format-pengajuan-seminar-hasil.docx',
                'document_format_size' => 47749,
                'is_primary' => 1,
                'is_pembimbing_required' => 1,
                'is_penguji_required' => 1,
                'is_tata_usaha_required' => 1,
                'is_skill_group_required' => 1,
                'is_need_trial_schedule' => 1,
                'is_all_lecturers_can_consultations' => 0,
                'if_approved_next_submission_using_previous_verificators' => 1,
                'type_id' => 2,
                'is_pembimbing_need_to_change_trial_status' => 1,
                'is_penguji_need_to_change_trial_status' => 0,
            ],
            [
                'name' => 'Format 1',
                'document_format' => 'laporan-skripsi-sidang-terbuka-format-1.docx',
                'document_format_name' => 'format-penilaian-sidang.docx',
                'document_format_size' => 98200,
                'is_primary' => 1,
                'is_pembimbing_required' => 1,
                'is_penguji_required' => 1,
                'is_tata_usaha_required' => 1,
                'is_skill_group_required' => 1,
                'is_need_trial_schedule' => 1,
                'is_all_lecturers_can_consultations' => 0,
                'if_approved_next_submission_using_previous_verificators' => 0,
                'type_id' => 3,
                'is_pembimbing_need_to_change_trial_status' => 1,
                'is_penguji_need_to_change_trial_status' => 1,
            ],
        ];

        $required_files = [
            [
                'name' => 'Proposal',
                'note'=> 'Upload proposal terakhir anda disini',
                'type_file' => 'document_word',
                'format_id' => 1,
                'is_verificator_can_edit' => 1,
                'is_verificator_can_view' => 1,
            ],
            [
                'name' => 'Transkrip Nilai',
                'note'=> 'Upload transkrip nilai terakhir anda disini',
                'type_file' => 'all',
                'format_id' => 1,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'LIRS',
                'note'=> 'Upload LIRS semester bersangkutan anda disini',
                'type_file' => 'all',
                'format_id' => 1,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'PPT',
                'note'=> 'PPT yang akan diseminarkan.',
                'type_file' => 'all',
                'format_id' => 1,
                'is_verificator_can_edit' => 1,
                'is_verificator_can_view' => 1,
            ],
            [
                'name' => 'Skripsi Hasil Seminar',
                'note' => 'Upload laporan skripsi hasil seminar anda disini',
                'type_file' => 'document_word',
                'format_id' => 2,
                'is_verificator_can_edit' => 1,
                'is_verificator_can_view' => 1,
            ],
            [
                'name' => 'Transkrip Nilai',
                'note'=> 'Upload transkrip nilai terakhir anda disini',
                'type_file' => 'all',
                'format_id' => 2,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'LIRS',
                'note'=> 'Upload LIRS semester bersangkutan anda disini',
                'type_file' => 'all',
                'format_id' => 2,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'PPT',
                'note'=> 'PPT yang akan diseminarkan.',
                'type_file' => 'all',
                'format_id' => 2,
                'is_verificator_can_edit' => 1,
                'is_verificator_can_view' => 1,
            ],
            [
                'name' => 'KTM',
                'note'=> 'Kartu Tanda Mahasiswa (KTM) terakhir',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Bukti pembayaran SPP',
                'note'=> 'Bukti pembayaran SPP terakhir.',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Pas Photo',
                'note'=> 'Ukuran 3x4, Warna Latar Belakang Biru',
                'type_file' => 'image',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'LIRS',
                'note'=> 'Upload LIRS semester terakhir anda disini',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Transkrip',
                'note'=> 'Rekapitulasi Nilai Terakhir',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Absen Seminar Outline Skripsi',
                'note'=> 'Bukti absen seminar outline skripsi',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'BA outline',
                'note'=> 'Berita Acara Seminar Outline Skripsi.',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Surat Rekomendasi Pelaksanaan Sidang Akhir Skripsi',
                'note'=> 'Surat Rekomendasi Pelaksanaan Sidang Akhir Skripsi Secara Daring dari Jurusan/Program Studi.',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Bebas Referensi Fakultas Teknik',
                'note'=> 'Upload surat bukti bebas referensi dari perpustakaan fakultas teknik',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Bebas Referensi UNTAN',
                'note'=> 'Upload surat bukti bebas referensi dari perpustakaan UNTAN',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Bebas Referensi Pepustakaan Daerah',
                'note'=> 'Upload surat bukti bebas referensi dari perpustakaan daerah.',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Siap Sidang TI. 08',
                'note'=> 'Upload surat bukti siap sidang TI. 08',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Bukti Upload Jurnal (Teknik Informatika)',
                'note'=> 'Bukti Upload Jurnal (Teknik Informatika).',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Format IF-TA-07',
                'note'=> 'Upload surat format TA 07.',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Kartu Konsultasi Skripsi (B4)',
                'note'=> 'Kartu konsultasi skripsi (B4).',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Format B.1',
                'note'=> 'Upload surat format B.1',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Format B.5',
                'note'=> 'Upload surat format B.5.',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Surat pernyataan wisuda',
                'note'=> 'Surat pernyataan wisuda.',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Biodata Sarjana Baru',
                'note'=> 'Biodata sarjana baru.',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Ijazah SMA',
                'note'=> 'Ijazah SMA',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Sertifikat TOEFL',
                'note'=> 'Sertifikat TOEFL (nilai min. 425)',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'FC KK',
                'note'=> 'FC Kartu Keluarga (KK).',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'FC KTP',
                'note'=> 'FC Kartu Tanda Penduduk (KTP).',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 0,
                'is_verificator_can_view' => 0,
            ],
            [
                'name' => 'Laporan Skripsi',
                'note' => 'Upload laporan skripsi anda disini',
                'type_file' => 'document_word',
                'format_id' => 3,
                'is_verificator_can_edit' => 1,
                'is_verificator_can_view' => 1,
            ],
            [
                'name' => 'PPT',
                'note'=> 'PPT yang akan diseminarkan.',
                'type_file' => 'all',
                'format_id' => 3,
                'is_verificator_can_edit' => 1,
                'is_verificator_can_view' => 1,
            ],
        ];

        $required_values = [
            [
                'name' => 'Kesesuaian Tata Tulis, Kutipan, dan Bahasa Ilmiah',
                'key' => 'nilai1',
                'weight' => 20,
                'note' => "Kesesuaian Tata Tulis, Kutipan, dan Bahasa Ilmiah (Bobot 20%)",
                'format_id' => 1
            ],
            [
                'name' => 'Penguasaan Latar Belakang, Masalah, Tujuan, Manfaat, Solusi dan Relevansi.',
                'key' => 'nilai2',
                'weight' => 20,
                'note' => "Penguasaan Latar Belakang, Masalah, Tujuan, Manfaat, Solusi dan Relevansi. (Bobot 20%)",
                'format_id' => 1
            ],
            [
                'name' => 'Pemahaman dan Penguasaan Teori Dasar',
                'key' => 'nilai3',
                'weight' => 30,
                'note' => "Pemahaman dan Penguasaan Teori Dasar (Bobot 30%)",
                'format_id' => 1
            ],
            [
                'name' => 'Penguasaan Metodologi Penelitian',
                'key' => 'nilai4',
                'weight' => 30,
                'note' => "Penguasaan Metodologi Penelitian (Bobot 30%)",
                'format_id' => 1
            ],
            [
                'name' => 'Isi/Tulisan Skripsi',
                'key' => 'nilai1',
                'weight' => 12.5,
                'note' => "Isi/Tulisan Skripsi (Bobot 12.5%)",
                'format_id' => 3
            ],
            [
                'name' => 'Kemampuan mempertahankan isi Skripsi/Tugas Akhir',
                'key' => 'nilai2',
                'weight' => 12.5,
                'note' => "Kemampuan mempertahankan isi Skripsi/Tugas Akhir (Bobot 12.5%)",
                'format_id' => 3
            ],
            [
                'name' => 'Kemampuan melakukan penalaran, memaparkan pendapat sesuai dengan pertanyaan',
                'key' => 'nilai3',
                'weight' => 12.5,
                'note' => "Kemampuan melakukan penalaran, memaparkan pendapat sesuai dengan pertanyaan (Bobot 12.5%)",
                'format_id' => 3
            ],
            [
                'name' => 'Penguasaan materi bidang studi dihubungkan dengan isi Skripsi/Tugas Akhir',
                'key' => 'nilai4',
                'weight' => 12.5,
                'note' => "Penguasaan materi bidang studi dihubungkan dengan isi Skripsi/Tugas Akhir (Bobot 12.5%)",
                'format_id' => 3
            ],
            [
                'name' => 'Tanggung jawab ilmiah dalam mengutip, memaparkan teori-teori dan menarik kesimpulan',
                'key' => 'nilai5',
                'weight' => 12.5,
                'note' => "Tanggung jawab ilmiah dalam mengutip, memaparkan teori-teori dan menarik kesimpulan (Bobot 12.5%)",
                'format_id' => 3
            ],
            [
                'name' => 'Relevansi Skripsi/Tugas Akhir dengan Program Studi',
                'key' => 'nilai6',
                'weight' => 12.5,
                'note' => "Relevansi Skripsi/Tugas Akhir dengan Program Studi (Bobot 12.5%)",
                'format_id' => 3
            ],
            [
                'name' => 'Tata Tulis dan bahasa ilmiah',
                'key' => 'nilai7',
                'weight' => 12.5,
                'note' => "Tata Tulis dan bahasa ilmiah (Bobot 12.5%)",
                'format_id' => 3
            ],
            [
                'name' => 'Kemampuan memaparkan kegunaan',
                'key' => 'nilai8',
                'weight' => 12.5,
                'note' => "Kemampuan memaparkan kegunaan (Bobot 12.5%)",
                'format_id' => 3
            ],
        ];

        $get_type_access_permissions = [
            [
                'type_id' => 1,
                'access_type_id' => 2,
                'format_id' => 1
            ],
            [
                'type_id' => 2,
                'access_type_id' => 3,
                'format_id' => 2
            ],
        ];

        foreach($types as $type) {
            Type::firstOrCreate($type);
        }

        foreach($formats as $format) {
            Format::firstOrCreate($format);
        }

        foreach ($required_files as $required_file) {
            RequiredFile::firstOrCreate($required_file);
        }

        foreach ($required_values as $required_value) {
            RequiredValue::firstOrCreate($required_value);
        }

        foreach ($get_type_access_permissions as $get_type_access_permission) {
            GetTypeAccessPermission::firstOrCreate($get_type_access_permission);
        }
    }
}
