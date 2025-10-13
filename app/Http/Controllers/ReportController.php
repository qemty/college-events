<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventGroup;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EventsExport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Получение параметров фильтрации
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $eventTypes = $request->input('event_types', []);
        $eventGroups = $request->input('event_groups', []);
        
        // Базовый запрос для получения мероприятий
        $eventsQuery = Event::with(['eventGroups', 'attendances', 'attendances.user']);
        
        // Применение фильтров
        if ($startDate) {
            $eventsQuery->whereDate('date_time', '>=', $startDate);
        }
        
        if ($endDate) {
            $eventsQuery->whereDate('date_time', '<=', $endDate);
        }
        
        if (!empty($eventTypes)) {
            $eventsQuery->whereIn('type', $eventTypes);
        }
        
        if (!empty($eventGroups)) {
            $eventsQuery->whereHas('eventGroups', function($query) use ($eventGroups) {
                $query->whereIn('group', $eventGroups);
            });
        }
        
        // Получение мероприятий
        $events = $eventsQuery->orderBy('date_time', 'desc')->get();
        
        // Проверка наличия данных о посещаемости
        $hasAttendanceData = false;
        $totalAttendances = 0;
        
        foreach ($events as $event) {
            $totalAttendances += $event->attendances->count();
            if ($event->attendances->count() > 0) {
                $hasAttendanceData = true;
                break;
            }
        }
        
        // Получение всех типов мероприятий и групп для фильтров
        $allEventTypes = Event::distinct()->pluck('type')->filter()->values();
        $allEventGroups = EventGroup::distinct()->pluck('group')->filter()->values();
        
        // Получение данных для графиков
        $eventsByType = $this->getEventsByType($events);
        $attendanceByEvent = $this->getAttendanceByEvent($events);
        $attendanceByMonth = $this->getAttendanceByMonth($events);
        $attendanceRateByGroup = $this->getAttendanceRateByGroup($events);
        
        // Получение данных для тренда посещаемости
        $attendanceTrend = $this->getAttendanceTrend($startDate, $endDate, $eventTypes, $eventGroups);
        
        // Получение данных для тепловой карты посещаемости
        $attendanceHeatmap = $this->getAttendanceHeatmap($startDate, $endDate, $eventTypes, $eventGroups);
        
        // Получение данных для сравнения периодов
        $periodComparison = $this->getPeriodComparison($startDate, $endDate, $eventTypes, $eventGroups);
        
        return view('reports.index', compact(
            'events',
            'eventsByType',
            'attendanceByEvent',
            'attendanceByMonth',
            'attendanceRateByGroup',
            'attendanceTrend',
            'attendanceHeatmap',
            'periodComparison',
            'allEventTypes',
            'allEventGroups',
            'hasAttendanceData',
            'totalAttendances'
        ));
    }
    
    /**
     * Получение данных о мероприятиях по типам
     */
    private function getEventsByType($events)
    {
        $eventsByType = [];
        $types = $events->pluck('type')->unique()->filter();
        
        foreach ($types as $type) {
            $count = $events->where('type', $type)->count();
            $eventsByType[] = [
                'name' => $type,
                'total' => $count
            ];
        }
        
        return $eventsByType;
    }
    
    /**
     * Получение данных о посещаемости по мероприятиям
     */
    private function getAttendanceByEvent($events)
    {
        $attendanceByEvent = [];
        
        foreach ($events as $event) {
            $totalAttendances = $event->attendances->count();
            if ($totalAttendances > 0) {
                $attendedCount = $event->attendances->where('attended', 1)->count();
                $notAttendedCount = $totalAttendances - $attendedCount;
                
                $attendanceByEvent[] = [
                    'title' => $event->title,
                    'attended' => $attendedCount,
                    'not_attended' => $notAttendedCount,
                    'total' => $totalAttendances,
                    'rate' => round(($attendedCount / $totalAttendances) * 100)
                ];
            } else {
                // Добавляем мероприятие с нулевой посещаемостью
                $attendanceByEvent[] = [
                    'title' => $event->title,
                    'attended' => 0,
                    'not_attended' => 0,
                    'total' => 0,
                    'rate' => 0
                ];
            }
        }
        
        // Сортировка по проценту посещаемости (по убыванию)
        usort($attendanceByEvent, function($a, $b) {
            return $b['rate'] <=> $a['rate'];
        });
        
        // Ограничение количества мероприятий для отображения
        return array_slice($attendanceByEvent, 0, 10);
    }
    
    /**
     * Получение данных о посещаемости по месяцам
     */
    private function getAttendanceByMonth($events)
    {
        $attendanceByMonth = [];
        $months = [];
        
        foreach ($events as $event) {
            $monthYear = Carbon::parse($event->date_time)->format('Y-m');
            $monthName = Carbon::parse($event->date_time)->format('M Y');
            
            if (!isset($months[$monthYear])) {
                $months[$monthYear] = [
                    'month_year' => $monthName,
                    'attended' => 0,
                    'not_attended' => 0,
                    'total' => 0
                ];
            }
            
            $attendedCount = $event->attendances->where('attended', 1)->count();
            $notAttendedCount = $event->attendances->where('attended', 0)->count();
            
            $months[$monthYear]['attended'] += $attendedCount;
            $months[$monthYear]['not_attended'] += $notAttendedCount;
            $months[$monthYear]['total'] += $attendedCount + $notAttendedCount;
        }
        
        // Сортировка по месяцам
        ksort($months);
        
        foreach ($months as $month) {
            $attendanceByMonth[] = $month;
        }
        
        return $attendanceByMonth;
    }
    
    /**
     * Получение данных о проценте посещаемости по группам
     */
    private function getAttendanceRateByGroup($events)
    {
        $attendanceRateByGroup = [];
        $groups = [];
        
        // Получаем все группы из мероприятий
        $allGroups = [];
        foreach ($events as $event) {
            foreach ($event->eventGroups as $eventGroup) {
                $allGroups[$eventGroup->group] = true;
            }
        }
        
        // Инициализируем счетчики для каждой группы
        foreach (array_keys($allGroups) as $group) {
            $groups[$group] = [
                'name' => $group,
                'attended' => 0,
                'total' => 0
            ];
        }
        
        // Подсчитываем посещаемость для каждой группы
        foreach ($events as $event) {
            // Получаем группы этого мероприятия
            $eventGroupNames = $event->eventGroups->pluck('group')->toArray();
            
            // Для каждой группы мероприятия
            foreach ($eventGroupNames as $groupName) {
                // Находим пользователей этой группы
                $groupAttendances = $event->attendances->filter(function($attendance) use ($groupName) {
                    return $attendance->user && $attendance->user->group === $groupName;
                });
                
                // Если есть посещения для этой группы
                if ($groupAttendances->count() > 0) {
                    $groups[$groupName]['attended'] += $groupAttendances->where('attended', 1)->count();
                    $groups[$groupName]['total'] += $groupAttendances->count();
                }
            }
        }
        
        // Формируем итоговый массив с процентами
        foreach ($groups as $group) {
            if ($group['total'] > 0) {
                $attendanceRateByGroup[] = [
                    'name' => $group['name'],
                    'attended' => $group['attended'],
                    'total' => $group['total'],
                    'rate' => round(($group['attended'] / $group['total']) * 100)
                ];
            } else {
                // Добавляем группу с нулевой посещаемостью
                $attendanceRateByGroup[] = [
                    'name' => $group['name'],
                    'attended' => 0,
                    'total' => 0,
                    'rate' => 0
                ];
            }
        }
        
        // Сортировка по проценту посещаемости (по убыванию)
        usort($attendanceRateByGroup, function($a, $b) {
            return $b['rate'] <=> $a['rate'];
        });
        
        return $attendanceRateByGroup;
    }
    
    /**
     * Получение данных для тренда посещаемости по неделям
     */
    private function getAttendanceTrend($startDate, $endDate, $eventTypes, $eventGroups)
    {
        // Определение периода для анализа
        if (!$startDate) {
            $startDate = Carbon::now()->subMonths(3)->startOfDay()->format('Y-m-d');
        }
        
        if (!$endDate) {
            $endDate = Carbon::now()->format('Y-m-d');
        }
        
        // Получение мероприятий за указанный период
        $eventsQuery = Event::whereDate('date_time', '>=', $startDate)
                           ->whereDate('date_time', '<=', $endDate);
        
        if (!empty($eventTypes)) {
            $eventsQuery->whereIn('type', $eventTypes);
        }
        
        if (!empty($eventGroups)) {
            $eventsQuery->whereHas('eventGroups', function($query) use ($eventGroups) {
                $query->whereIn('group', $eventGroups);
            });
        }
        
        $events = $eventsQuery->with(['attendances', 'attendances.user'])->get();
        
        // Создаем массив всех недель в диапазоне дат
        $startWeek = Carbon::parse($startDate)->startOfWeek();
        $endWeek = Carbon::parse($endDate)->startOfWeek();
        $currentWeek = $startWeek->copy();
        
        $weeks = [];
        while ($currentWeek->lte($endWeek)) {
            $weekStart = $currentWeek->format('Y-m-d');
            $weekEnd = $currentWeek->copy()->endOfWeek()->format('Y-m-d');
            $weekKey = $weekStart . ' - ' . $weekEnd;
            
            $weeks[$weekKey] = [
                'week' => $weekKey,
                'attended' => 0,
                'total' => 0,
                'rate' => 0
            ];
            
            $currentWeek->addWeek();
        }
        
        // Заполняем данные о посещаемости
        foreach ($events as $event) {
            $eventDate = Carbon::parse($event->date_time);
            $weekStart = $eventDate->copy()->startOfWeek()->format('Y-m-d');
            $weekEnd = $eventDate->copy()->endOfWeek()->format('Y-m-d');
            $weekKey = $weekStart . ' - ' . $weekEnd;
            
            if (!isset($weeks[$weekKey])) {
                continue; // Пропускаем, если неделя не входит в диапазон
            }
            
            // Фильтруем посещения по группам, если указаны
            $eventAttendances = $event->attendances;
            if (!empty($eventGroups)) {
                $eventAttendances = $eventAttendances->filter(function($attendance) use ($eventGroups) {
                    return $attendance->user && in_array($attendance->user->group, $eventGroups);
                });
            }
            
            $totalAttendances = $eventAttendances->count();
            $attendedCount = $eventAttendances->where('attended', 1)->count();
            
            $weeks[$weekKey]['total'] += $totalAttendances;
            $weeks[$weekKey]['attended'] += $attendedCount;
        }
        
        // Вычисление процента посещаемости и сортировка по неделям
        ksort($weeks);
        
        $trend = [];
        foreach ($weeks as $week) {
            $week['rate'] = $week['total'] > 0 ? round(($week['attended'] / $week['total']) * 100) : 0;
            $trend[] = $week;
        }
        
        return $trend;
    }
    
    /**
     * Получение данных для тепловой карты посещаемости
     */
    private function getAttendanceHeatmap($startDate, $endDate, $eventTypes, $eventGroups)
    {
        // Определение периода для анализа
        if (!$startDate) {
            $startDate = Carbon::now()->subMonths(3)->startOfDay()->format('Y-m-d');
        }
        
        if (!$endDate) {
            $endDate = Carbon::now()->format('Y-m-d');
        }
        
        // Получение данных о посещаемости
        $eventsQuery = Event::whereDate('date_time', '>=', $startDate)
                           ->whereDate('date_time', '<=', $endDate);
        
        if (!empty($eventTypes)) {
            $eventsQuery->whereIn('type', $eventTypes);
        }
        
        if (!empty($eventGroups)) {
            $eventsQuery->whereHas('eventGroups', function($query) use ($eventGroups) {
                $query->whereIn('group', $eventGroups);
            });
        }
        
        $events = $eventsQuery->with(['attendances', 'attendances.user'])->get();
        
        // Определение дней недели и временных интервалов
        $days = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'];
        $times = ['8:00-10:00', '10:00-12:00', '12:00-14:00', '14:00-16:00', '16:00-18:00', '18:00-20:00'];
        
        // Инициализация данных для тепловой карты
        $heatmapData = [];
        
        for ($dayIndex = 0; $dayIndex < count($days); $dayIndex++) {
            for ($timeIndex = 0; $timeIndex < count($times); $timeIndex++) {
                // Фильтруем мероприятия по дню недели и времени
                $filteredEvents = $events->filter(function($event) use ($dayIndex, $timeIndex, $times) {
                    $eventDateTime = Carbon::parse($event->date_time);
                    $eventDayIndex = $eventDateTime->dayOfWeekIso - 1; // 1 (Понедельник) -> 0
                    
                    if ($eventDayIndex != $dayIndex) {
                        return false;
                    }
                    
                    $eventHour = $eventDateTime->hour;
                    $timeRange = explode('-', $times[$timeIndex]);
                    $startHour = (int)explode(':', $timeRange[0])[0];
                    $endHour = (int)explode(':', $timeRange[1])[0];
                    
                    return $eventHour >= $startHour && $eventHour < $endHour;
                });
                
                // Подсчитываем посещаемость для этой ячейки
                $totalAttendances = 0;
                $attendedCount = 0;
                
                foreach ($filteredEvents as $event) {
                    // Фильтруем посещения по группам, если указаны
                    $eventAttendances = $event->attendances;
                    if (!empty($eventGroups)) {
                        $eventAttendances = $eventAttendances->filter(function($attendance) use ($eventGroups) {
                            return $attendance->user && in_array($attendance->user->group, $eventGroups);
                        });
                    }
                    
                    $totalAttendances += $eventAttendances->count();
                    $attendedCount += $eventAttendances->where('attended', 1)->count();
                }
                
                $rate = $totalAttendances > 0 ? round(($attendedCount / $totalAttendances) * 100) : 0;
                
                $heatmapData[] = [
                    'x' => $timeIndex,
                    'y' => $dayIndex,
                    'value' => $rate
                ];
            }
        }
        
        return [
            'days' => $days,
            'times' => $times,
            'data' => $heatmapData
        ];
    }
    
    /**
     * Получение данных для сравнения периодов
     */
    private function getPeriodComparison($startDate, $endDate, $eventTypes, $eventGroups)
    {
        // Определение текущего периода
        $currentPeriodEnd = $endDate ? Carbon::parse($endDate) : Carbon::now();
        $currentPeriodStart = $startDate ? Carbon::parse($startDate) : $currentPeriodEnd->copy()->subMonths(1);
        
        // Определение предыдущего периода (такой же длительности)
        $periodDuration = $currentPeriodStart->diffInDays($currentPeriodEnd);
        $previousPeriodEnd = $currentPeriodStart->copy()->subDays(1);
        $previousPeriodStart = $previousPeriodEnd->copy()->subDays($periodDuration);
        
        // Получение данных о посещаемости для текущего периода
        $currentPeriodData = $this->getPeriodAttendanceData(
            $currentPeriodStart->format('Y-m-d'),
            $currentPeriodEnd->format('Y-m-d'),
            $eventTypes,
            $eventGroups
        );
        
        // Получение данных о посещаемости для предыдущего периода
        $previousPeriodData = $this->getPeriodAttendanceData(
            $previousPeriodStart->format('Y-m-d'),
            $previousPeriodEnd->format('Y-m-d'),
            $eventTypes,
            $eventGroups
        );
        
        // Вычисление изменения в процентах
        $currentRate = $currentPeriodData['rate'];
        $previousRate = $previousPeriodData['rate'];
        $change = $previousRate > 0 ? round($currentRate - $previousRate) : 0;
        
        return [
            'current_period' => [
                'start_date' => $currentPeriodStart->format('d.m.Y'),
                'end_date' => $currentPeriodEnd->format('d.m.Y'),
                'events_count' => $currentPeriodData['events_count'],
                'attended' => $currentPeriodData['attended'],
                'total' => $currentPeriodData['total'],
                'rate' => $currentPeriodData['rate']
            ],
            'previous_period' => [
                'start_date' => $previousPeriodStart->format('d.m.Y'),
                'end_date' => $previousPeriodEnd->format('d.m.Y'),
                'events_count' => $previousPeriodData['events_count'],
                'attended' => $previousPeriodData['attended'],
                'total' => $previousPeriodData['total'],
                'rate' => $previousPeriodData['rate']
            ],
            'change' => $change
        ];
    }
    
    /**
     * Получение данных о посещаемости за указанный период
     */
    private function getPeriodAttendanceData($startDate, $endDate, $eventTypes, $eventGroups)
    {
        // Получение мероприятий за указанный период
        $eventsQuery = Event::whereDate('date_time', '>=', $startDate)
                           ->whereDate('date_time', '<=', $endDate);
        
        if (!empty($eventTypes)) {
            $eventsQuery->whereIn('type', $eventTypes);
        }
        
        if (!empty($eventGroups)) {
            $eventsQuery->whereHas('eventGroups', function($query) use ($eventGroups) {
                $query->whereIn('group', $eventGroups);
            });
        }
        
        $events = $eventsQuery->with(['attendances', 'attendances.user'])->get();
        
        // Подсчет статистики
        $eventsCount = $events->count();
        $attended = 0;
        $total = 0;
        
        foreach ($events as $event) {
            // Фильтруем посещения по группам, если указаны
            $eventAttendances = $event->attendances;
            if (!empty($eventGroups)) {
                $eventAttendances = $eventAttendances->filter(function($attendance) use ($eventGroups) {
                    return $attendance->user && in_array($attendance->user->group, $eventGroups);
                });
            }
            
            $eventAttended = $eventAttendances->where('attended', 1)->count();
            $eventTotal = $eventAttendances->count();
            
            $attended += $eventAttended;
            $total += $eventTotal;
        }
        
        // Вычисление процента посещаемости
        $rate = $total > 0 ? round(($attended / $total) * 100) : 0;
        
        return [
            'events_count' => $eventsCount,
            'attended' => $attended,
            'total' => $total,
            'rate' => $rate
        ];
    }
    
    /**
     * Экспорт отчета в PDF
     */
    public function exportPdf(Request $request)
    {
        // Получение параметров фильтрации
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $eventTypes = $request->input('event_types', []);
        $eventGroups = $request->input('event_groups', []);
        
        // Базовый запрос для получения мероприятий
        $eventsQuery = Event::with(['eventGroups', 'attendances']);
        
        // Применение фильтров
        if ($startDate) {
            $eventsQuery->whereDate('date_time', '>=', $startDate);
        }
        
        if ($endDate) {
            $eventsQuery->whereDate('date_time', '<=', $endDate);
        }
        
        if (!empty($eventTypes)) {
            $eventsQuery->whereIn('type', $eventTypes);
        }
        
        if (!empty($eventGroups)) {
            $eventsQuery->whereHas('eventGroups', function($query) use ($eventGroups) {
                $query->whereIn('group', $eventGroups);
            });
        }
        
        // Получение мероприятий
        $events = $eventsQuery->orderBy('date_time', 'desc')->get();
        
        // Получение данных для отчета
        $eventsByType = $this->getEventsByType($events);
        $attendanceByEvent = $this->getAttendanceByEvent($events);
        $attendanceByMonth = $this->getAttendanceByMonth($events);
        $attendanceRateByGroup = $this->getAttendanceRateByGroup($events);
        $attendanceTrend = $this->getAttendanceTrend($startDate, $endDate, $eventTypes, $eventGroups);
        $periodComparison = $this->getPeriodComparison($startDate, $endDate, $eventTypes, $eventGroups);
        
        // Формирование заголовка отчета
        $title = 'Отчет о посещаемости мероприятий';
        if ($startDate && $endDate) {
            $title .= ' за период с ' . Carbon::parse($startDate)->format('d.m.Y') . ' по ' . Carbon::parse($endDate)->format('d.m.Y');
        } elseif ($startDate) {
            $title .= ' с ' . Carbon::parse($startDate)->format('d.m.Y');
        } elseif ($endDate) {
            $title .= ' по ' . Carbon::parse($endDate)->format('d.m.Y');
        }
        
        if (!empty($eventTypes)) {
            $title .= ' (типы: ' . implode(', ', $eventTypes) . ')';
        }
        
        if (!empty($eventGroups)) {
            $title .= ' (группы: ' . implode(', ', $eventGroups) . ')';
        }
        
        // Генерация PDF
        $pdf = PDF::loadView('reports.pdf', compact(
            'title',
            'events',
            'eventsByType',
            'attendanceByEvent',
            'attendanceByMonth',
            'attendanceRateByGroup',
            'attendanceTrend',
            'periodComparison'
        ));
        
        return $pdf->download('attendance_report.pdf');
    }
    
    /**
     * Экспорт отчета в Excel
     */
    public function exportExcel(Request $request)
    {
        // Получение параметров фильтрации
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $eventTypes = $request->input('event_types', []);
        $eventGroups = $request->input('event_groups', []);
        
        return Excel::download(new EventsExport($startDate, $endDate, $eventTypes, $eventGroups), 'attendance_report.xlsx');
    }
    
    /**
     * Экспорт отчета в Word
     */
    public function exportWord(Request $request)
    {
        // Получение параметров фильтрации
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $eventTypes = $request->input('event_types', []);
        $eventGroups = $request->input('event_groups', []);
        
        // Базовый запрос для получения мероприятий
        $eventsQuery = Event::with(['eventGroups', 'attendances']);
        
        // Применение фильтров
        if ($startDate) {
            $eventsQuery->whereDate('date_time', '>=', $startDate);
        }
        
        if ($endDate) {
            $eventsQuery->whereDate('date_time', '<=', $endDate);
        }
        
        if (!empty($eventTypes)) {
            $eventsQuery->whereIn('type', $eventTypes);
        }
        
        if (!empty($eventGroups)) {
            $eventsQuery->whereHas('eventGroups', function($query) use ($eventGroups) {
                $query->whereIn('group', $eventGroups);
            });
        }
        
        // Получение мероприятий
        $events = $eventsQuery->orderBy('date_time', 'desc')->get();
        
        // Получение данных для отчета
        $eventsByType = $this->getEventsByType($events);
        $attendanceByEvent = $this->getAttendanceByEvent($events);
        $attendanceRateByGroup = $this->getAttendanceRateByGroup($events);
        $periodComparison = $this->getPeriodComparison($startDate, $endDate, $eventTypes, $eventGroups);
        
        // Формирование заголовка отчета
        $title = 'Отчет о посещаемости мероприятий';
        if ($startDate && $endDate) {
            $title .= ' за период с ' . Carbon::parse($startDate)->format('d.m.Y') . ' по ' . Carbon::parse($endDate)->format('d.m.Y');
        } elseif ($startDate) {
            $title .= ' с ' . Carbon::parse($startDate)->format('d.m.Y');
        } elseif ($endDate) {
            $title .= ' по ' . Carbon::parse($endDate)->format('d.m.Y');
        }
        
        if (!empty($eventTypes)) {
            $title .= ' (типы: ' . implode(', ', $eventTypes) . ')';
        }
        
        if (!empty($eventGroups)) {
            $title .= ' (группы: ' . implode(', ', $eventGroups) . ')';
        }
        
        // Создание документа Word
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);
        
        // Стили
        $headingStyle = [
            'bold' => true,
            'size' => 14,
        ];
        
        // Создание секции
        $section = $phpWord->addSection();
        
        // Заголовок отчета
        $section->addText($title, ['bold' => true, 'size' => 16], ['alignment' => 'center']);
        $section->addTextBreak();
        
        // Общая статистика
        $section->addText('Общая статистика', $headingStyle);
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => '000000', 'width' => 100 * 50]);
        $table->addRow();
        $table->addCell(2000)->addText('Всего мероприятий', ['bold' => true]);
        $table->addCell(2000)->addText(count($events));
        $table->addRow();
        $table->addCell(2000)->addText('Текущий период', ['bold' => true]);
        $table->addCell(2000)->addText($periodComparison['current_period']['rate'] . '% посещаемость');
        $table->addRow();
        $table->addCell(2000)->addText('Предыдущий период', ['bold' => true]);
        $table->addCell(2000)->addText($periodComparison['previous_period']['rate'] . '% посещаемость');
        $table->addRow();
        $table->addCell(2000)->addText('Изменение', ['bold' => true]);
        $table->addCell(2000)->addText(($periodComparison['change'] >= 0 ? '+' : '') . $periodComparison['change'] . '%');
        
        $section->addTextBreak();
        
        // Мероприятия по типам
        $section->addText('Мероприятия по типам', $headingStyle);
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => '000000', 'width' => 100 * 50]);
        $table->addRow();
        $table->addCell(4000)->addText('Тип мероприятия', ['bold' => true]);
        $table->addCell(2000)->addText('Количество', ['bold' => true]);
        
        foreach ($eventsByType as $item) {
            $table->addRow();
            $table->addCell(4000)->addText($item['name']);
            $table->addCell(2000)->addText($item['total']);
        }
        
        $section->addTextBreak();
        
        // Посещаемость по мероприятиям
        $section->addText('Посещаемость по мероприятиям', $headingStyle);
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => '000000', 'width' => 100 * 50]);
        $table->addRow();
        $table->addCell(4000)->addText('Название', ['bold' => true]);
        $table->addCell(1500)->addText('Посетили', ['bold' => true]);
        $table->addCell(1500)->addText('Пропустили', ['bold' => true]);
        $table->addCell(1500)->addText('Процент', ['bold' => true]);
        
        foreach ($attendanceByEvent as $item) {
            $table->addRow();
            $table->addCell(4000)->addText($item['title']);
            $table->addCell(1500)->addText($item['attended']);
            $table->addCell(1500)->addText($item['not_attended']);
            $table->addCell(1500)->addText($item['rate'] . '%');
        }
        
        $section->addTextBreak();
        
        // Посещаемость по группам
        $section->addText('Посещаемость по группам', $headingStyle);
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => '000000', 'width' => 100 * 50]);
        $table->addRow();
        $table->addCell(2000)->addText('Группа', ['bold' => true]);
        $table->addCell(1500)->addText('Посетили', ['bold' => true]);
        $table->addCell(1500)->addText('Всего', ['bold' => true]);
        $table->addCell(1500)->addText('Процент', ['bold' => true]);
        
        foreach ($attendanceRateByGroup as $item) {
            $table->addRow();
            $table->addCell(2000)->addText($item['name']);
            $table->addCell(1500)->addText($item['attended']);
            $table->addCell(1500)->addText($item['total']);
            $table->addCell(1500)->addText($item['rate'] . '%');
        }
        
        $section->addTextBreak();
        
        // Список мероприятий
        $section->addText('Список мероприятий', $headingStyle);
        $table = $section->addTable(['borderSize' => 1, 'borderColor' => '000000', 'width' => 100 * 50]);
        $table->addRow();
        $table->addCell(4000)->addText('Название', ['bold' => true]);
        $table->addCell(2000)->addText('Дата', ['bold' => true]);
        $table->addCell(2000)->addText('Тип', ['bold' => true]);
        $table->addCell(2000)->addText('Посещаемость', ['bold' => true]);
        
        foreach ($events as $event) {
            $attendedCount = $event->attendances->where('attended', 1)->count();
            $totalCount = $event->attendances->count();
            $percentage = $totalCount > 0 ? round(($attendedCount / $totalCount) * 100) : 0;
            
            $table->addRow();
            $table->addCell(4000)->addText($event->title);
            $table->addCell(2000)->addText(Carbon::parse($event->date_time)->format('d.m.Y'));
            $table->addCell(2000)->addText($event->type ?? 'Не указан');
            $table->addCell(2000)->addText($percentage . '% (' . $attendedCount . '/' . $totalCount . ')');
        }
        
        // Сохранение документа
        $filename = 'attendance_report.docx';
        $tempFile = storage_path('app/' . $filename);
        $phpWord->save($tempFile);
        
        return response()->download($tempFile)->deleteFileAfterSend(true);
    }
}
