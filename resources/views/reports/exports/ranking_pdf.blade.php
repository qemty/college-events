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
        .medal-1 {
            display: inline-block;
            width: 20px;
            height: 20px;
            background-color: #FFD700;
            border-radius: 50%;
            text-align: center;
            color: #333;
            font-weight: bold;
        }
        .medal-2 {
            display: inline-block;
            width: 20px;
            height: 20px;
            background-color: #C0C0C0;
            border-radius: 50%;
            text-align: center;
            color: #333;
            font-weight: bold;
        }
        .medal-3 {
            display: inline-block;
            width: 20px;
            height: 20px;
            background-color: #CD7F32;
            border-radius: 50%;
            text-align: center;
            color: #333;
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
        .highlight-row {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    
    <!-- Сводная информация -->
    <div class="summary-box">
        <h2>Сводная информация</h2>
        <p>Отчет содержит данные о посещаемости студентов и групп за выбранный период.</p>
        <p>Общее количество групп: {{ count($groupsStatistics) }}</p>
        <p>Общее количество студентов в рейтинге: {{ count($studentsRanking) }}</p>
        <p>Средний процент посещаемости: {{ round(array_sum(array_column($groupsStatistics, 'attendance_rate')) / count($groupsStatistics), 1) }}%</p>
    </div>
    
    <!-- Статистика по группам -->
    <h2>Статистика посещаемости по группам</h2>
    <table>
        <thead>
            <tr>
                <th>Место</th>
                <th>Группа</th>
                <th>Кол-во студентов</th>
                <th>Посещено</th>
                <th>Пропущено</th>
                <th>Процент посещаемости</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupsStatistics as $index => $group)
            <tr class="{{ $index < 3 ? 'highlight-row' : '' }}">
                <td>
                    @if($index === 0)
                        <span class="medal-1">1</span>
                    @elseif($index === 1)
                        <span class="medal-2">2</span>
                    @elseif($index === 2)
                        <span class="medal-3">3</span>
                    @else
                        {{ $index + 1 }}
                    @endif
                </td>
                <td>{{ $group['name'] }}</td>
                <td>{{ $group['students_count'] }}</td>
                <td>{{ $group['attended_events'] }}</td>
                <td>{{ $group['missed_events'] }}</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: {{ $group['attendance_rate'] }}%;"></div>
                    </div>
                    {{ $group['attendance_rate'] }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Анализ посещаемости по группам -->
    <h2>Анализ посещаемости по группам</h2>
    <div class="summary-box">
        <h3>Группы с высокой посещаемостью (>80%)</h3>
        <ul>
            @php $highAttendanceGroups = array_filter($groupsStatistics, function($group) { return $group['attendance_rate'] > 80; }); @endphp
            @forelse($highAttendanceGroups as $group)
                <li>{{ $group['name'] }} - {{ $group['attendance_rate'] }}%</li>
            @empty
                <li>Нет групп с посещаемостью выше 80%</li>
            @endforelse
        </ul>
        
        <h3>Группы с низкой посещаемостью (<60%)</h3>
        <ul>
            @php $lowAttendanceGroups = array_filter($groupsStatistics, function($group) { return $group['attendance_rate'] < 60; }); @endphp
            @forelse($lowAttendanceGroups as $group)
                <li>{{ $group['name'] }} - {{ $group['attendance_rate'] }}%</li>
            @empty
                <li>Нет групп с посещаемостью ниже 60%</li>
            @endforelse
        </ul>
        
        <h3>Рекомендации</h3>
        <p>Для групп с низкой посещаемостью рекомендуется:</p>
        <ul>
            <li>Провести анализ причин низкой посещаемости</li>
            <li>Организовать встречу с кураторами групп</li>
            <li>Разработать план мероприятий по повышению посещаемости</li>
            <li>Установить систему поощрений для групп, показывающих улучшение</li>
        </ul>
    </div>
    
    <div class="page-break"></div>
    
    <!-- Рейтинг студентов -->
    <h2>Рейтинг студентов по посещаемости</h2>
    <table>
        <thead>
            <tr>
                <th>Место</th>
                <th>Студент</th>
                <th>Группа</th>
                <th>Посещено</th>
                <th>Пропущено</th>
                <th>Процент посещаемости</th>
            </tr>
        </thead>
        <tbody>
            @foreach($studentsRanking as $index => $student)
            <tr class="{{ $index < 3 ? 'highlight-row' : '' }}">
                <td>
                    @if($index === 0)
                        <span class="medal-1">1</span>
                    @elseif($index === 1)
                        <span class="medal-2">2</span>
                    @elseif($index === 2)
                        <span class="medal-3">3</span>
                    @else
                        {{ $index + 1 }}
                    @endif
                </td>
                <td>{{ $student['name'] }}</td>
                <td>{{ $student['group'] }}</td>
                <td>{{ $student['attended_events'] }}</td>
                <td>{{ $student['missed_events'] }}</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: {{ $student['attendance_rate'] }}%;"></div>
                    </div>
                    {{ $student['attendance_rate'] }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Топ студентов по посещаемости -->
    <h2>Топ-10 студентов по посещаемости</h2>
    <div class="summary-box">
        <table>
            <thead>
                <tr>
                    <th>Место</th>
                    <th>Студент</th>
                    <th>Группа</th>
                    <th>Процент посещаемости</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_slice($studentsRanking, 0, 10) as $index => $student)
                <tr>
                    <td>
                        @if($index === 0)
                            <span class="medal-1">1</span>
                        @elseif($index === 1)
                            <span class="medal-2">2</span>
                        @elseif($index === 2)
                            <span class="medal-3">3</span>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </td>
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['group'] }}</td>
                    <td>{{ $student['attendance_rate'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Студенты, требующие внимания -->
    <h2>Студенты с низкой посещаемостью (менее 50%)</h2>
    <div class="summary-box">
        <table>
            <thead>
                <tr>
                    <th>Студент</th>
                    <th>Группа</th>
                    <th>Посещено</th>
                    <th>Пропущено</th>
                    <th>Процент посещаемости</th>
                </tr>
            </thead>
            <tbody>
                @php $lowAttendanceStudents = array_filter($studentsRanking, function($student) { return $student['attendance_rate'] < 50; }); @endphp
                @forelse($lowAttendanceStudents as $student)
                <tr>
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['group'] }}</td>
                    <td>{{ $student['attended_events'] }}</td>
                    <td>{{ $student['missed_events'] }}</td>
                    <td>{{ $student['attendance_rate'] }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Нет студентов с посещаемостью ниже 50%</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <h3>Рекомендации по работе со студентами с низкой посещаемостью</h3>
        <ul>
            <li>Провести индивидуальные беседы для выяснения причин пропусков</li>
            <li>Разработать индивидуальные планы повышения посещаемости</li>
            <li>Организовать дополнительные консультации для помощи в освоении пропущенного материала</li>
            <li>Рассмотреть возможность привлечения психолога или социального педагога</li>
        </ul>
    </div>
    
    <div class="footer">
        <p>Отчет сгенерирован {{ now()->format('d.m.Y H:i:s') }}</p>
        <p>Система учета посещаемости мероприятий колледжа</p>
    </div>
</body>
</html>
