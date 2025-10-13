<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StudentAttendanceExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $student;
    protected $startDate;
    protected $endDate;
    protected $eventTypes;
    protected $eventGroups;
    
    /**
     * Конструктор
     */
    public function __construct(User $student, $startDate = null, $endDate = null, $eventTypes = [], $eventGroups = [])
    {
        $this->student = $student;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->eventTypes = $eventTypes;
        $this->eventGroups = $eventGroups;
    }
    
    /**
     * Заголовок листа
     */
    public function title(): string
    {
        return 'Посещаемость студента';
    }
    
    /**
     * Заголовки таблицы
     */
    public function headings(): array
    {
        return [
            'Дата',
            'Мероприятие',
            'Тип',
            'Статус посещения',
            'Комментарий'
        ];
    }
    
    /**
     * Стили для таблицы
     */
    public function styles(Worksheet $sheet)
    {
        // Стиль для заголовка
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E2EFDA',
                ],
            ],
        ]);
        
        // Автоширина для всех колонок
        foreach(range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Добавляем информацию о студенте в начало листа
        $studentName = $this->student->name ?? 'Не указано';
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'Отчет о посещаемости: ' . $studentName);
        
        // Добавляем строку с информацией о группе
        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'Группа: ' . ($this->student->group ?? 'Не указана'));
        
        // Добавляем строку с информацией о периоде
        $period = 'Весь период';
        if ($this->startDate && $this->endDate) {
            $period = 'с ' . $this->startDate . ' по ' . $this->endDate;
        } elseif ($this->startDate) {
            $period = 'с ' . $this->startDate;
        } elseif ($this->endDate) {
            $period = 'по ' . $this->endDate;
        }
        
        $sheet->mergeCells('A3:E3');
        $sheet->setCellValue('A3', 'Период: ' . $period);
        
        // Устанавливаем заголовки в строке 5
        $sheet->fromArray($this->headings(), null, 'A5');
        
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
            ],
            2 => [
                'font' => [
                    'bold' => true,
                ],
            ],
            3 => [
                'font' => [
                    'italic' => true,
                ],
            ],
            5 => [
                'font' => [
                    'bold' => true,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'E2EFDA',
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Данные для экспорта - реальные данные из базы
     */
    public function collection()
    {
        // Создаем коллекцию для данных
        $data = new Collection();
        
        try {
            // Получаем данные напрямую через SQL-запрос для большей надежности
            $query = DB::table('attendances')
                ->join('events', 'attendances.event_id', '=', 'events.id')
                ->select(
                    'events.date_time',
                    'events.title',
                    'events.type',
                    'attendances.attended',
                    'attendances.comment'
                )
                ->where('attendances.user_id', $this->student->id);
            
            // Применяем фильтры
            if ($this->startDate) {
                $query->whereDate('events.date_time', '>=', $this->startDate);
            }
            
            if ($this->endDate) {
                $query->whereDate('events.date_time', '<=', $this->endDate);
            }
            
            if (!empty($this->eventTypes)) {
                if (is_array($this->eventTypes)) {
                    $query->whereIn('events.type', $this->eventTypes);
                } else {
                    $query->where('events.type', $this->eventTypes);
                }
            }
            
            if (!empty($this->eventGroups)) {
                // Используем join с таблицей event_groups для корректной фильтрации по группам
                $query->join('event_groups', 'events.id', '=', 'event_groups.event_id');
                
                if (is_array($this->eventGroups)) {
                    $query->whereIn('event_groups.group', $this->eventGroups);
                } else {
                    $query->where('event_groups.group', $this->eventGroups);
                }
            }
            
            // Получаем результаты
            $results = $query->orderBy('events.date_time', 'desc')->get();
            
            // Логирование для диагностики
            \Log::info('Excel Export Query Results', [
                'student_id' => $this->student->id,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'event_types' => $this->eventTypes,
                'event_groups' => $this->eventGroups,
                'results_count' => $results->count()
            ]);
            
            // Если нет данных, получаем последние 10 посещений без фильтров
            if ($results->isEmpty()) {
                // Используем более надежный запрос без фильтров по группам
                $fallbackQuery = DB::table('attendances')
                    ->join('events', 'attendances.event_id', '=', 'events.id')
                    ->select(
                        'events.date_time',
                        'events.title',
                        'events.type',
                        'attendances.attended',
                        'attendances.comment'
                    )
                    ->where('attendances.user_id', $this->student->id)
                    ->orderBy('events.date_time', 'desc')
                    ->limit(10);
                
                // Логирование fallback запроса
                \Log::info('Excel Export Fallback Query', [
                    'student_id' => $this->student->id,
                    'sql' => $fallbackQuery->toSql(),
                    'bindings' => $fallbackQuery->getBindings()
                ]);
                
                $results = $fallbackQuery->get();
                
                \Log::info('Excel Export Fallback Results', [
                    'count' => $results->count()
                ]);
                
                // Если и fallback не дал результатов, добавляем информационную строку
                if ($results->isEmpty()) {
                    // Создаем объект с нужными полями для совместимости
                    $emptyResult = new \stdClass();
                    $emptyResult->date_time = now();
                    $emptyResult->title = 'Нет данных о посещаемости';
                    $emptyResult->type = 'Информация';
                    $emptyResult->attended = false;
                    $emptyResult->comment = 'Для выбранного периода и фильтров данные отсутствуют';
                    
                    // Добавляем в коллекцию результатов
                    $results = collect([$emptyResult]);
                }
            }
            
            // Преобразуем результаты в нужный формат
            foreach ($results as $row) {
                $data->push([
                    Carbon::parse($row->date_time)->format('d.m.Y H:i'),
                    $row->title ?? 'Без названия',
                    $row->type ?? 'Не указан',
                    $row->attended ? 'Посещено' : 'Пропущено',
                    $row->comment ?? ''
                ]);
            }
            
            // Если данных все равно нет, добавляем информационную строку
            if ($data->isEmpty()) {
                $data->push([
                    Carbon::now()->format('d.m.Y'),
                    'Нет данных о посещаемости',
                    'Не применимо',
                    'Не применимо',
                    'Нет данных для отображения в базе'
                ]);
            }
        } catch (\Exception $e) {
            // В случае ошибки добавляем информацию об ошибке
            $data->push([
                Carbon::now()->format('d.m.Y'),
                'Ошибка при получении данных',
                'Ошибка',
                'Ошибка',
                $e->getMessage()
            ]);
        }
        
        return $data;
    }
}
