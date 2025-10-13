<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    /**
     * Заполнение базы данных тестовыми данными.
     *
     * @return void
     */
    public function run()
    {
        // Очистка таблиц перед заполнением
        DB::table('attendances')->truncate();
        DB::table('registrations')->truncate();
        DB::table('event_groups')->truncate();
        DB::table('events')->truncate();
        DB::table('invitations')->truncate();
        DB::table('users')->truncate();

        // Создание администратора
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Администратор',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Создание групп
        $groups = ['Т-191', 'Т-192', 'Т-193','Т-194', 'Т-195', 'Т-196'];

        // Создание кураторов для каждой группы
        $curatorIds = [];
        foreach ($groups as $index => $group) {
            $curatorIds[$group] = DB::table('users')->insertGetId([
                'name' => 'Куратор ' . $group,
                'email' => 'curator' . ($index + 1) . '@example.com',
                'password' => Hash::make('password'),
                'role' => 'curator',
                'group' => $group,
                'curator_groups' => json_encode([$group]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Создание студентов для каждой группы
        $studentIds = [];
        foreach ($groups as $group) {
            for ($i = 1; $i <= 15; $i++) {
                $studentIds[] = DB::table('users')->insertGetId([
                    'name' => 'Студент ' . $i . ' ' . $group,
                    'email' => 'student' . $i . '_' . strtolower(str_replace('-', '', $group)) . '@example.com',
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'group' => $group,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Типы мероприятий
        $eventTypes = ['Лекция', 'Семинар', 'Практика', 'Конференция', 'Мастер-класс', 'Экскурсия'];
        
        // Тематики мероприятий
        $eventThemes = ['Программирование', 'Базы данных', 'Сети', 'Искусственный интеллект', 'Веб-разработка', 'Мобильная разработка', 'Информационная безопасность'];
        
        // Места проведения
        $locations = ['Аудитория 101', 'Аудитория 202', 'Конференц-зал', 'Актовый зал', 'Компьютерный класс 1', 'Компьютерный класс 2', 'Лаборатория'];

        // Создание мероприятий
        $eventIds = [];
        for ($i = 1; $i <= 50; $i++) {
            // Определяем дату мероприятия (от 3 месяцев назад до 3 месяцев вперед)
            $date = Carbon::now()->subMonths(3)->addDays(rand(0, 180));
            
            // Выбираем случайный тип и тематику
            $type = $eventTypes[array_rand($eventTypes)];
            $theme = $eventThemes[array_rand($eventThemes)];
            $location = $locations[array_rand($locations)];
            
            // Создаем мероприятие
            $eventIds[] = DB::table('events')->insertGetId([
                'title' => $type . ' по теме "' . $theme . '" #' . $i,
                'description' => 'Описание мероприятия ' . $i . '. Тематика: ' . $theme . '. Тип: ' . $type . '.',
                'date_time' => $date->format('Y-m-d H:i:s'),
                'location' => $location,
                'type' => $type,
                'theme' => $theme,
                'user_id' => $adminId,
                'qr_token' => Str::random(20),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Привязка мероприятий к группам
        foreach ($eventIds as $eventId) {
            // Выбираем случайное количество групп для мероприятия (от 1 до 3)
            $selectedGroups = array_rand(array_flip($groups), rand(1, 3));
            if (!is_array($selectedGroups)) {
                $selectedGroups = [$selectedGroups];
            }
            
            foreach ($selectedGroups as $group) {
                DB::table('event_groups')->insert([
                    'event_id' => $eventId,
                    'group' => $group,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Создание посещений для прошедших мероприятий
        foreach ($eventIds as $eventId) {
            // Получаем информацию о мероприятии
            $event = DB::table('events')->where('id', $eventId)->first();
            
            // Если мероприятие уже прошло
            if (Carbon::parse($event->date_time)->isPast()) {
                // Получаем группы, привязанные к мероприятию
                $eventGroups = DB::table('event_groups')->where('event_id', $eventId)->pluck('group')->toArray();
                
                // Для каждой группы создаем посещения
                foreach ($eventGroups as $group) {
                    // Получаем студентов группы
                    $groupStudents = DB::table('users')
                        ->where('role', 'student')
                        ->where('group', $group)
                        ->pluck('id')
                        ->toArray();
                    
                    // Для каждого студента с вероятностью 70% создаем запись о посещении
                    foreach ($groupStudents as $studentId) {
                        if (rand(1, 100) <= 70) {
                            DB::table('attendances')->insert([
                                'event_id' => $eventId,
                                'user_id' => $studentId,
                                'attended' => true,
                                'created_at' => Carbon::parse($event->date_time),
                                'updated_at' => Carbon::parse($event->date_time),
                            ]);
                        }
                    }
                }
            }
        }

        // Создание приглашений
        $invitationTypes = ['одноразовое', 'многоразовое'];
        $invitationRoles = ['student', 'curator'];
        
        for ($i = 1; $i <= 10; $i++) {
            $type = $invitationTypes[array_rand($invitationTypes)];
            $role = $invitationRoles[array_rand($invitationRoles)];
            $group = $groups[array_rand($groups)];
            
            DB::table('invitations')->insert([
                'code' => Str::random(8),
                'type' => $type,
                'role' => $role,
                'group' => $group,
                'used' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
