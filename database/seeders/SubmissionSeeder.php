<?php

namespace Database\Seeders;

use App\Helpers\SpotaHelper;
use App\Models\SkillGroup;
use App\Models\Submission;
use App\Models\Type;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $spotaHelper = new SpotaHelper();
        $spotaHelper->syncSubmissionData();
    }
}
