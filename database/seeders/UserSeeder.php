<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => "Admin",
            'email' => "admin@gmail.com",
            'unique_id' => 'admin',
            'email_verified_at' => now(),
            'phone_number' => '08' . rand(1000000000, 9999999999),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'role' => 'admin'
        ]);

        User::create([
            'name' => "Bagian Administrasi",
            'email' => "bagianadministrasi@gmail.com",
            'unique_id' => 'bagianadministrasi',
            'email_verified_at' => now(),
            'phone_number' => '08' . rand(1000000000, 9999999999),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'role' => 'bagian administrasi'
        ]);

        // $dosens = [
        //     [
        //         'name' => 'Dr.Yus Sholva, S.T., M.T.',
        //         'email' => 'yusholva',
        //         'position' => 'Ketua Jurusan'
        //     ],
        //     [
        //         'name' => 'Enda Esyudha P, S.T, M.T.',
        //         'email' => 'endaesyudha',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Eva Faja Ripanti, S.Kom, MMSI., Ph.D',
        //         'email' => 'evafajaripanti',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Anggi Srimurdianti S, S.T., M.T.',
        //         'email' => 'anggisrimurdianti',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Morteza Muthahhari, S.Kom, MTI.',
        //         'email' => 'mortezamuthahhari',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Hengky Anra S.T., M.Kom.',
        //         'email' => 'hengkyanra',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Anggi Perwitasari, S.T., M.T.',
        //         'email' => 'anggiperwitasari',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Rudy Dwi Nyoto, S.T., M.Eng.',
        //         'email' => 'rudydwinyoto',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Novi Safriadi, S.T., M.T.',
        //         'email' => 'novisafriadi',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Yulianti, S.Kom, MMSI',
        //         'email' => 'yulianti',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Helfi Nasution, S.Kom, M.Cs.',
        //         'email' => 'helfinasution',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'M. Azhar Irwansyah, S.T., M.Eng.',
        //         'email' => 'azharirwansyah',
        //         'position' => 'Ketua Prodi'
        //     ],
        //     [
        //         'name' => 'Heri Priyanto, S.T., M.T.',
        //         'email' => 'heripriyanto',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Haried Novriando, S.Kom., M. Eng.',
        //         'email' => 'hariednovriando',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Dr. Arif Bijaksana Putra Negara, S.T., M.T.',
        //         'email' => 'arifbijaksana',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Hafiz Muhardi, S.T., M.Kom.',
        //         'email' => 'hafizmuhardi',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Dr. Herry Sujaini, S.T., M.T.',
        //         'email' => 'herrysujaini',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Tursina, S.T., M.Cs.',
        //         'email' => 'tursina',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Helen Sastypratiwi, S.T., M.Eng',
        //         'email' => 'helensastyapratiwi',
        //         'position' => null
        //     ],
        //     [
        //         'name' => 'Rina Septiriana, ST., M.Cs.',
        //         'email' => 'rinaseptriana',
        //         'position' => null
        //     ]
        // ];

        // for ($i = 0; $i < count($dosens); $i++) {
        //     User::create([
        //         'name' => $dosens[$i]['name'],
        //         'email' => $dosens[$i]['email'] . "@gmail.com",
        //         'unique_id' => rand(1000000000, 9999999999),
        //         'position' => $dosens[$i]['position'],
        //         'email_verified_at' => now(),
        //         'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //         'remember_token' => Str::random(10),
        //         'phone_number' => '08' . rand(1000000000, 9999999999),
        //         'role' => 'dosen'
        //     ]);
        // }

        // User::create([
        //     'name' => "Willy Ferry",
        //     'email' => "willyferry@student.untan.ac.id",
        //     'unique_id' => 'D1042181026',
        //     'email_verified_at' => now(),
        //     'phone_number' => '08' . rand(1000000000, 9999999999),
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'remember_token' => Str::random(10),
        //     'role' => 'mahasiswa',
        //     'dosen_id' => 3
        // ]);

        // for ($i = 1; $i <= 10; $i++) {
        //     $dosen = User::where('role', User::ROLE_DOSEN)->inRandomOrder()->first();

        //     User::create([
        //         'name' => "Mahasiswa " . $i,
        //         'email' => "mahasiswa" . $i . "@gmail.com",
        //         'email_verified_at' => now(),
        //         'unique_id' => 'D10' . rand(1000000, 9999999),
        //         'phone_number' => '08' . rand(1000000000, 9999999999),
        //         'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //         'remember_token' => Str::random(10),
        //         'role' => 'mahasiswa',
        //         'dosen_id' => $dosen->id
        //     ]);
        // }

        // \App\Models\User::factory(10)->create();
    }
}
