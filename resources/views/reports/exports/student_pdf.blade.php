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
        .student-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .stat-box {
            width: 30%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }
        .stat-label {
            font-size: 11px;
            color: #666;
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
    
    <!-- Информация о студенте -->
    <div class="student-info">
        <h2>Информация о студенте</h2>
        <p><strong>ФИО:</strong> {{ $student->name }}</p>
        <p><strong>Группа:</strong> {{ $student->group }}</p>
        <p><strong>Email:</strong> {{ $student->email }}</p>
    </div>
    
    <!-- Общая статистика -->
    <h2>Общая статистика посещаемости</h2>
    <div style="width: 100%; margin-bottom: 20px;">
        <div style="width: 30%; float: left; padding: 10px; text-align: center; border: 1px solid #ddd; margin-right: 5%;">
            <div style="font-size: 11px; color: #666;">Всего мероприятий</div>
            <div style="font-size: 18px; font-weight: bold; margin: 5px 0;">{{ $studentAttendanceData['total_events'] }}</div>
        </div>
        <div style="width: 30%; float: left; padding: 10px; text-align: center; border: 1px solid #ddd; margin-right: 5%;">
            <div style="font-size: 11px; color: #666;">Посещено</div>
            <div style="font-size: 18px; font-weight: bold; margin: 5px 0; color: #38a169;">{{ $studentAttendanceData['attended_events'] }}</div>
        </div>
        <div style="width: 30%; float: left; padding: 10px; text-align: center; border: 1px solid #ddd;">
            <div style="font-size: 11px; color: #666;">Пропущено</div>
            <div style="font-size: 18px; font-weight: bold; margin: 5px 0; color: #e53e3e;">{{ $studentAttendanceData['missed_events'] }}</div>
        </div>
    </div>
    
    <div style="clear: both; margin-top: 20px;">
        <h3>Общий процент посещаемости: 
            <span class="{{ $studentAttendanceData['attendance_rate'] >= 70 ? 'good' : ($studentAttendanceData['attendance_rate'] >= 50 ? 'average' : 'poor') }}">
                {{ $studentAttendanceData['attendance_rate'] }}%
            </span>
        </h3>
        <div class="progress-bar">
            <div class="progress-value" style="width: {{ $studentAttendanceData['attendance_rate'] }}%"></div>
        </div>
    </div>
    
    <!-- Сравнение с группой -->
    <h2>Сравнение с группой</h2>
    <table>
        <tr>
            <th>Показатель</th>
            <th>Значение</th>
            <th>Комментарий</th>
        </tr>
        <tr>
            <td>Рейтинг в группе</td>
            <td>{{ $studentComparisonWithGroup['student_rank'] }} из {{ $studentComparisonWithGroup['total_students'] }}</td>
            <td>
                @if($studentComparisonWithGroup['student_rank'] <= 3)
                    Отличный результат! Вы в числе лидеров группы по посещаемости.
                @elseif($studentComparisonWithGroup['student_rank'] <= $studentComparisonWithGroup['total_students'] / 2)
                    Хороший результат. Вы в верхней половине рейтинга группы.
                @else
                    Есть куда стремиться. Ваш показатель ниже среднего по группе.
                @endif
            </td>
        </tr>
        <tr>
            <td>Ваша посещаемость</td>
            <td>{{ $studentComparisonWithGroup['student_rate'] }}%</td>
            <td rowspan="2">
                <span class="{{ $studentComparisonWithGroup['difference'] >= 0 ? 'good' : 'poor' }}">
                    {{ $studentComparisonWithGroup['difference'] >= 0 ? '+' : '' }}{{ $studentComparisonWithGroup['difference'] }}%
                    {{ $studentComparisonWithGroup['difference'] >= 0 ? 'выше' : 'ниже' }} среднего
                </span>
            </td>
        </tr>
        <tr>
            <td>Средняя по группе</td>
            <td>{{ $studentComparisonWithGroup['group_average_rate'] }}%</td>
        </tr>
    </table>
    
    <!-- Посещаемость по типам мероприятий -->
    <h2>Посещаемость по типам мероприятий</h2>
    <table>
        <thead>
            <tr>
                <th>Тип мероприятия</th>
                <th>Посещено</th>
                <th>Пропущено</th>
                <th>Всего</th>
                <th>Процент посещаемости</th>
            </tr>
        </thead>
        <tbody>
            @foreach($studentAttendanceByType as $type)
            <tr>
                <td>{{ $type['name'] }}</td>
                <td>{{ $type['attended'] }}</td>
                <td>{{ $type['missed'] }}</td>
                <td>{{ $type['total'] }}</td>
                <td>
                    {{ $type['rate'] }}%
                    <div class="progress-bar">
                        <div class="progress-value" style="width: {{ $type['rate'] }}%"></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Посещаемость по месяцам -->
    <h2>Динамика посещаемости по месяцам</h2>
    <table>
        <thead>
            <tr>
                <th>Месяц</th>
                <th>Посещено</th>
                <th>Пропущено</th>
                <th>Всего</th>
                <th>Процент посещаемости</th>
            </tr>
        </thead>
        <tbody>
            @foreach($studentAttendanceByMonth as $month)
            <tr>
                <td>{{ $month['month_year'] }}</td>
                <td>{{ $month['attended'] }}</td>
                <td>{{ $month['missed'] }}</td>
                <td>{{ $month['total'] }}</td>
                <td>
                    {{ $month['rate'] }}%
                    <div class="progress-bar">
                        <div class="progress-value" style="width: {{ $month['rate'] }}%"></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Рекомендации -->
    <h2>Рекомендации и выводы</h2>
    <div style="padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;">
        @if($studentAttendanceData['attendance_rate'] >= 80)
            <p>Отличный показатель посещаемости! Продолжайте в том же духе.</p>
            <p>Ваша высокая посещаемость положительно влияет на успеваемость и общий рейтинг группы.</p>
        @elseif($studentAttendanceData['attendance_rate'] >= 60)
            <p>Хороший показатель посещаемости, но есть потенциал для улучшения.</p>
            <p>Обратите внимание на типы мероприятий с наименьшей посещаемостью.</p>
        @else
            <p>Рекомендуется улучшить показатель посещаемости.</p>
            <p>Низкая посещаемость может негативно сказаться на успеваемости и общем рейтинге группы.</p>
            <p>Обратите особое внимание на типы мероприятий с наименьшей посещаемостью.</p>
        @endif
        
        @if($studentComparisonWithGroup['difference'] < 0)
            <p>Ваша посещаемость ниже средней по группе. Постарайтесь улучшить этот показатель.</p>
        @endif
    </div>
    
    <div class="footer">
        <p>Отчет сгенерирован {{ now()->format('d.m.Y H:i:s') }}</p>
        <p>Колледж мероприятий - Система учета посещаемости</p>
    </div>
</body>
</html>
