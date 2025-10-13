<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class EventsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $eventTypes;
    protected $eventGroups;

    public function __construct($startDate = null, $endDate = null, $eventTypes = [], $eventGroups = [])
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->eventTypes = $eventTypes;
        $this->eventGroups = $eventGroups;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Event::with(['attendances', 'eventGroups']);
        
        // Применяем фильтры
        if ($this->startDate) {
            $query->whereDate('date_time', '>=', $this->startDate);
        }
        
        if ($this->endDate) {
            $query->whereDate('date_time', '<=', $this->endDate);
        }
        
        if (!empty($this->eventTypes)) {
            $query->whereIn('type', $this->eventTypes);
        }
        
        if (!empty($this->eventGroups)) {
            $query->whereHas('eventGroups', function($q) {
                $q->whereIn('group', $this->eventGroups);
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
            'Название',
            'Описание',
            'Дата',
            'Время начала',
            'Время окончания',
            'Место проведения',
            'Тип мероприятия',
            'Тема',
            'Количество участников',
            'Посетили',
            'Процент посещаемости'
        ];
    }

    /**
     * @param Event $event
     * @return array
     */
    public function map($event): array
    {
        $attendedCount = $event->attendances->where('attended', 1)->count();
        $totalCount = $event->attendances->count();
        $percentage = $totalCount > 0 ? round(($attendedCount / $totalCount) * 100, 1) : 0;
        
        $dateTime = Carbon::parse($event->date_time);
        
        return [
            $event->id,
            $event->title,
            $event->description,
            $dateTime->format('d.m.Y'),
            $dateTime->format('H:i'),
            '', // Нет данных о времени окончания
            $event->location,
            $event->type ?? 'Не указан',
            $event->theme ?? 'Не указана',
            $totalCount,
            $attendedCount,
            $percentage . '%'
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
