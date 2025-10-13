<?php

return [

     // Общие элементы интерфейса
    'dashboard' => 'Панель управления',
    'events' => 'Мероприятия',
    'users' => 'Пользователи',
    'reports' => 'Отчеты',
    'help' => 'Помощь',
    'invitations' => 'Приглашения',
    'profile' => 'Профиль',
    'settings' => 'Настройки',
    'logout' => 'Выход',
    

     'previous' => '&laquo; Предыдущая',
    'next' => 'Следующая &raquo;',
    'showing' => 'Показано с',
    'to' => 'по',
    'of' => 'из',
    'results' => 'результатов',

    // Мероприятия
    'event' => 'Мероприятие',
    'event_name' => 'Название мероприятия',
    'event_type' => 'Тип мероприятия',
    'event_date' => 'Дата мероприятия',
    'event_time' => 'Время мероприятия',
    'event_location' => 'Место проведения',
    'event_description' => 'Описание мероприятия',
    'create_event' => 'Создать мероприятие',
    'edit_event' => 'Редактировать мероприятие',
    'delete_event' => 'Удалить мероприятие',
    'event_details' => 'Детали мероприятия',
    'event_participants' => 'Участники мероприятия',
    'event_groups' => 'Группы',
    'add_group' => 'Добавить группу',
    'new_group' => 'Новая группа',
    
    // Пользователи
    'user' => 'Пользователь',
    'name' => 'Имя',
    'email' => 'Email',
    'role' => 'Роль',
    'create_user' => 'Создать пользователя',
    'edit_user' => 'Редактировать пользователя',
    'delete_user' => 'Удалить пользователя',
    'admin' => 'Администратор',
    'curator' => 'Куратор',
    'student' => 'Студент',
    
    // Отчеты
    'report' => 'Отчет',
    'generate_report' => 'Сформировать отчет',
    'export_report' => 'Экспортировать отчет',
    'report_period' => 'Период отчета',
    'report_type' => 'Тип отчета',
    'attendance_report' => 'Отчет о посещаемости',
    'event_report' => 'Отчет по мероприятиям',
    'user_report' => 'Отчет по пользователям',
    
    // Действия
    'create' => 'Создать',
    'edit' => 'Редактировать',
    'update' => 'Обновить',
    'delete' => 'Удалить',
    'save' => 'Сохранить',
    'cancel' => 'Отмена',
    'back' => 'Назад',
    'search' => 'Поиск',
    'filter' => 'Фильтр',
    'apply' => 'Применить',
    'reset' => 'Сбросить',
    'confirm' => 'Подтвердить',
    
    // Статусы
    'success' => 'Успешно',
    'error' => 'Ошибка',
    'warning' => 'Предупреждение',
    'info' => 'Информация',
    'pending' => 'В ожидании',
    'completed' => 'Завершено',
    'cancelled' => 'Отменено',
    
    // Панели
    'student_panel' => 'Студенческая панель',
    'admin_panel' => 'Панель администратора',
    'curator_panel' => 'Панель куратора',
    
    // QR-код
    'qr_code' => 'QR-код',
    'scan_qr' => 'Сканировать QR-код',
    'generate_qr' => 'Сгенерировать QR-код',
    
    // Прочее
    'welcome' => 'Добро пожаловать',
    'no_data' => 'Нет данных',
    'loading' => 'Загрузка...',
    'confirmation' => 'Подтверждение',
    'are_you_sure' => 'Вы уверены?',
    'yes' => 'Да',
    'no' => 'Нет',

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'Europe/Moscow', // MSK (UTC+3)

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'ru'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'ru_RU'),

    

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],



];
