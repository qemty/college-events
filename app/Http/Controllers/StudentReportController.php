<?php

namespace App\Http\Controllers;
use Illuminate\Support\Arr; // Добавьте этот импорт в начало файла
use App\Models\User;
use App\Models\Event;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentAttendanceExport;
use PhpOffice\PhpWord\PhpWord;

class StudentReportController extends Controller
{
    /**
     * Отображение страницы аналитики по студентам
     */
    public function index(Request $request)
    {
        // Получение параметров фильтрации
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $eventTypes = $request->input('event_types', []);
        $eventGroups = $request->input('event_groups', []);
        $studentId = $request->input('student_id');
        $group = $request->input('group');
        
        // Получение списка студентов для выбора с фильтрацией по группе
        $studentsQuery = User::where('role', 'student');
        
        // Если выбрана группа, фильтруем студентов только из этой группы
        if ($group) {
            $studentsQuery->where('group', $group);
        }
        
        $students = $studentsQuery->orderBy('name')->get();
        
        // Получение списка типов мероприятий для фильтра
        $eventTypes = Event::distinct()->pluck('type')->filter()->values();
        
        // Получение списка групп для фильтра
        $eventGroups = DB::table('event_groups')->distinct()->pluck('group')->filter()->values();
        
        // Если выбран конкретный студент, получаем его данные
        $selectedStudent = null;
        $studentAttendanceData = null;
        $studentAttendanceByType = null;
        $studentAttendanceByMonth = null;
        $studentComparisonWithGroup = null;
        
        if ($studentId) {
            $selectedStudent = User::findOrFail($studentId);
            $studentAttendanceData = $this->getStudentAttendanceData($selectedStudent, $startDate, $endDate, $eventTypes, $eventGroups);
            $studentAttendanceByType = $this->getStudentAttendanceByType($selectedStudent, $startDate, $endDate, $eventTypes, $eventGroups);
            $studentAttendanceByMonth = $this->getStudentAttendanceByMonth($selectedStudent, $startDate, $endDate, $eventTypes, $eventGroups);
            $studentComparisonWithGroup = $this->getStudentComparisonWithGroup($selectedStudent, $startDate, $endDate, $eventTypes, $eventGroups);
        }
        
        return view('reports.students', compact(
            'students',
            'eventTypes',
            'eventGroups',
            'selectedStudent',
            'studentAttendanceData',
            'studentAttendanceByType',
            'studentAttendanceByMonth',
            'studentComparisonWithGroup'
        ));
    }
    
    /**
     * Отображение страницы рейтинга студентов по посещаемости
     */
    public function ranking(Request $request)
    {
        // Получение параметров фильтрации
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $eventTypes = $request->input('event_types', []);
        $eventGroups = $request->input('event_groups', []);
        $group = $request->input('group');
        
        // Получение списка типов мероприятий для фильтра
        $eventTypes = Event::distinct()->pluck('type')->filter()->values();
        
        // Получение списка групп для фильтра
        $groups = User::where('role', 'student')->distinct()->pluck('group')->filter()->values();
        
        // Получение рейтинга студентов
        $studentsRanking = $this->getStudentsRanking($startDate, $endDate, $eventTypes, $eventGroups, $group);
        
        // Получение статистики по группам
        $groupsStatistics = $this->getGroupsStatistics($startDate, $endDate, $eventTypes, $eventGroups);
        
        return view('reports.ranking', compact(
            'studentsRanking',
            'groupsStatistics',
            'eventTypes',
            'groups'
        ));
    }
    
    /**
     * Получение данных о посещаемости студента
     */
    private function getStudentAttendanceData($student, $startDate, $endDate, $eventTypes, $eventGroups)
{
    // Нормализация входных параметров
    $eventTypes = !empty($eventTypes) ? Arr::flatten((array)$eventTypes) : [];
    $eventGroups = !empty($eventGroups) ? Arr::flatten((array)$eventGroups) : [];

    // Базовый запрос для получения посещений студента
    $attendancesQuery = Attendance::where('user_id', $student->id)
        ->whereHas('event', function ($query) use ($startDate, $endDate, $eventTypes, $eventGroups) {
            if ($startDate) {
                $query->whereDate('date_time', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('date_time', '<=', $endDate);
            }
            if (!empty($eventTypes)) {
                $query->whereIn('type', $eventTypes);
            }
            if (!empty($eventGroups)) {
                $query->whereHas('eventGroups', function ($q) use ($eventGroups) {
                    $q->whereIn('group', $eventGroups);
                });
            }
        })
        ->with(['event' => function ($query) {
            $query->select('id', 'title', 'type', 'date_time');
        }]);

    // Получение посещений
    $attendances = $attendancesQuery->get();

    // Логирование для диагностики
    \Log::info('Student Attendance Query', [
        'student_id' => $student->id,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'event_types' => $eventTypes,
        'event_groups' => $eventGroups,
        'attendance_count' => $attendances->count(),
    ]);

    // Подсчёт статистики
    $totalEvents = $attendances->count();
    $attendedEvents = $attendances->where('attended', true)->count();
    $missedEvents = $totalEvents - $attendedEvents;
    $attendanceRate = $totalEvents > 0 ? round(($attendedEvents / $totalEvents) * 100) : 0;

    // Получение последних посещений
    $recentAttendances = $attendances->filter(function ($attendance) {
        return !is_null($attendance->event) && !is_null($attendance->event->date_time);
    })->sortByDesc(function ($attendance) {
        return $attendance->event->date_time;
    })->take(5)->values();
    
    // Логирование для диагностики последних посещений
    \Log::info('Recent Attendances', [
        'student_id' => $student->id,
        'recent_count' => $recentAttendances->count(),
        'recent_dates' => $recentAttendances->map(function($att) {
            return $att->event->date_time;
        })
    ]);

    return [
        'total_events' => $totalEvents,
        'attended_events' => $attendedEvents,
        'missed_events' => $missedEvents,
        'attendance_rate' => $attendanceRate,
        'recent_attendances' => $recentAttendances,
    ];
}
    
    /**
     * Получение данных о посещаемости студента по типам мероприятий
     */
    private function getStudentAttendanceByType($student, $startDate, $endDate, $eventTypes, $eventGroups)
    {
        // Базовый запрос для получения посещений студента
        $attendancesQuery = Attendance::where('user_id', $student->id)
                                    ->whereHas('event', function($query) use ($startDate, $endDate, $eventTypes, $eventGroups) {
                                        if ($startDate) {
                                            $query->whereDate('date_time', '>=', $startDate);
                                        }
                                        
                                        if ($endDate) {
                                            $query->whereDate('date_time', '<=', $endDate);
                                        }
                                        
                                        if (!empty($eventTypes)) {
                                            $query->whereIn('type', $eventTypes);
                                        }
                                        
                                        if (!empty($eventGroups)) {
                                            $query->whereHas('eventGroups', function($q) use ($eventGroups) {
                                                $q->whereIn('group', $eventGroups);
                                            });
                                        }
                                    })
                                    ->with('event');
        
        // Получение посещений
        $attendances = $attendancesQuery->get();
        
        // Группировка по типам мероприятий
        $attendanceByType = [];
        
        foreach ($attendances as $attendance) {
            $type = $attendance->event->type ?? 'Не указан';
            
            if (!isset($attendanceByType[$type])) {
                $attendanceByType[$type] = [
                    'name' => $type,
                    'attended' => 0,
                    'missed' => 0,
                    'total' => 0,
                    'rate' => 0
                ];
            }
            
            $attendanceByType[$type]['total']++;
            
            if ($attendance->attended) {
                $attendanceByType[$type]['attended']++;
            } else {
                $attendanceByType[$type]['missed']++;
            }
        }
        
        // Вычисление процента посещаемости
        foreach ($attendanceByType as &$type) {
            $type['rate'] = $type['total'] > 0 ? round(($type['attended'] / $type['total']) * 100) : 0;
        }
        
        return array_values($attendanceByType);
    }
    
    /**
     * Получение данных о посещаемости студента по месяцам
     */
    private function getStudentAttendanceByMonth($student, $startDate, $endDate, $eventTypes, $eventGroups)
    {
        // Базовый запрос для получения посещений студента
        $attendancesQuery = Attendance::where('user_id', $student->id)
                                    ->whereHas('event', function($query) use ($startDate, $endDate, $eventTypes, $eventGroups) {
                                        if ($startDate) {
                                            $query->whereDate('date_time', '>=', $startDate);
                                        }
                                        
                                        if ($endDate) {
                                            $query->whereDate('date_time', '<=', $endDate);
                                        }
                                        
                                        if (!empty($eventTypes)) {
                                            $query->whereIn('type', $eventTypes);
                                        }
                                        
                                        if (!empty($eventGroups)) {
                                            $query->whereHas('eventGroups', function($q) use ($eventGroups) {
                                                $q->whereIn('group', $eventGroups);
                                            });
                                        }
                                    })
                                    ->with('event');
        
        // Получение посещений
        $attendances = $attendancesQuery->get();
        
        // Группировка по месяцам
        $attendanceByMonth = [];
        
        foreach ($attendances as $attendance) {
            $monthYear = Carbon::parse($attendance->event->date_time)->format('Y-m');
            $monthName = Carbon::parse($attendance->event->date_time)->format('M Y');
            
            if (!isset($attendanceByMonth[$monthYear])) {
                $attendanceByMonth[$monthYear] = [
                    'month_year' => $monthName,
                    'attended' => 0,
                    'missed' => 0,
                    'total' => 0,
                    'rate' => 0
                ];
            }
            
            $attendanceByMonth[$monthYear]['total']++;
            
            if ($attendance->attended) {
                $attendanceByMonth[$monthYear]['attended']++;
            } else {
                $attendanceByMonth[$monthYear]['missed']++;
            }
        }
        
        // Вычисление процента посещаемости
        foreach ($attendanceByMonth as &$month) {
            $month['rate'] = $month['total'] > 0 ? round(($month['attended'] / $month['total']) * 100) : 0;
        }
        
        // Сортировка по месяцам
        ksort($attendanceByMonth);
        
        return array_values($attendanceByMonth);
    }
    
    /**
     * Получение данных для сравнения посещаемости студента с группой
     */
    private function getStudentComparisonWithGroup($student, $startDate, $endDate, $eventTypes, $eventGroups)
    {
        // Получение данных о посещаемости студента
        $studentData = $this->getStudentAttendanceData($student, $startDate, $endDate, $eventTypes, $eventGroups);
        
        // Получение данных о посещаемости группы
        $groupAttendances = Attendance::whereHas('user', function($query) use ($student) {
                                    $query->where('group', $student->group);
                                })
                                ->whereHas('event', function($query) use ($startDate, $endDate, $eventTypes, $eventGroups) {
                                    if ($startDate) {
                                        $query->whereDate('date_time', '>=', $startDate);
                                    }
                                    
                                    if ($endDate) {
                                        $query->whereDate('date_time', '<=', $endDate);
                                    }
                                    
                                    if (!empty($eventTypes)) {
                                        $query->whereIn('type', $eventTypes);
                                    }
                                    
                                    if (!empty($eventGroups)) {
                                        $query->whereHas('eventGroups', function($q) use ($eventGroups) {
                                            $q->whereIn('group', $eventGroups);
                                        });
                                    }
                                })
                                ->with(['user', 'event'])
                                ->get();
        
        // Подсчет статистики по группе
        $groupTotalEvents = $groupAttendances->count();
        $groupAttendedEvents = $groupAttendances->where('attended', 1)->count();
        $groupAttendanceRate = $groupTotalEvents > 0 ? round(($groupAttendedEvents / $groupTotalEvents) * 100) : 0;
        
        // Получение рейтинга студента в группе
        $studentsInGroup = User::where('group', $student->group)
                              ->where('role', 'student')
                              ->get();
        
        $studentsRanking = [];
        
        foreach ($studentsInGroup as $groupStudent) {
            $studentAttendances = $groupAttendances->where('user_id', $groupStudent->id);
            $totalEvents = $studentAttendances->count();
            $attendedEvents = $studentAttendances->where('attended', 1)->count();
            $attendanceRate = $totalEvents > 0 ? round(($attendedEvents / $totalEvents) * 100) : 0;
            
            $studentsRanking[] = [
                'id' => $groupStudent->id,
                'name' => $groupStudent->name,
                'total_events' => $totalEvents,
                'attended_events' => $attendedEvents,
                'attendance_rate' => $attendanceRate
            ];
        }
        
        // Сортировка по проценту посещаемости (по убыванию)
        usort($studentsRanking, function($a, $b) {
            return $b['attendance_rate'] <=> $a['attendance_rate'];
        });
        
        // Определение позиции студента в рейтинге
        $studentRank = 0;
        foreach ($studentsRanking as $index => $rankData) {
            if ($rankData['id'] == $student->id) {
                $studentRank = $index + 1;
                break;
            }
        }
        
        return [
            'student_rate' => $studentData['attendance_rate'],
            'group_average_rate' => $groupAttendanceRate,
            'difference' => $studentData['attendance_rate'] - $groupAttendanceRate,
            'student_rank' => $studentRank,
            'total_students' => count($studentsRanking),
            'top_students' => array_slice($studentsRanking, 0, 3)
        ];
    }
    
    /**
     * Получение рейтинга студентов по посещаемости
     */
    private function getStudentsRanking($startDate, $endDate, $eventTypes, $eventGroups, $group = null)
    {
        // Базовый запрос для получения студентов
        $studentsQuery = User::where('role', 'student');
        
        // Фильтрация по группе
        if ($group) {
            $studentsQuery->where('group', $group);
        }
        
        // Получение студентов
        $students = $studentsQuery->get();
        
        // Получение данных о посещаемости для каждого студента
        $studentsRanking = [];
        
        foreach ($students as $student) {
            // Получение посещений студента
            $attendancesQuery = Attendance::where('user_id', $student->id)
                                        ->whereHas('event', function($query) use ($startDate, $endDate, $eventTypes, $eventGroups) {
                                            if ($startDate) {
                                                $query->whereDate('date_time', '>=', $startDate);
                                            }
                                            
                                            if ($endDate) {
                                                $query->whereDate('date_time', '<=', $endDate);
                                            }
                                            
                                            if (!empty($eventTypes)) {
                                                $query->whereIn('type', $eventTypes);
                                            }
                                            
                                            if (!empty($eventGroups)) {
                                                $query->whereHas('eventGroups', function($q) use ($eventGroups) {
                                                    $q->whereIn('group', $eventGroups);
                                                });
                                            }
                                        });
            
            // Получение посещений
            $attendances = $attendancesQuery->get();
            
            // Подсчет статистики
            $totalEvents = $attendances->count();
            
            // Пропускаем студентов без посещений
            if ($totalEvents == 0) {
                continue;
            }
            
            $attendedEvents = $attendances->where('attended', 1)->count();
            $missedEvents = $totalEvents - $attendedEvents;
            $attendanceRate = round(($attendedEvents / $totalEvents) * 100);
            
            $studentsRanking[] = [
                'id' => $student->id,
                'name' => $student->name,
                'group' => $student->group,
                'attended_events' => $attendedEvents,
                'missed_events' => $missedEvents,
                'total_events' => $totalEvents,
                'attendance_rate' => $attendanceRate
            ];
        }
        
        // Сортировка по проценту посещаемости (по убыванию)
        usort($studentsRanking, function($a, $b) {
            return $b['attendance_rate'] <=> $a['attendance_rate'];
        });
        
        return $studentsRanking;
    }
    
    /**
     * Получение статистики посещаемости по группам
     */
    private function getGroupsStatistics($startDate, $endDate, $eventTypes, $eventGroups)
    {
        // Получение списка групп
        $groups = User::where('role', 'student')
                     ->distinct()
                     ->pluck('group')
                     ->filter()
                     ->values();
        
        // Получение данных о посещаемости для каждой группы
        $groupsStatistics = [];
        
        foreach ($groups as $group) {
            // Получение студентов группы
            $students = User::where('role', 'student')
                           ->where('group', $group)
                           ->get();
            
            $studentsCount = $students->count();
            $attendedEvents = 0;
            $missedEvents = 0;
            
            // Получение посещений для каждого студента группы
            foreach ($students as $student) {
                $attendancesQuery = Attendance::where('user_id', $student->id)
                                            ->whereHas('event', function($query) use ($startDate, $endDate, $eventTypes, $eventGroups) {
                                                if ($startDate) {
                                                    $query->whereDate('date_time', '>=', $startDate);
                                                }
                                                
                                                if ($endDate) {
                                                    $query->whereDate('date_time', '<=', $endDate);
                                                }
                                                
                                                if (!empty($eventTypes)) {
                                                    $query->whereIn('type', $eventTypes);
                                                }
                                                
                                                if (!empty($eventGroups)) {
                                                    $query->whereHas('eventGroups', function($q) use ($eventGroups) {
                                                        $q->whereIn('group', $eventGroups);
                                                    });
                                                }
                                            });
                
                // Получение посещений
                $attendances = $attendancesQuery->get();
                
                // Подсчет статистики
                $attendedEvents += $attendances->where('attended', 1)->count();
                $missedEvents += $attendances->where('attended', 0)->count();
            }
            
            $totalEvents = $attendedEvents + $missedEvents;
            $attendanceRate = $totalEvents > 0 ? round(($attendedEvents / $totalEvents) * 100) : 0;
            
            $groupsStatistics[] = [
                'name' => $group,
                'students_count' => $studentsCount,
                'attended_events' => $attendedEvents,
                'missed_events' => $missedEvents,
                'total_events' => $totalEvents,
                'attendance_rate' => $attendanceRate
            ];
        }
        
        // Сортировка по проценту посещаемости (по убыванию)
        usort($groupsStatistics, function($a, $b) {
            return $b['attendance_rate'] <=> $a['attendance_rate'];
        });
        
        return $groupsStatistics;
    }
    
    /**
     * Экспорт отчета о посещаемости студента в PDF
     */
    public function exportStudentPdf(Request $request, $id)
    {
        // Получение параметров фильтрации
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $eventTypes = $request->input('event_types', []);
        $eventGroups = $request->input('event_groups', []);
        
        // Получение данных о студенте
        $student = User::findOrFail($id);
        
        // Убедимся, что у студента есть имя
        if (empty($student->name)) {
            $student->name = $student->last_name . ' ' . $student->first_name . ' ' . $student->middle_name;
        }
        
        $studentAttendanceData = $this->getStudentAttendanceData($student, $startDate, $endDate, $eventTypes, $eventGroups);
        $studentAttendanceByType = $this->getStudentAttendanceByType($student, $startDate, $endDate, $eventTypes, $eventGroups);
        $studentAttendanceByMonth = $this->getStudentAttendanceByMonth($student, $startDate, $endDate, $eventTypes, $eventGroups);
        $studentComparisonWithGroup = $this->getStudentComparisonWithGroup($student, $startDate, $endDate, $eventTypes, $eventGroups);
        
        // Формирование заголовка отчета
        $title = 'Отчет о посещаемости студента: ' . $student->name;
        if ($startDate && $endDate) {
            $title .= ' за период с ' . Carbon::parse($startDate)->format('d.m.Y') . ' по ' . Carbon::parse($endDate)->format('d.m.Y');
        } elseif ($startDate) {
            $title .= ' с ' . Carbon::parse($startDate)->format('d.m.Y');
        } elseif ($endDate) {
            $title .= ' по ' . Carbon::parse($endDate)->format('d.m.Y');
        }
        
        // Генерация PDF
        $pdf = PDF::loadView('reports.student_pdf', compact(
            'title',
            'student',
            'studentAttendanceData',
            'studentAttendanceByType',
            'studentAttendanceByMonth',
            'studentComparisonWithGroup'
        ));
        
        return $pdf->download('student_attendance_report.pdf');
    }
    
    /**
     * Экспорт отчета о посещаемости студента в Excel
     */
    public function exportStudentExcel(Request $request, $id)
    {
        // Получение параметров фильтрации
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $eventTypes = $request->input('event_types', []);
        $eventGroups = $request->input('event_groups', []);
        
        // Получение данных о студенте
        $student = User::findOrFail($id);
        
        // Убедимся, что у студента есть имя
        if (empty($student->name)) {
            $student->name = $student->last_name . ' ' . $student->first_name . ' ' . $student->middle_name;
        }
        
        // Преобразуем параметры фильтрации, если они пришли как строки
        if (!is_array($eventTypes) && !empty($eventTypes)) {
            $eventTypes = [$eventTypes];
        }
        
        if (!is_array($eventGroups) && !empty($eventGroups)) {
            $eventGroups = [$eventGroups];
        }
        
        return Excel::download(new StudentAttendanceExport($student, $startDate, $endDate, $eventTypes, $eventGroups), 'student_attendance_report.xlsx');
    }
    
    /**
     * Экспорт отчета о посещаемости студента в Word
     */
    public function exportStudentWord(Request $request, $id)
    {
        // Получение параметров фильтрации
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $eventTypes = $request->input('event_types', []);
        $eventGroups = $request->input('event_groups', []);
        
        // Получение данных о студенте
        $student = User::findOrFail($id);
        $studentAttendanceData = $this->getStudentAttendanceData($student, $startDate, $endDate, $eventTypes, $eventGroups);
        $studentAttendanceByType = $this->getStudentAttendanceByType($student, $startDate, $endDate, $eventTypes, $eventGroups);
        $studentAttendanceByMonth = $this->getStudentAttendanceByMonth($student, $startDate, $endDate, $eventTypes, $eventGroups);
        $studentComparisonWithGroup = $this->getStudentComparisonWithGroup($student, $startDate, $endDate, $eventTypes, $eventGroups);
        
        // Создание документа Word
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        
        // Стили
        $titleStyle = ['bold' => true, 'size' => 16, 'name' => 'Arial'];
        $headingStyle = ['bold' => true, 'size' => 14, 'name' => 'Arial'];
        $subheadingStyle = ['bold' => true, 'size' => 12, 'name' => 'Arial'];
        $textStyle = ['size' => 11, 'name' => 'Arial'];
        
        // Заголовок
        $title = 'Отчет о посещаемости студента: ' . $student->name;
        if ($startDate && $endDate) {
            $title .= ' за период с ' . Carbon::parse($startDate)->format('d.m.Y') . ' по ' . Carbon::parse($endDate)->format('d.m.Y');
        } elseif ($startDate) {
            $title .= ' с ' . Carbon::parse($startDate)->format('d.m.Y');
        } elseif ($endDate) {
            $title .= ' по ' . Carbon::parse($endDate)->format('d.m.Y');
        }
        
        $section->addText($title, $titleStyle, ['alignment' => 'center']);
        $section->addTextBreak();
        
        // Информация о студенте
        $section->addText('Информация о студенте', $headingStyle);
        $section->addText('ФИО: ' . $student->name, $textStyle);
        $section->addText('Группа: ' . $student->group, $textStyle);
        $section->addTextBreak();
        
        // Общая статистика
        $section->addText('Общая статистика посещаемости', $headingStyle);
        $section->addText('Всего мероприятий: ' . $studentAttendanceData['total_events'], $textStyle);
        $section->addText('Посещено: ' . $studentAttendanceData['attended_events'], $textStyle);
        $section->addText('Пропущено: ' . $studentAttendanceData['missed_events'], $textStyle);
        $section->addText('Процент посещаемости: ' . $studentAttendanceData['attendance_rate'] . '%', $textStyle);
        $section->addTextBreak();
        
        // Сравнение с группой
        $section->addText('Сравнение с группой', $headingStyle);
        $section->addText('Рейтинг в группе: ' . $studentComparisonWithGroup['student_rank'] . ' из ' . $studentComparisonWithGroup['total_students'], $textStyle);
        $section->addText('Посещаемость студента: ' . $studentComparisonWithGroup['student_rate'] . '%', $textStyle);
        $section->addText('Средняя посещаемость по группе: ' . $studentComparisonWithGroup['group_average_rate'] . '%', $textStyle);
        $section->addText('Разница: ' . ($studentComparisonWithGroup['difference'] >= 0 ? '+' : '') . $studentComparisonWithGroup['difference'] . '%', $textStyle);
        $section->addTextBreak();
        
        // Посещаемость по типам мероприятий
        $section->addText('Посещаемость по типам мероприятий', $headingStyle);
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        $table->addRow();
        $table->addCell(2000)->addText('Тип мероприятия', $subheadingStyle);
        $table->addCell(1000)->addText('Посещено', $subheadingStyle);
        $table->addCell(1000)->addText('Пропущено', $subheadingStyle);
        $table->addCell(1000)->addText('Всего', $subheadingStyle);
        $table->addCell(1500)->addText('Процент', $subheadingStyle);
        
        foreach ($studentAttendanceByType as $type) {
            $table->addRow();
            $table->addCell(2000)->addText($type['name'], $textStyle);
            $table->addCell(1000)->addText($type['attended'], $textStyle);
            $table->addCell(1000)->addText($type['missed'], $textStyle);
            $table->addCell(1000)->addText($type['total'], $textStyle);
            $table->addCell(1500)->addText($type['rate'] . '%', $textStyle);
        }
        
        $section->addTextBreak();
        
        // Посещаемость по месяцам
        $section->addText('Посещаемость по месяцам', $headingStyle);
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80]);
        $table->addRow();
        $table->addCell(2000)->addText('Месяц', $subheadingStyle);
        $table->addCell(1000)->addText('Посещено', $subheadingStyle);
        $table->addCell(1000)->addText('Пропущено', $subheadingStyle);
        $table->addCell(1000)->addText('Всего', $subheadingStyle);
        $table->addCell(1500)->addText('Процент', $subheadingStyle);
        
        foreach ($studentAttendanceByMonth as $month) {
            $table->addRow();
            $table->addCell(2000)->addText($month['month_year'], $textStyle);
            $table->addCell(1000)->addText($month['attended'], $textStyle);
            $table->addCell(1000)->addText($month['missed'], $textStyle);
            $table->addCell(1000)->addText($month['total'], $textStyle);
            $table->addCell(1500)->addText($month['rate'] . '%', $textStyle);
        }
        
        // Сохранение документа
        $filename = 'student_attendance_report.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word');
        $phpWord->save($tempFile, 'Word2007');
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
    
    /**
     * Экспорт рейтинга студентов в PDF
     */
    public function exportRankingPdf(Request $request)
    {
        // Получение параметров фильтрации
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $eventTypes = $request->input('event_types', []);
        $eventGroups = $request->input('event_groups', []);
        $group = $request->input('group');
        
        // Получение рейтинга студентов
        $studentsRanking = $this->getStudentsRanking($startDate, $endDate, $eventTypes, $eventGroups, $group);
        
        // Получение статистики по группам
        $groupsStatistics = $this->getGroupsStatistics($startDate, $endDate, $eventTypes, $eventGroups);
        
        // Формирование заголовка отчета
        $title = 'Рейтинг студентов по посещаемости';
        if ($startDate && $endDate) {
            $period = 'с ' . Carbon::parse($startDate)->format('d.m.Y') . ' по ' . Carbon::parse($endDate)->format('d.m.Y');
            $title .= ' за период ' . $period;
        } elseif ($startDate) {
            $period = 'с ' . Carbon::parse($startDate)->format('d.m.Y');
            $title .= ' ' . $period;
        } elseif ($endDate) {
            $period = 'по ' . Carbon::parse($endDate)->format('d.m.Y');
            $title .= ' ' . $period;
        } else {
            $period = 'весь период';
        }
        
        if ($group) {
            $title .= ' (группа: ' . $group . ')';
        }
        
        // Генерация PDF
        $pdf = PDF::loadView('reports.ranking_pdf', compact(
            'title',
            'studentsRanking',
            'groupsStatistics',
            'period'
        ));
        
        return $pdf->download('students_ranking_report.pdf');
    }
    
    /**
     * Экспорт рейтинга студентов в Excel
     */
    public function exportRankingExcel(Request $request)
    {
        // Получение параметров фильтрации
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $eventTypes = $request->input('event_types', []);
        $eventGroups = $request->input('event_groups', []);
        $group = $request->input('group');
        
        // Получение рейтинга студентов
        $studentsRanking = $this->getStudentsRanking($startDate, $endDate, $eventTypes, $eventGroups, $group);
        
        // Получение статистики по группам
        $groupsStatistics = $this->getGroupsStatistics($startDate, $endDate, $eventTypes, $eventGroups);
        
        // Создание Excel файла
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Первый лист - Рейтинг студентов
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Рейтинг студентов');
        
        // Заголовок
        $title = 'Рейтинг студентов по посещаемости';
        if ($startDate && $endDate) {
            $title .= ' за период с ' . Carbon::parse($startDate)->format('d.m.Y') . ' по ' . Carbon::parse($endDate)->format('d.m.Y');
        } elseif ($startDate) {
            $title .= ' с ' . Carbon::parse($startDate)->format('d.m.Y');
        } elseif ($endDate) {
            $title .= ' по ' . Carbon::parse($endDate)->format('d.m.Y');
        }
        
        if ($group) {
            $title .= ' (группа: ' . $group . ')';
        }
        
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Заголовки таблицы
        $sheet->setCellValue('A3', 'Место');
        $sheet->setCellValue('B3', 'Студент');
        $sheet->setCellValue('C3', 'Группа');
        $sheet->setCellValue('D3', 'Посещено');
        $sheet->setCellValue('E3', 'Пропущено');
        $sheet->setCellValue('F3', 'Процент посещаемости');
        
        $sheet->getStyle('A3:F3')->getFont()->setBold(true);
        $sheet->getStyle('A3:F3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        
        // Данные рейтинга
        $row = 4;
        foreach ($studentsRanking as $index => $student) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $student['name']);
            $sheet->setCellValue('C' . $row, $student['group']);
            $sheet->setCellValue('D' . $row, $student['attended_events']);
            $sheet->setCellValue('E' . $row, $student['missed_events']);
            $sheet->setCellValue('F' . $row, $student['attendance_rate'] . '%');
            
            // Выделение топ-3 студентов
            if ($index < 3) {
                $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFF9C4');
            }
            
            $row++;
        }
        
        // Автоматическая ширина столбцов
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Границы таблицы
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A3:F' . ($row - 1))->applyFromArray($styleArray);
        
        // Второй лист - Статистика по группам
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->getSheet(1);
        $sheet->setTitle('Статистика по группам');
        
        // Заголовок
        $sheet->setCellValue('A1', 'Статистика посещаемости по группам');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        
        // Заголовки таблицы
        $sheet->setCellValue('A3', 'Группа');
        $sheet->setCellValue('B3', 'Кол-во студентов');
        $sheet->setCellValue('C3', 'Посещено');
        $sheet->setCellValue('D3', 'Пропущено');
        $sheet->setCellValue('E3', 'Всего');
        $sheet->setCellValue('F3', 'Процент посещаемости');
        
        $sheet->getStyle('A3:F3')->getFont()->setBold(true);
        $sheet->getStyle('A3:F3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DDDDDD');
        
        // Данные статистики по группам
        $row = 4;
        foreach ($groupsStatistics as $groupStat) {
            $sheet->setCellValue('A' . $row, $groupStat['name']);
            $sheet->setCellValue('B' . $row, $groupStat['students_count']);
            $sheet->setCellValue('C' . $row, $groupStat['attended_events']);
            $sheet->setCellValue('D' . $row, $groupStat['missed_events']);
            $sheet->setCellValue('E' . $row, $groupStat['total_events']);
            $sheet->setCellValue('F' . $row, $groupStat['attendance_rate'] . '%');
            $row++;
        }
        
        // Автоматическая ширина столбцов
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Границы таблицы
        $sheet->getStyle('A3:F' . ($row - 1))->applyFromArray($styleArray);
        
        // Возврат на первый лист
        $spreadsheet->setActiveSheetIndex(0);
        
        // Сохранение файла
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'students_ranking_report.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
