<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'key' => 'site_name',
            'value' => 'Laravel'
        ]);

        Setting::create([
            'key' => 'semester',
            'value' => '1'
        ]);

        Setting::create([
            'key' => 'academic_year',
            'value' => '2019/2020'
        ]);

        Setting::create([
            'key' => 'sync_spota_data',
            'value' => date('Y-m-d H:i:s')
        ]);

        Setting::create([
            'key' => 'wa_api_key',
            'value' => '979b07377545d6ccda68e8fa2e8d629cad2216de'
        ]);

        Setting::create([
            'key' => 'wa_sender_phone_number',
            'value' => '6285171564947'
        ]);

        Setting::create([
            'key' => 'google_client_id',
            'value' => '291214359036-ihihpqsldsshb28cvtstnipkm9c656t2.apps.googleusercontent.com',
        ]);

        Setting::create([
            'key' => 'google_client_secret',
            'value' => 'Coe4rYtSHOUTvfEx7oreIBLe'
        ]);

        Setting::create([
            'key' => 'google_refresh_token',
            'value' => '1//04QBeFnX04kFZCgYIARAAGAQSNwF-L9IrgLnK6dDH8ORNsm5QAbUOUuuyCK_0wvSTOe1sYCtC2hejluDjP09muduIXt-k2PhzyzQ'
        ]);

        Setting::create([
            'key' => 'google_drive_folder_id',
            'value' => '1krLCYVRcCZJGsyk7e0glIemjpLncaBxS'
        ]);
    }
}
