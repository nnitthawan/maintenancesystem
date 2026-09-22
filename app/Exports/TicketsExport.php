<?php

namespace App\Exports;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TicketsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected string $status;
    protected ?int $categoryId;
    protected ?int $departmentId;
    protected ?string $from;
    protected ?string $to;

    public function __construct(
        string $status = '',
        ?int $categoryId = null,
        ?int $departmentId = null,
        ?string $from = null,
        ?string $to = null
    )
    {
        $this->status = $status;
        $this->categoryId = $categoryId;
        $this->departmentId = $departmentId;
        $this->from = $from;
        $this->to = $to;
    }

    public function query()
    {
        $query = Ticket::with(['reporter.department', 'category'])
            ->latest();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->isAdmin()) {
            $query->where('user_id', Auth::id());
        }

        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        if ($this->departmentId) {
            $query->whereHas('reporter', function ($reporterQuery) {
                $reporterQuery->where('department_id', $this->departmentId);
            });
        }

        if ($this->from) {
            $query->whereDate('created_at', '>=', $this->from);
        }

        if ($this->to) {
            $query->whereDate('created_at', '<=', $this->to);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'เลขที่ใบแจ้งซ่อม',
            'ผู้แจ้งซ่อม',
            'แผนก/สังกัด',
            'หมวดหมู่',
            'รหัสครุภัณฑ์',
            'สถานที่/อาคาร/ห้อง',
            'รายละเอียดปัญหา',
            'สถานะ',
            'วันที่แจ้ง',
            'อัปเดตล่าสุด',
        ];
    }

    public function map($ticket): array
    {
        $statusLabel = match ($ticket->status) {
            'pending'     => 'รอรับเรื่อง',
            'in_progress' => 'กำลังดำเนินการ',
            'completed'   => 'เสร็จสิ้น',
            'cancelled'   => 'ยกเลิก',
            default       => $ticket->status,
        };

        return [
            $ticket->ticket_no,
            $ticket->reporter->name ?? '-',
            $ticket->reporter->department->department_name ?? '-',
            $ticket->category->category_name ?? '-',
            $ticket->asset_no ?: '-',
            $ticket->location,
            $ticket->symptom,
            $statusLabel,
            $ticket->created_at ? $ticket->created_at->format('d/m/Y H:i') : '-',
            $ticket->updated_at ? $ticket->updated_at->format('d/m/Y H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E2A44']],
            ],
        ];
    }
}
