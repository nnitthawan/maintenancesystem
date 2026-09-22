<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $categories = [
            'เครื่องสำรองไฟ / UPS',
            'เครื่องคอมพิวเตอร์ / Computer',
            'เครื่องปริ้นเตอร์ / เครื่องแสกน',
            'เมาส์ / คีย์บอร์ด',
            'จอคอมพิวเตอร์',
            'อินเทอร์เน็ต',
            'อื่นๆ',
        ];
    
        $data = array_map(function ($name) use ($now) {
            return [
                'category_name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $categories);

        DB::table('categories')->insert($data);
    }
}