<?php

namespace Database\Seeders;

use App\Helpers\SpotaHelper;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DosenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $spotaHelper = new SpotaHelper();
        $spotaHelper->syncLecturerData();
    }
}
