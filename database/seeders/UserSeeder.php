<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('users')->insert([
            [
                'name' => 'ผู้ดูแลระบบ IT',
                'phone' => '',
                'department_id' => 13, // กลุ่มงานผู้ดูแลระบบ(แอดมิน)
                'username' => 'admin',
                'password' => Hash::make('admin11211'),
                'role_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'เจ้าหน้าที่',
                'phone' => '0800000000',
                'department_id' => 3,
                'username' => 'user',
                'password' => Hash::make('password123'),
                'role_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
