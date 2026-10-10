<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            SettingSeeder::class,
            GradingSeeder::class,
            UserSeeder::class,
            DosenSeeder::class,
            MahasiswaSeeder::class,
            TypeSeeder::class,
            SkillGroupSeeder::class,
            SubmissionSeeder::class,
            AdditionalSettingSeeder::class,
        ]);
    }
}
