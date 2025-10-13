<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Общий отчет по мероприятиям</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        h1 {
            font-size: 18px;
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 16px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        h3 {
            font-size: 14px;
            margin-top: 15px;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 30px;
        }
        .progress-bar {
            background-color: #e0e0e0;
            height: 10px;
            width: 100%;
            border-radius: 5px;
            margin-top: 3px;
        }
        .progress-value {
            background-color: #4299e1;
            height: 10px;
            border-radius: 5px;
        }
        .period-comparison {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .period-box {
            width: 30%;
            padding: 10px;
            border: 1px solid #ddd;
        }
        .period-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .good {
            color: #38a169;
        }
        .average {
            color: #d69e2e;
        }
        .poor {
            color: #e53e3e;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    
    <!-- Сравнение периодов -->
    <h2>Сравнение периодов</h2>
    <div style="width: 100%; margin-bottom: 20px;">
        <div style="width: 30%; float: left; padding: 10px; border: 1px solid #ddd; margin-right: 3%;">
            <div style="font-weight: bold; margin-bottom: 5px;">Текущий период</div>
            <p style="font-size: 11px; color: #666;">{{ $periodComparison['current_period']['start_date'] }} - {{ $periodComparison['current_period']['end_date'] }}</p>
            <div style="margin-top: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span style="font-size: 11px;">Посещаемость</span>
                    <span style="font-size: 11px;">{{ $periodComparison['current_period']['rate'] }}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-value" style="width: {{ $periodComparison['current_period']['rate'] }}%"></div>
                </div>
            </div>
            <div style="margin-top: 10px; display: flex; justify-content: space-between;">
                <div style="text-align: center; width: 48%;">
                    <p style="font-size: 10px; color: #666; margin: 0;">Мероприятий</p>
                    <p style="font-size: 14px; font-weight: bold; margin: 5px 0;">{{ $periodComparison['current_period']['events_count'] }}</p>
                </div>
                <div style="text-align: center; width: 48%;">
                    <p style="font-size: 10px; color: #666; margin: 0;">Посещений</p>
                    <p style="font-size: 14px; font-weight: bold; margin: 5px 0;">{{ $periodComparison['current_period']['attended'] }}/{{ $periodComparison['current_period']['total'] }}</p>
                </div>
            </div>
        </div>
        
        <div style="width: 30%; float: left; padding: 10px; border: 1px solid #ddd; margin-right: 3%;">
            <div style="font-weight: bold; margin-bottom: 5px;">Предыдущий период</div>
            <p style="font-size: 11px; color: #666;">{{ $periodComparison['previous_period']['start_date'] }} - {{ $periodComparison['previous_period']['end_date'] }}</p>
            <div style="margin-top: 10px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span style="font-size: 11px;">Посещаемость</span>
                    <span style="font-size: 11px;">{{ $periodComparison['previous_period']['rate'] }}%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-value" style="width: {{ $periodComparison['previous_period']['rate'] }}%"></div>
                </div>
            </div>
            <div style="margin-top: 10px; display: flex; justify-content: space-between;">
                <div style="text-align: center; width: 48%;">
                    <p style="font-size: 10px; color: #666; margin: 0;">Мероприятий</p>
                    <p style="font-size: 14px; font-weight: bold; margin: 5px 0;">{{ $periodComparison['previous_period']['events_count'] }}</p>
                </div>
                <div style="text-align: center; width: 48%;">
                    <p style="font-size: 10px; color: #666; margin: 0;">Посещений</p>
                    <p style="font-size: 14px; font-weight: bold; margin: 5px 0;">{{ $periodComparison['previous_period']['attended'] }}/{{ $periodComparison['previous_period']['total'] }}</p>
                </div>
            </div>
        </div>
        
        <div style="width: 30%; float: left; padding: 10px; border: 1px solid #ddd; text-align: center;">
            <div style="font-weight: bold; margin-bottom: 5px;">Изменение</div>
            <div style="font-size: 24px; font-weight: bold; margin: 15px 0; {{ $periodComparison['change'] >= 0 ? 'color: #38a169;' : 'color: #e53e3e;' }}">
                {{ $periodComparison['change'] >= 0 ? '+' : '' }}{{ $periodComparison['change'] }}%
            </div>
            <p style="font-size: 11px; color: #666;">
                {{ $periodComparison['change'] >= 0 ? 'Улучшение' : 'Ухудшение' }} посещаемости
            </p>
        </div>
    </div>
    
    <div style="clear: both;"></div>
    
    <!-- Мероприятия по типам -->
    <h2>Мероприятия по типам</h2>
    <table>
        <thead>
            <tr>
                <th>Тип мероприятия</th>
                <th>Количество</th>
                <th>Процент от общего числа</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalEvents = array_sum(array_column($eventsByType, 'total'));
            @endphp
            @foreach($eventsByType as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['total'] }}</td>
                <td>
                    {{ round(($item['total'] / $totalEvents) * 100) }}%
                    <div class="progress-bar">
                        <div class="progress-value" style="width: {{ round(($item['total'] / $totalEvents) * 100) }}%"></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Процент посещаемости по группам -->
    <h2>Процент посещаемости по группам</h2>
    <table>
        <thead>
            <tr>
                <th>Группа</th>
                <th>Посещено</th>
                <th>Всего</th>
                <th>Процент посещаемости</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendanceRateByGroup as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['attended'] }}</td>
                <td>{{ $item['total'] }}</td>
                <td>
                    {{ $item['rate'] }}%
                    <div class="progress-bar">
                        <div class="progress-value" style="width: {{ $item['rate'] }}%"></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Посещаемость по мероприятиям -->
    <h2>Посещаемость по мероприятиям</h2>
    <table>
        <thead>
            <tr>
                <th>Мероприятие</th>
                <th>Посетили</th>
                <th>Не посетили</th>
                <th>Всего</th>
                <th>Процент посещаемости</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendanceByEvent as $item)
            <tr>
                <td>{{ $item['title'] }}</td>
                <td>{{ $item['attended'] }}</td>
                <td>{{ $item['not_attended'] }}</td>
                <td>{{ $item['total'] }}</td>
                <td>
                    {{ $item['rate'] }}%
                    <div class="progress-bar">
                        <div class="progress-value" style="width: {{ $item['rate'] }}%"></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Посещаемость по месяцам -->
    <h2>Посещаемость по месяцам</h2>
    <table>
        <thead>
            <tr>
                <th>Месяц</th>
                <th>Посетили</th>
                <th>Не посетили</th>
                <th>Всего</th>
                <th>Процент посещаемости</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendanceByMonth as $item)
            <tr>
                <td>{{ $item['month_year'] }}</td>
                <td>{{ $item['attended'] }}</td>
                <td>{{ $item['not_attended'] }}</td>
                <td>{{ $item['total'] }}</td>
                <td>
                    @php
                        $rate = $item['total'] > 0 ? round(($item['attended'] / $item['total']) * 100) : 0;
                    @endphp
                    {{ $rate }}%
                    <div class="progress-bar">
                        <div class="progress-value" style="width: {{ $rate }}%"></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Тренд посещаемости -->
    <h2>Тренд посещаемости по неделям</h2>
    <table>
        <thead>
            <tr>
                <th>Неделя</th>
                <th>Посетили</th>
                <th>Всего</th>
                <th>Процент посещаемости</th>
                <th>Тренд</th>
            </tr>
        </thead>
        <tbody>
            @php
                $prevRate = null;
            @endphp
            @foreach($attendanceTrend as $item)
            <tr>
                <td>{{ $item['week'] }}</td>
                <td>{{ $item['attended'] }}</td>
                <td>{{ $item['total'] }}</td>
                <td>{{ $item['rate'] }}%</td>
                <td>
                    @if($prevRate !== null)
                        @if($item['rate'] > $prevRate)
                            <span style="color: #38a169;">↑ +{{ $item['rate'] - $prevRate }}%</span>
                        @elseif($item['rate'] < $prevRate)
                            <span style="color: #e53e3e;">↓ {{ $item['rate'] - $prevRate }}%</span>
                        @else
                            <span style="color: #718096;">→ 0%</span>
                        @endif
                    @else
                        -
                    @endif
                    @php
                        $prevRate = $item['rate'];
                    @endphp
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Список мероприятий -->
    <h2>Список мероприятий</h2>
    <table>
        <thead>
            <tr>
                <th>Название</th>
                <th>Дата</th>
                <th>Тип</th>
                <th>Группы</th>
                <th>Посещаемость</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $event)
            <tr>
                <td>{{ $event->title }}</td>
                <td>{{ \Carbon\Carbon::parse($event->date_time)->format('d.m.Y H:i') }}</td>
                <td>{{ $event->type }}</td>
                <td>{{ $event->eventGroups->pluck('group')->implode(', ') }}</td>
                <td>
                    @php
                        $totalAttendances = $event->attendances->count();
                        $attendedCount = $event->attendances->where('attended', 1)->count();
                        $rate = $totalAttendances > 0 ? round(($attendedCount / $totalAttendances) * 100) : 0;
                    @endphp
                    {{ $attendedCount }}/{{ $totalAttendances }} ({{ $rate }}%)
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Выводы и рекомендации -->
    <h2>Выводы и рекомендации</h2>
    <div style="padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;">
        <h3>Общие показатели</h3>
        <p>
            За анализируемый период было проведено {{ count($events) }} мероприятий.
            Общий процент посещаемости составил {{ $periodComparison['current_period']['rate'] }}%.
            @if($periodComparison['change'] >= 0)
                По сравнению с предыдущим периодом наблюдается положительная динамика (+{{ $periodComparison['change'] }}%).
            @else
                По сравнению с предыдущим периодом наблюдается отрицательная динамика ({{ $periodComparison['change'] }}%).
            @endif
        </p>
        
        <h3>Рекомендации</h3>
        <ul>
            @if($periodComparison['current_period']['rate'] < 70)
                <li>Рекомендуется принять меры по повышению общей посещаемости мероприятий.</li>
            @endif
            
            @php
                $lowestAttendanceGroup = collect($attendanceRateByGroup)->sortBy('rate')->first();
            @endphp
            @if($lowestAttendanceGroup && $lowestAttendanceGroup['rate'] < 60)
                <li>Обратить особое внимание на группу {{ $lowestAttendanceGroup['name'] }} с низким показателем посещаемости ({{ $lowestAttendanceGroup['rate'] }}%).</li>
            @endif
            
            @php
                $lowestAttendanceEvent = collect($attendanceByEvent)->sortBy('rate')->first();
            @endphp
            @if($lowestAttendanceEvent && $lowestAttendanceEvent['rate'] < 50)
                <li>Проанализировать причины низкой посещаемости мероприятия "{{ $lowestAttendanceEvent['title'] }}" ({{ $lowestAttendanceEvent['rate'] }}%).</li>
            @endif
            
            @php
                $trendDown = false;
                $prevRate = null;
                foreach(array_slice($attendanceTrend, -3) as $item) {
                    if($prevRate !== null && $item['rate'] < $prevRate) {
                        $trendDown = true;
                    }
                    $prevRate = $item['rate'];
                }
            @endphp
            @if($trendDown)
                <li>Обратить внимание на снижение посещаемости в последние недели.</li>
            @endif
        </ul>
    </div>
    
    <div class="footer">
        <p>Отчет сгенерирован {{ now()->format('d.m.Y H:i:s') }}</p>
        <p>Колледж мероприятий - Система учета посещаемости</p>
    </div>
</body>
</html>
