<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];
    public int $imported = 0;
    public int $skipped = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Row 1 = header

            //ตรวจสอบตัวแปร username, name, password ว่ามีหรือเปล่า
            if (empty($row['username']) || empty($row['name']) || empty($row['password'])) {
                $this->errors[] = "แถวที่ {$rowNum}: ข้อมูล username, name หรือ password ไม่ครบถ้วน";
                $this->skipped++;
                continue;
            }

            // ตรวจสอบว่ามี username ซ้ำในระบบหรือไม่
            if (User::where('username', trim($row['username']))->exists()) {
                $this->errors[] = "แถวที่ {$rowNum}: username '{$row['username']}' มีอยู่ในระบบแล้ว";
                $this->skipped++;
                continue;
            }

            // ตรวจสอบ department_id ว่ามีอยู่ในระบบหรือไม่ ถ้าเป็นตัวเลขให้ใช้เป็น id ถ้าเป็น string ให้ค้นหาตามชื่อ
            $departmentId = null;
            if (!empty($row['department_id'])) {
                if (is_numeric($row['department_id'])) {
                    $departmentId = (int) $row['department_id'];
                } else {
                    $dept = Department::where('department_name', 'like', '%' . trim($row['department_id']) . '%')->first();
                    $departmentId = $dept?->id;
                }
            }

            // กำหนดให้ใช้ค่า default เป็น 3 (user)
            $roleId = 3;
            if (!empty($row['role_id'])) {
                $roleId = (int) $row['role_id'];
            }

            // สร้างผู้ใช้ใหม่
            User::create([
                'name' => trim($row['name']),
                'username' => trim($row['username']),
                'phone' => trim($row['phone'] ?? ''),
                'department_id' => $departmentId,
                'role_id' => $roleId,
                'password' => Hash::make(trim($row['password'])),
            ]);

            $this->imported++;
        }
    }
}
