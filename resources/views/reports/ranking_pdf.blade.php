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
        .highlight {
            background-color: #fffde7;
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
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    
    <div>
        <!-- Статистика по группам -->
        <h2>Статистика посещаемости по группам</h2>
        <table>
            <thead>
                <tr>
                    <th>Группа</th>
                    <th>Кол-во учащихся</th>
                    <th>Посещено</th>
                    <th>Пропущено</th>
                    <th>Процент посещаемости</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groupsStatistics as $groupStat)
                <tr>
                    <td>{{ $groupStat['name'] }}</td>
                    <td>{{ $groupStat['students_count'] }}</td>
                    <td>{{ $groupStat['attended_events'] }}</td>
                    <td>{{ $groupStat['missed_events'] }}</td>
                    <td>
                        {{ $groupStat['attendance_rate'] }}%
                        <div class="progress-bar">
                            <div class="progress-value" style="width: {{ $groupStat['attendance_rate'] }}%"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Рейтинг студентов -->
        <h2>Рейтинг учащихся по посещаемости</h2>
        <table>
            <thead>
                <tr>
                    <th>Место</th>
                    <th>Учащийся</th>
                    <th>Группа</th>
                    <th>Посещено</th>
                    <th>Пропущено</th>
                    <th>Процент посещаемости</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studentsRanking as $index => $student)
                <tr class="{{ $index < 3 ? 'highlight' : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student['name'] }}</td>
                    <td>{{ $student['group'] }}</td>
                    <td>{{ $student['attended_events'] }}</td>
                    <td>{{ $student['missed_events'] }}</td>
                    <td>
                        {{ $student['attendance_rate'] }}%
                        <div class="progress-bar">
                            <div class="progress-value" style="width: {{ $student['attendance_rate'] }}%"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        <p>Отчет сгенерирован {{ now()->format('d.m.Y H:i:s') }}</p>
        <p>Период: {{ $period }}</p>
    </div>
</body>
</html>
