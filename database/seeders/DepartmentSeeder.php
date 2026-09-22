<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $departments = [
            'กลุ่มงานการแพทย์',
            'กลุ่มงานบริหารทั่วไป',
            'กลุ่มงานการพยาบาล',
            'กลุ่มงานทันตกรรม',
            'กลุ่มงานเภสัชกรรม',
            'กลุ่มงานการบริการด้านปฐมภูมิและองค์รวม',
            'กลุ่มงานแพทย์แผนไทย',
            'กลุ่มงานเวชกรรมฟื้นฟู',
            'กลุ่มงานประกันสุขภาพ ยุทธศาสตร์ และสารสนเทศทางการแพทย์',
            'กลุ่มงานเทคนิคการแพทย์',
            'กลุ่มงานโภชนาศาสตร์',
            'กลุ่มงานรังสีวิทยา',
            'ผู้ดูแลระบบ(แอดมิน)'
        ];

        $data = array_map(function ($name) use ($now) {
            return [
                'department_name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $departments);

        DB::table('departments')->insert($data);
    }
}