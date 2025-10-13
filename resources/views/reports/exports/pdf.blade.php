<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
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
        .summary-box {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
        }
        .progress-bar {
            height: 15px;
            background-color: #e0e0e0;
            margin-bottom: 10px;
            position: relative;
        }
        .progress-bar-fill {
            height: 100%;
            background-color: #4a86e8;
            position: absolute;
            top: 0;
            left: 0;
        }
        .page-break {
            page-break-after: always;
        }
        .highlight-positive {
            color: #2e7d32;
            font-weight: bold;
        }
        .highlight-negative {
            color: #c62828;
            font-weight: bold;
        }
        .highlight-neutral {
            color: #f9a825;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    
    <!-- Сводная информация -->
    <div class="summary-box">
        <h2>Сводная информация</h2>
        <p>Период анализа: 
            @if(isset($periodComparison['current_period']['start_date']) && isset($periodComparison['current_period']['end_date']))
                {{ $periodComparison['current_period']['start_date'] }} - {{ $periodComparison['current_period']['end_date'] }}
            @else
                Весь период
            @endif
        </p>
        
        <table>
            <tr>
                <th>Показатель</th>
                <th>Текущий период</th>
                <th>Предыдущий период</th>
                <th>Изменение</th>
            </tr>
            <tr>
                <td>Количество мероприятий</td>
                <td>{{ $periodComparison['current_period']['events_count'] }}</td>
                <td>{{ $periodComparison['previous_period']['events_count'] }}</td>
                <td class="{{ $periodComparison['current_period']['events_count'] - $periodComparison['previous_period']['events_count'] >= 0 ? 'highlight-positive' : 'highlight-negative' }}">
                    {{ $periodComparison['current_period']['events_count'] - $periodComparison['previous_period']['events_count'] >= 0 ? '+' : '' }}{{ $periodComparison['current_period']['events_count'] - $periodComparison['previous_period']['events_count'] }}
                </td>
            </tr>
            <tr>
                <td>Посещаемость</td>
                <td>{{ $periodComparison['current_period']['rate'] }}%</td>
                <td>{{ $periodComparison['previous_period']['rate'] }}%</td>
                <td class="{{ $periodComparison['change'] >= 0 ? 'highlight-positive' : 'highlight-negative' }}">
                    {{ $periodComparison['change'] >= 0 ? '+' : '' }}{{ $periodComparison['change'] }}%
                </td>
            </tr>
            <tr>
                <td>Посещения</td>
                <td>{{ $periodComparison['current_period']['attended'] }}/{{ $periodComparison['current_period']['total'] }}</td>
                <td>{{ $periodComparison['previous_period']['attended'] }}/{{ $periodComparison['previous_period']['total'] }}</td>
                <td>-</td>
            </tr>
        </table>
    </div>
    
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
            @php $totalEvents = array_sum(array_column($eventsByType, 'total')); @endphp
            @foreach($eventsByType as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->total }}</td>
                <td>{{ round(($item->total / $totalEvents) * 100, 1) }}%</td>
            </tr>
            @endforeach
            <tr>
                <td><strong>Всего</strong></td>
                <td><strong>{{ $totalEvents }}</strong></td>
                <td><strong>100%</strong></td>
            </tr>
        </tbody>
    </table>
    
    <!-- Процент посещаемости по группам -->
    <h2>Процент посещаемости по группам</h2>
    <table>
        <thead>
            <tr>
                <th>Группа</th>
                <th>Процент посещаемости</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendanceRateByGroup as $item)
            <tr>
                <td>{{ $item['name'] }}</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: {{ $item['rate'] }}%;"></div>
                    </div>
                    {{ $item['rate'] }}%
                </td>
                <td>
                    @if($item['rate'] >= 80)
                        <span class="highlight-positive">Отлично</span>
                    @elseif($item['rate'] >= 60)
                        <span class="highlight-neutral">Хорошо</span>
                    @else
                        <span class="highlight-negative">Требует внимания</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="page-break"></div>
    
    <!-- Посещаемость по мероприятиям -->
    <h2>Посещаемость по мероприятиям</h2>
    <table>
        <thead>
            <tr>
                <th>Мероприятие</th>
                <th>Дата</th>
                <th>Тип</th>
                <th>Посетили</th>
                <th>Не посетили</th>
                <th>Процент посещаемости</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendanceByEvent as $item)
            <tr>
                <td>{{ $item->title }}</td>
                <td>{{ \Carbon\Carbon::parse($item->date_time)->format('d.m.Y') }}</td>
                <td>{{ $item->type }}</td>
                <td>{{ $item->attended }}</td>
                <td>{{ $item->not_attended }}</td>
                <td>
                    @php $attendanceRate = $item->attended + $item->not_attended > 0 ? round(($item->attended / ($item->attended + $item->not_attended)) * 100, 1) : 0; @endphp
                    {{ $attendanceRate }}%
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
                <th>Количество мероприятий</th>
                <th>Посетили</th>
                <th>Не посетили</th>
                <th>Процент посещаемости</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendanceByMonth as $item)
            <tr>
                <td>{{ $item['month_year'] }}</td>
                <td>{{ $item['events_count'] }}</td>
                <td>{{ $item['attended'] }}</td>
                <td>{{ $item['not_attended'] }}</td>
                <td>
                    @php $monthRate = $item['attended'] + $item['not_attended'] > 0 ? round(($item['attended'] / ($item['attended'] + $item['not_attended'])) * 100, 1) : 0; @endphp
                    {{ $monthRate }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Тренд посещаемости по неделям -->
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
            @php $prevRate = null; @endphp
            @foreach($attendanceTrend as $item)
            <tr>
                <td>{{ $item['week'] }}</td>
                <td>{{ $item['attended'] }}</td>
                <td>{{ $item['total'] }}</td>
                <td>{{ $item['rate'] }}%</td>
                <td>
                    @if($prevRate !== null)
                        @if($item['rate'] > $prevRate)
                            <span class="highlight-positive">↑ +{{ $item['rate'] - $prevRate }}%</span>
                        @elseif($item['rate'] < $prevRate)
                            <span class="highlight-negative">↓ {{ $item['rate'] - $prevRate }}%</span>
                        @else
                            <span class="highlight-neutral">→ 0%</span>
                        @endif
                    @else
                        -
                    @endif
                    @php $prevRate = $item['rate']; @endphp
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="page-break"></div>
    
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
                <td>{{ $event->type ?? 'Не указан' }}</td>
                <td>{{ $event->eventGroups->pluck('group')->implode(', ') }}</td>
                <td>
                    @php
                        $totalAttendances = $event->attendances->count();
                        $attendedCount = $event->attendances->where('attended', 1)->count();
                        $percentage = $totalAttendances > 0 ? round(($attendedCount / $totalAttendances) * 100) : 0;
                    @endphp
                    {{ $attendedCount }}/{{ $totalAttendances }} ({{ $percentage }}%)
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Рекомендации по улучшению посещаемости -->
    <h2>Рекомендации по улучшению посещаемости</h2>
    <div class="summary-box">
        @if($periodComparison['change'] >= 5)
            <p>Отмечается положительная динамика посещаемости (+{{ $periodComparison['change'] }}%). Рекомендуется:</p>
            <ul>
                <li>Продолжать применять текущие методы привлечения студентов</li>
                <li>Проанализировать наиболее успешные мероприятия для масштабирования опыта</li>
                <li>Поощрить группы с наивысшими показателями посещаемости</li>
            </ul>
        @elseif($periodComparison['change'] >= -5 && $periodComparison['change'] < 5)
            <p>Посещаемость остается стабильной ({{ $periodComparison['change'] }}%). Рекомендуется:</p>
            <ul>
                <li>Внедрить новые форматы мероприятий для повышения интереса</li>
                <li>Провести опрос среди студентов для выявления их предпочтений</li>
                <li>Усилить информирование о предстоящих мероприятиях</li>
            </ul>
        @else
            <p>Наблюдается снижение посещаемости ({{ $periodComparison['change'] }}%). Рекомендуется:</p>
            <ul>
                <li>Провести анализ причин снижения посещаемости</li>
                <li>Пересмотреть формат и содержание мероприятий</li>
                <li>Организовать встречи с представителями групп для обсуждения проблем</li>
                <li>Разработать план мероприятий по повышению мотивации студентов</li>
            </ul>
        @endif
        
        <h3>Общие рекомендации:</h3>
        <ul>
            <li>Регулярно обновлять информацию о мероприятиях на информационных ресурсах колледжа</li>
            <li>Внедрить систему поощрений для активных участников</li>
            <li>Привлекать студентов к организации и планированию мероприятий</li>
            <li>Учитывать интересы и предпочтения различных групп студентов</li>
            <li>Проводить мероприятия в удобное для большинства студентов время</li>
        </ul>
    </div>
    
    <div class="footer">
        <p>Отчет сгенерирован {{ now()->format('d.m.Y H:i:s') }}</p>
        <p>Система учета посещаемости мероприятий колледжа</p>
    </div>
</body>
</html>
