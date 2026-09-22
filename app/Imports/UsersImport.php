<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Validator;

class UsersImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];
    public int $imported = 0;
    public int $skipped = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Row 1 = header

            // Required: username, name, password
            if (empty($row['username']) || empty($row['name']) || empty($row['password'])) {
                $this->errors[] = "แถวที่ {$rowNum}: ข้อมูล username, name หรือ password ไม่ครบถ้วน";
                $this->skipped++;
                continue;
            }

            // Check duplicate username
            if (User::where('username', trim($row['username']))->exists()) {
                $this->errors[] = "แถวที่ {$rowNum}: username '{$row['username']}' มีอยู่ในระบบแล้ว";
                $this->skipped++;
                continue;
            }

            // Find department_id by name if provided as text
            $departmentId = null;
            if (!empty($row['department_id'])) {
                if (is_numeric($row['department_id'])) {
                    $departmentId = (int) $row['department_id'];
                } else {
                    $dept = Department::where('department_name', 'like', '%' . trim($row['department_id']) . '%')->first();
                    $departmentId = $dept?->id;
                }
            }

            // role_id: default user=3
            $roleId = 3;
            if (!empty($row['role_id'])) {
                $roleId = (int) $row['role_id'];
            }

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
