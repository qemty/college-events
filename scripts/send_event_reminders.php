<?php

// Скрипт для отправки напоминаний о предстоящих мероприятиях

// Подключаем автозагрузчик Composer
require __DIR__ . '/../vendor/autoload.php';

// Загружаем переменные окружения из .env файла
// Исправлено: правильный импорт и использование Dotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Подключаемся к базе данных
$host = $_ENV['DB_HOST'];
$database = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Получаем список мероприятий, которые состоятся в ближайшие 24 часа
$tomorrow = date('Y-m-d H:i:s', strtotime('+24 hours'));
$now = date('Y-m-d H:i:s');

$stmt = $pdo->prepare("
    SELECT e.id, e.title, e.date, e.location, u.email, u.name
    FROM events e
    JOIN event_user eu ON e.id = eu.event_id
    JOIN users u ON eu.user_id = u.id
    WHERE e.date BETWEEN :now AND :tomorrow
    AND e.status = 'active'
    AND u.email_notifications = 1
");

$stmt->bindParam(':now', $now);
$stmt->bindParam(':tomorrow', $tomorrow);
$stmt->execute();

$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Отправляем напоминания
$sentCount = 0;

foreach ($events as $event) {
    $to = $event['email'];
    $subject = "Напоминание о мероприятии: {$event['title']}";
    
    $message = "
    <html>
    <head>
        <title>Напоминание о мероприятии</title>
    </head>
    <body>
        <h2>Здравствуйте, {$event['name']}!</h2>
        <p>Напоминаем, что вы зарегистрированы на мероприятие <strong>{$event['title']}</strong>, которое состоится <strong>" . date('d.m.Y в H:i', strtotime($event['date'])) . "</strong>.</p>
        <p>Место проведения: <strong>{$event['location']}</strong></p>
        <p>Не забудьте взять с собой студенческий билет!</p>
        <p>С уважением,<br>Команда College Events</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: College Events <noreply@college-events.com>" . "\r\n";
    
    if (mail($to, $subject, $message, $headers)) {
        $sentCount++;
        
        // Логируем отправку
        $logStmt = $pdo->prepare("
            INSERT INTO email_logs (user_id, event_id, type, sent_at)
            VALUES (
                (SELECT id FROM users WHERE email = :email LIMIT 1),
                :event_id,
                'reminder',
                NOW()
            )
        ");
        
        $logStmt->bindParam(':email', $to);
        $logStmt->bindParam(':event_id', $event['id']);
        $logStmt->execute();
    }
}

echo "Отправлено напоминаний: $sentCount\n";
