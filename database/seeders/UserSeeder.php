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
        ]);
    }
}
