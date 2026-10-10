<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class AdditionalSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'key' => 'site_logo',
            'value' => 'laravel.png'
        ]);

        Setting::create([
            'key' => 'terms_of_service',
            'value' => '<h4>Syarat & Ketentuan</h4>
                        <p>
                            Mahasiswa yang mengajukan pengajuan ini harus memenuhi syarat dan ketentuan berikut:
                            <ol>
                                <li>Berkas syarat telah terupload semua.</li>
                                <li>syarat ke dua</li>
                                <li>syarat ke tiga</li>
                                <li>syarat ke empat</li>
                            </ol>
                        </p>

                        <h4>Harus diperhatikan bahwa:</h4>
                        <ol>
                            <li>Setelah menyetujui ketentuan dan persyaratan diatas maka mahasiswa harus siap terhadap resiko dan harus bertanggung jawab terhadap tindakan yang melanggar.</li>
                        </ol>'
        ]);
    }
}
