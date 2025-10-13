@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-center">Аналитика посещаемости учащихся</h1>
    
    <!-- Навигация по разделам аналитики -->
    <div class="mb-8 flex flex-wrap justify-center gap-4">
        <a href="{{ route('reports.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            Общая аналитика
        </a>
        <a href="{{ route('reports.students') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
            Аналитика по учащимся
        </a>
        <a href="{{ route('reports.students.ranking') }}" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
            Рейтинг посещаемости
        </a>
    </div>
    
    <!-- Фильтры -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Выбор учащегося и фильтры</h2>
        <form id="filters-form" action="{{ route('reports.students') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- В форме фильтров -->
<div class="lg:col-span-3">
    <label for="student_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Учащийся</label>
    <select id="student_id" name="student_id" 
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
        <option value="">Выберите учащегося</option>
        @foreach($students as $student)
            <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                {{ $student->name }}{{ empty(request('group')) && !empty($student->group) ? ' (' . $student->group . ')' : '' }}
            </option>
        @endforeach
    </select>
</div>
            
            <div>
                <label for="group" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Группа (для фильтрации списка)</label>
                <select id="group" name="group" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Все группы</option>
                    @foreach($eventGroups as $groupName)
                        <option value="{{ $groupName }}" {{ request('group') == $groupName ? 'selected' : '' }}>
                            {{ $groupName }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата начала</label>
                <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            
            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата окончания</label>
                <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            
            <div>
                <label for="event_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип мероприятия</label>
                <select id="event_type" name="event_type" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Все типы</option>
                    @foreach($eventTypes as $type)
                        <option value="{{ $type }}" {{ request('event_type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="lg:col-span-3 flex justify-center">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Применить фильтры
                </button>
                <a href="{{ route('reports.students') }}" class="ml-4 px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Сбросить
                </a>
            </div>
        </form>
    </div>
    
    @if(!$selectedStudent)
        <!-- Инструкция при отсутствии выбранного студента -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8 text-center">
            <div class="flex flex-col items-center justify-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <h2 class="text-xl font-semibold mb-2">Выберите учащегося для просмотра аналитики</h2>
                <p class="text-gray-600 dark:text-gray-400 max-w-md">
                    Используйте фильтры выше, чтобы выбрать учащегося и период для анализа посещаемости.
                </p>
            </div>
        </div>
    @else
        <!-- Экспорт отчетов -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Экспорт отчета по учащемуся</h2>
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="{{ route('reports.student.export_pdf', ['id' => $selectedStudent->id]) }}?{{ http_build_query(request()->except(['page'])) }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                    </svg>
                    Экспорт в PDF
                </a>
                <a href="{{ route('reports.student.export_excel', ['id' => $selectedStudent->id]) }}?{{ http_build_query(request()->except(['page'])) }}" 
                   class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm2 1v1h10V6H5zm10 3H5v7h10V9z" clip-rule="evenodd" />
                    </svg>
                    Экспорт в Excel
                </a>
                <a href="{{ route('reports.student.export_word', ['id' => $selectedStudent->id]) }}?{{ http_build_query(request()->except(['page'])) }}" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 2v10h8V6H6z" clip-rule="evenodd" />
                    </svg>
                    Экспорт в Word
                </a>
            </div>
        </div>
        
        <!-- Информация о студенте и общая статистика -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $selectedStudent->name }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">Группа: {{ $selectedStudent->group ?? 'Не указана' }}</p>
                </div>
                
                @if(isset($studentAttendanceData) && is_array($studentAttendanceData) && isset($studentAttendanceData['attendance_rate']))
                <div class="mt-4 md:mt-0 text-center">
                    <div class="text-3xl font-bold {{ $studentAttendanceData['attendance_rate'] >= 70 ? 'text-green-600 dark:text-green-400' : ($studentAttendanceData['attendance_rate'] >= 50 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400') }}">
                        {{ $studentAttendanceData['attendance_rate'] }}%
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Общая посещаемость</div>
                </div>
                @endif
            </div>
            
            @if(isset($studentAttendanceData) && is_array($studentAttendanceData))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-300">{{ $studentAttendanceData['total_events'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Всего мероприятий</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-300">{{ $studentAttendanceData['attended_events'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Посещено</div>
                </div>
                <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg text-center">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-300">{{ $studentAttendanceData['missed_events'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Пропущено</div>
                </div>
            </div>
            
            <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                <div class="bg-blue-600 h-4 rounded-full" style="width: {{ $studentAttendanceData['attendance_rate'] ?? 0 }}%"></div>
            </div>
            @else
            <div class="text-center py-4">
                <p class="text-gray-600 dark:text-gray-400">Нет данных о посещаемости для выбранного периода</p>
            </div>
            @endif
        </div>
        
        <!-- Сравнение с группой -->
        @if(isset($studentComparisonWithGroup) && is_array($studentComparisonWithGroup) && !empty($studentComparisonWithGroup))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Сравнение с группой</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-lg font-medium mb-2">Рейтинг в группе</h3>
                    <div class="flex items-center">
                        <div class="text-3xl font-bold {{ isset($studentComparisonWithGroup['student_rank']) && isset($studentComparisonWithGroup['total_students']) ? ($studentComparisonWithGroup['student_rank'] <= 3 ? 'text-green-600 dark:text-green-400' : ($studentComparisonWithGroup['student_rank'] <= $studentComparisonWithGroup['total_students'] / 2 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400')) : 'text-gray-600' }}">
                            {{ $studentComparisonWithGroup['student_rank'] ?? '-' }}
                        </div>
                        <div class="text-gray-600 dark:text-gray-400 ml-2">из {{ $studentComparisonWithGroup['total_students'] ?? 0 }}</div>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                        @if(isset($studentComparisonWithGroup['student_rank']) && isset($studentComparisonWithGroup['total_students']))
                            @if($studentComparisonWithGroup['student_rank'] <= 3)
                                Отличный результат! Вы в числе лидеров группы по посещаемости.
                            @elseif($studentComparisonWithGroup['student_rank'] <= $studentComparisonWithGroup['total_students'] / 2)
                                Хороший результат. Вы в верхней половине рейтинга группы.
                            @else
                                Есть куда стремиться. Ваш показатель ниже среднего по группе.
                            @endif
                        @else
                            Недостаточно данных для определения рейтинга.
                        @endif
                    </p>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium mb-2">Сравнение посещаемости</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ваша посещаемость</span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $studentComparisonWithGroup['student_rate'] ?? 0 }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $studentComparisonWithGroup['student_rate'] ?? 0 }}%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Средняя по группе</span>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $studentComparisonWithGroup['group_average_rate'] ?? 0 }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                <div class="bg-gray-600 h-2.5 rounded-full" style="width: {{ $studentComparisonWithGroup['group_average_rate'] ?? 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                    
                    @if(isset($studentComparisonWithGroup['difference']))
                    <div class="mt-4 text-center">
                        <span class="text-sm font-medium {{ $studentComparisonWithGroup['difference'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $studentComparisonWithGroup['difference'] >= 0 ? '+' : '' }}{{ $studentComparisonWithGroup['difference'] }}%
                            {{ $studentComparisonWithGroup['difference'] >= 0 ? 'выше' : 'ниже' }} среднего
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
        
        <!-- Графики и диаграммы -->
        @if(isset($studentAttendanceByType) && is_array($studentAttendanceByType) && !empty($studentAttendanceByType) && isset($studentAttendanceByMonth) && is_array($studentAttendanceByMonth) && !empty($studentAttendanceByMonth))
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Посещаемость по типам мероприятий -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Посещаемость по типам мероприятий</h2>
                <div class="h-80">
                    <canvas id="attendanceByEventTypeChart"></canvas>
                </div>
            </div>
            
            <!-- Динамика посещаемости по месяцам -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Динамика посещаемости по месяцам</h2>
                <div class="h-80">
                    <canvas id="attendanceByMonthChart"></canvas>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Последние посещения -->
        @if(isset($studentAttendanceData['recent_attendances']) && !empty($studentAttendanceData['recent_attendances']))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Последние посещения</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Дата</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Мероприятие</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Тип</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Статус</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($studentAttendanceData['recent_attendances'] as $attendance)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($attendance->event->date_time)->format('d.m.Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $attendance->event->title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $attendance->event->type ?? 'Не указан' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($attendance->attended)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                    Посещено
                                </span>
                                @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                    Пропущено
                                </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    @endif
</div>

@if(isset($studentAttendanceByType) && is_array($studentAttendanceByType) && !empty($studentAttendanceByType) && isset($studentAttendanceByMonth) && is_array($studentAttendanceByMonth) && !empty($studentAttendanceByMonth))
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Данные для графика по типам мероприятий
        const typeData = @json($studentAttendanceByType);
        const typeLabels = typeData.map(item => item.name);
        const typeAttended = typeData.map(item => item.attended);
        const typeMissed = typeData.map(item => item.missed);
        
        // График посещаемости по типам мероприятий
        const typeCtx = document.getElementById('attendanceByEventTypeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'bar',
            data: {
                labels: typeLabels,
                datasets: [
                    {
                        label: 'Посещено',
                        data: typeAttended,
                        backgroundColor: 'rgba(34, 197, 94, 0.6)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Пропущено',
                        data: typeMissed,
                        backgroundColor: 'rgba(239, 68, 68, 0.6)',
                        borderColor: 'rgba(239, 68, 68, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                }
            }
        });
        
        // Данные для графика по месяцам
        const monthData = @json($studentAttendanceByMonth);
        const monthLabels = monthData.map(item => item.month_year);
        const monthRates = monthData.map(item => item.rate);
        
        // График динамики посещаемости по месяцам
        const monthCtx = document.getElementById('attendanceByMonthChart').getContext('2d');
        new Chart(monthCtx, {
            type: 'line',
            data: {
                labels: monthLabels,
                datasets: [{
                    label: 'Посещаемость (%)',
                    data: monthRates,
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                }
            }
        });
    });
</script>
@endpush

@endif
@endsection
