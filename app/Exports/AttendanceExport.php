<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Attendance::with(['user', 'event']);
        
        // Применяем фильтры
        if ($this->request->filled('start_date')) {
            $query->whereHas('event', function($q) {
                $q->whereDate('date_time', '>=', $this->request->start_date);
            });
        }
        
        if ($this->request->filled('end_date')) {
            $query->whereHas('event', function($q) {
                $q->whereDate('date_time', '<=', $this->request->end_date);
            });
        }
        
        if ($this->request->filled('event_type')) {
            $query->whereHas('event', function($q) {
                $q->where('type', $this->request->event_type);
            });
        }
        
        if ($this->request->filled('event_group')) {
            $group = $this->request->event_group;
            $query->whereHas('event.eventGroups', function($q) use ($group) {
                $q->where('group', $group);
            });
        }
        
        return $query->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Пользователь',
            'Мероприятие',
            'Тип мероприятия',
            'Дата мероприятия',
            'Статус посещения',
            'Дата отметки'
        ];
    }

    /**
     * @param Attendance $attendance
     * @return array
     */
    public function map($attendance): array
    {
        return [
            $attendance->id,
            $attendance->user->name ?? 'Неизвестно',
            $attendance->event->title ?? 'Неизвестно',
            $attendance->event->type ?? 'Не указан',
            $attendance->event->date_time ? Carbon::parse($attendance->event->date_time)->format('d.m.Y') : 'Не указана',
            $attendance->attended ? 'Посетил' : 'Не посетил',
            $attendance->updated_at->format('d.m.Y H:i:s')
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Стиль для заголовков
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ]
            ],
        ];
    }
}
