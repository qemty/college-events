@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-center">Аналитика мероприятий</h1>
    
    
    
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

<!-- Экспорт отчетов -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Экспорт отчета</h2>
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="{{ route('reports.export_pdf') }}?{{ http_build_query(request()->all()) }}" 
               class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                </svg>
                Экспорт в PDF
            </a>
            <a href="{{ route('reports.export_excel') }}?{{ http_build_query(request()->all()) }}" 
               class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm2 1v1h10V6H5zm10 3H5v7h10V9z" clip-rule="evenodd" />
                </svg>
                Экспорт в Excel
            </a>
            <a href="{{ route('reports.export_word') }}?{{ http_build_query(request()->all()) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 2v10h8V6H6z" clip-rule="evenodd" />
                </svg>
                Экспорт в Word
            </a>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Фильтры</h2>
        <form id="filters-form" action="{{ route('reports.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
                <label for="event_types" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Типы мероприятий</label>
                <select id="event_types" name="event_types[]" multiple 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @foreach($allEventTypes as $type)
                        <option value="{{ $type }}" {{ in_array($type, request('event_types', [])) ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Удерживайте Ctrl (Cmd на Mac) для выбора нескольких типов</p>
            </div>
            
            <div>
                <label for="event_groups" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Группы</label>
                <select id="event_groups" name="event_groups[]" multiple 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @foreach($allEventGroups as $group)
                        <option value="{{ $group }}" {{ in_array($group, request('event_groups', [])) ? 'selected' : '' }}>
                            {{ $group }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Удерживайте Ctrl (Cmd на Mac) для выбора нескольких групп</p>
            </div>
            
            <div class="md:col-span-2 lg:col-span-4 flex justify-center">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Применить фильтры
                </button>
                <a href="{{ route('reports.index') }}" class="ml-4 px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Сбросить
                </a>
            </div>
        </form>
    </div>
    
    
    <!-- Сравнение периодов -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
        <!-- <h2 class="text-xl font-semibold mb-4">Сравнение периодов</h2> -->
        <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
            <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                <h3 class="text-lg font-medium mb-2 text-blue-800 dark:text-blue-200">Текущий период</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $periodComparison['current_period']['start_date'] }} - {{ $periodComparison['current_period']['end_date'] }}</p>
                <div class="mt-4">
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Посещаемость</span>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $periodComparison['current_period']['rate'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $periodComparison['current_period']['rate'] }}%"></div>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Мероприятий</p>
                        <p class="text-xl font-bold text-blue-800 dark:text-blue-200">{{ $periodComparison['current_period']['events_count'] }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Посещений</p>
                        <p class="text-xl font-bold text-blue-800 dark:text-blue-200">{{ $periodComparison['current_period']['attended'] }}/{{ $periodComparison['current_period']['total'] }}</p>
                    </div>
                </div>
            </div>
            
            <!-- <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h3 class="text-lg font-medium mb-2 text-gray-800 dark:text-gray-200">Предыдущий период</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $periodComparison['previous_period']['start_date'] }} - {{ $periodComparison['previous_period']['end_date'] }}</p>
                <div class="mt-4">
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Посещаемость</span>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $periodComparison['previous_period']['rate'] }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-gray-600 h-2.5 rounded-full" style="width: {{ $periodComparison['previous_period']['rate'] }}%"></div>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Мероприятий</p>
                        <p class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ $periodComparison['previous_period']['events_count'] }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Посещений</p>
                        <p class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ $periodComparison['previous_period']['attended'] }}/{{ $periodComparison['previous_period']['total'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-{{ $periodComparison['change'] >= 0 ? 'green' : 'red' }}-50 dark:bg-{{ $periodComparison['change'] >= 0 ? 'green' : 'red' }}-900 p-4 rounded-lg flex flex-col justify-center">
                <h3 class="text-lg font-medium mb-2 text-{{ $periodComparison['change'] >= 0 ? 'green' : 'red' }}-800 dark:text-{{ $periodComparison['change'] >= 0 ? 'green' : 'red' }}-200">Изменение</h3>
                <div class="text-center">
                    <p class="text-3xl font-bold text-{{ $periodComparison['change'] >= 0 ? 'green' : 'red' }}-600 dark:text-{{ $periodComparison['change'] >= 0 ? 'green' : 'red' }}-400">
                        {{ $periodComparison['change'] >= 0 ? '+' : '' }}{{ $periodComparison['change'] }}%
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                        {{ $periodComparison['change'] >= 0 ? 'Улучшение' : 'Ухудшение' }} посещаемости
                    </p>
                </div>
            </div> -->
        </div>
    </div>
    
    <!-- Графики и диаграммы -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- График мероприятий по типам -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Мероприятия по типам</h2>
            <div class="h-80">
                <canvas id="eventsByTypeChart"></canvas>
            </div>
        </div>
        
        <!-- График посещаемости по мероприятиям -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Посещаемость по мероприятиям</h2>
            <div class="h-80">
                <canvas id="attendanceByEventChart"></canvas>
            </div>
        </div>
        
        <!-- График посещаемости по месяцам -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Посещаемость по месяцам</h2>
            <div class="h-80">
                <canvas id="attendanceByMonthChart"></canvas>
            </div>
        </div>
        
        <!-- Диаграмма процента посещаемости по группам -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Процент посещаемости по группам</h2>
            <div class="h-80">
                <canvas id="attendanceRateByGroupChart"></canvas>
            </div>
        </div>
        
        <!-- График тренда посещаемости -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Тренд посещаемости по неделям</h2>
            <div class="h-80">
                <canvas id="attendanceTrendChart"></canvas>
            </div>
        </div>
        
        <!-- Тепловая карта посещаемости -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Тепловая карта посещаемости</h2>
            <div class="h-80">
                <canvas id="attendanceHeatmapChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Таблица мероприятий -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Список мероприятий</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Название
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Дата
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Тип
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Группы
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Посещаемость
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($events as $event)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            {{ $event->title }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($event->date_time)->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                            {{ $event->type ?? 'Не указан' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                            {{ $event->eventGroups->pluck('group')->implode(', ') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                            @php
                                $totalAttendances = $event->attendances->count();
                                $attendedCount = $event->attendances->where('attended', 1)->count();
                                $percentage = $totalAttendances > 0 ? round(($attendedCount / $totalAttendances) * 100) : 0;
                            @endphp
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2 max-w-xs">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span>{{ $attendedCount }}/{{ $totalAttendances }} ({{ $percentage }}%)</span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Данные для графиков
        const eventsByTypeData = @json($eventsByType);
        const attendanceByEventData = @json($attendanceByEvent);
        const attendanceByMonthData = @json($attendanceByMonth);
        const attendanceRateByGroupData = @json($attendanceRateByGroup);
        const attendanceTrendData = @json($attendanceTrend);
        const attendanceHeatmapData = @json($attendanceHeatmap);
        
        // График мероприятий по типам
        const eventsByTypeCtx = document.getElementById('eventsByTypeChart').getContext('2d');
        new Chart(eventsByTypeCtx, {
            type: 'pie',
            data: {
                labels: eventsByTypeData.map(item => item.name),
                datasets: [{
                    data: eventsByTypeData.map(item => item.total),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                        'rgba(40, 159, 64, 0.8)',
                        'rgba(210, 199, 199, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)',
                        'rgba(83, 102, 255, 1)',
                        'rgba(40, 159, 64, 1)',
                        'rgba(210, 199, 199, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
        
        // График посещаемости по мероприятиям
        const attendanceByEventCtx = document.getElementById('attendanceByEventChart').getContext('2d');
        new Chart(attendanceByEventCtx, {
            type: 'bar',
            data: {
                labels: attendanceByEventData.map(item => item.title),
                datasets: [{
                    label: 'Посетили',
                    data: attendanceByEventData.map(item => item.attended),
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true,
                        ticks: {
                            autoSkip: false,
                            maxRotation: 90,
                            minRotation: 0
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                }
            }
        });
        
        // График посещаемости по месяцам
        const attendanceByMonthCtx = document.getElementById('attendanceByMonthChart').getContext('2d');
        new Chart(attendanceByMonthCtx, {
            type: 'line',
            data: {
                labels: attendanceByMonthData.map(item => item.month_year),
                datasets: [{
                    label: 'Посетили',
                    data: attendanceByMonthData.map(item => item.attended),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1
                }, {
                    label: 'Не посетили',
                    data: attendanceByMonthData.map(item => item.not_attended),
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Диаграмма процента посещаемости по группам
        const attendanceRateByGroupCtx = document.getElementById('attendanceRateByGroupChart').getContext('2d');
        new Chart(attendanceRateByGroupCtx, {
            type: 'bar',
            data: {
                labels: attendanceRateByGroupData.map(item => item.name),
                datasets: [{
                    label: 'Процент посещаемости',
                    data: attendanceRateByGroupData.map(item => item.rate),
                    backgroundColor: attendanceRateByGroupData.map(item => {
                        const rate = item.rate;
                        if (rate >= 80) return 'rgba(75, 192, 192, 0.8)'; // Зеленый
                        if (rate >= 60) return 'rgba(255, 206, 86, 0.8)'; // Желтый
                        return 'rgba(255, 99, 132, 0.8)'; // Красный
                    }),
                    borderColor: attendanceRateByGroupData.map(item => {
                        const rate = item.rate;
                        if (rate >= 80) return 'rgba(75, 192, 192, 1)';
                        if (rate >= 60) return 'rgba(255, 206, 86, 1)';
                        return 'rgba(255, 99, 132, 1)';
                    }),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Процент посещаемости'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.raw + '%';
                            }
                        }
                    }
                }
            }
        });
        
        // График тренда посещаемости по неделям
        const attendanceTrendCtx = document.getElementById('attendanceTrendChart').getContext('2d');
        new Chart(attendanceTrendCtx, {
            type: 'line',
            data: {
                labels: attendanceTrendData.map(item => item.week),
                datasets: [{
                    label: 'Процент посещаемости',
                    data: attendanceTrendData.map(item => item.rate),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Процент посещаемости'
                        }
                    },
                    x: {
                        ticks: {
                            autoSkip: true,
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const dataPoint = attendanceTrendData[context.dataIndex];
                                return `Посещаемость: ${dataPoint.rate}% (${dataPoint.attended}/${dataPoint.total})`;
                            }
                        }
                    }
                }
            }
        });
        
        // Тепловая карта посещаемости
        const attendanceHeatmapCtx = document.getElementById('attendanceHeatmapChart').getContext('2d');
        new Chart(attendanceHeatmapCtx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Процент посещаемости',
                    data: attendanceHeatmapData.data,
                    backgroundColor: function(context) {
                        const value = context.raw.value;
                        if (value >= 80) return 'rgba(75, 192, 192, 0.8)'; // Зеленый
                        if (value >= 60) return 'rgba(255, 206, 86, 0.8)'; // Желтый
                        if (value >= 40) return 'rgba(255, 159, 64, 0.8)'; // Оранжевый
                        return 'rgba(255, 99, 132, 0.8)'; // Красный
                    },
                    pointRadius: 15,
                    pointHoverRadius: 18
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        type: 'linear',
                        position: 'bottom',
                        min: -0.5,
                        max: attendanceHeatmapData.times.length - 0.5,
                        ticks: {
                            callback: function(value) {
                                return attendanceHeatmapData.times[value];
                            },
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'Время'
                        }
                    },
                    y: {
                        type: 'linear',
                        min: -0.5,
                        max: attendanceHeatmapData.days.length - 0.5,
                        ticks: {
                            callback: function(value) {
                                return attendanceHeatmapData.days[value];
                            },
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'День недели'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const x = context.raw.x;
                                const y = context.raw.y;
                                const value = context.raw.value;
                                const day = attendanceHeatmapData.days[y];
                                const time = attendanceHeatmapData.times[x];
                                return `${day}, ${time}: ${value}% посещаемость`;
                            }
                        }
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
