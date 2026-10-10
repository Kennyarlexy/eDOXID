<?php

namespace Database\Seeders;

use App\Helpers\SpotaHelper;
use App\Models\SkillGroup;
use App\Models\SkillGroupLecturer;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Database\Seeder;

class SkillGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $spotaHelper = new SpotaHelper();
        $spotaHelper->syncSkillGroupData();
    }
}
