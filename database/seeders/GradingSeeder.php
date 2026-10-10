<?php

namespace Database\Seeders;

use App\Models\Grading;
use Illuminate\Database\Seeder;

class GradingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $gradings = [
            [
                'maksimal_score' => 100,
                'minimal_score' => 80,
                'grade' => 'A',
                'description' => 'Diterima',
                'submission_status' => 'approved'
            ],
            [
                'maksimal_score' => 79.99,
                'minimal_score' => 75,
                'grade' => 'B+',
                'description' => 'Diterima',
                'submission_status' => 'approved'
            ],
            [
                'maksimal_score' => 74.99,
                'minimal_score' => 70,
                'grade' => 'B',
                'description' => 'Diterima',
                'submission_status' => 'approved'
            ],
            [
                'maksimal_score' => 69.99,
                'minimal_score' => 65,
                'grade' => 'C+',
                'description' => 'Diterima',
                'submission_status' => 'approved'
            ],
            [
                'maksimal_score' => 64.99,
                'minimal_score' => 60,
                'grade' => 'C',
                'description' => 'Diterima',
                'submission_status' => 'approved'
            ],
            [
                'maksimal_score' => 59.99,
                'minimal_score' => 50,
                'grade' => 'D',
                'description' => 'Diulang',
                'submission_status' => 'repeated'
            ],
            [
                'maksimal_score' => 49.99,
                'minimal_score' => 0,
                'grade' => 'E',
                'description' => 'Ditolak',
                'submission_status' => 'rejected'
            ],
        ];

        foreach ($gradings as $grading) {
            Grading::create($grading);
        }
    }
}
