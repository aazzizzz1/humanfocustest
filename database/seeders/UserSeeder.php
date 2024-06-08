<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'admin',
                'email' => 'admin@softui.com',
                'password' => Hash::make('secret'),
                'job' => 'Software Engineer',
                'work_location' => 'San Francisco',
                'examiner_name' => 'John Doe',
                'age' => 30,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Aziz',
                'email' => 'aziz@gmail.com',
                'password' => Hash::make('Aziz123!'),
                'job' => 'Data Scientist',
                'work_location' => 'New York',
                'examiner_name' => 'Jane Smith',
                'age' => 22,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'Aryo',
                'email' => 'aryo@gmail.com',
                'password' => Hash::make('Aryo123!'),
                'job' => 'K3',
                'work_location' => 'PPNS',
                'examiner_name' => 'Jane Smith',
                'age' => 25,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
