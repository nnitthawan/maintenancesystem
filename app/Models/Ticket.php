<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_no',
        'user_id',
        'category_id',
        'asset_no',
        'location',
        'symptom',
        'status',
    ];

    // ผู้แจ้งซ่อม
    public function reporter()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // หมวดหมู่การซ่อม
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // รูปภาพประกอบการแจ้งซ่อม (มีหลายรูป)
    public function images()
    {
        return $this->hasMany(TicketImage::class, 'ticket_id');
    }

    // การแจ้งเตือนที่เกี่ยวข้อง
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'ticket_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending', 'open' => '<span class="badge bg-primary">เปิด / รอรับเรื่อง</span>',
            'in_progress' => '<span class="badge bg-warning text-dark">กำลังดำเนินการ</span>',
            'resolved', 'completed' => '<span class="badge bg-success">แก้ไขแล้ว / เสร็จสิ้น</span>',
            'closed', 'cancelled' => '<span class="badge bg-secondary">ปิด / ยกเลิก</span>',
            default => '<span class="badge bg-secondary">ไม่ทราบสถานะ</span>',
        };
    }
}