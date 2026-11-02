<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'ticket_id',
        'message',
        'is_read',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function getDisplayMessageAttribute(): string
    {
        return str_replace(
            ['เปลี่ยนสถานะเป็น pending', 'เปลี่ยนสถานะเป็น in_progress', 'เปลี่ยนสถานะเป็น completed', 'เปลี่ยนสถานะเป็น cancelled'],
            ['เปลี่ยนสถานะเป็น รอรับเรื่อง', 'เปลี่ยนสถานะเป็น กำลังดำเนินการ', 'เปลี่ยนสถานะเป็น เสร็จสิ้น', 'เปลี่ยนสถานะเป็น ยกเลิก'],
            $this->message,
        );
    }
}