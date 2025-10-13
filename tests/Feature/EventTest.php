<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест просмотра списка мероприятий администратором.
     *
     * @return void
     */
    public function test_admin_can_view_all_events()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $event1 = Event::factory()->create([
            'title' => 'Тестовое мероприятие 1',
            'description' => 'Описание тестового мероприятия 1',
        ]);
        
        $event2 = Event::factory()->create([
            'title' => 'Тестовое мероприятие 2',
            'description' => 'Описание тестового мероприятия 2',
        ]);

        $response = $this->actingAs($admin)->get('/events');

        $response->assertStatus(200);
        $response->assertSee('Тестовое мероприятие 1');
        $response->assertSee('Тестовое мероприятие 2');
    }

    /**
     * Тест просмотра списка мероприятий куратором.
     *
     * @return void
     */
    public function test_curator_can_view_events_for_their_groups()
    {
        $curator = User::factory()->create([
            'role' => 'curator',
            'curator_groups' => ['Т-192'],
        ]);
        
        // Мероприятие для группы куратора
        $event1 = Event::factory()->create([
            'title' => 'Мероприятие для Т-192',
        ]);
        $event1->eventGroups()->create(['group' => 'Т-192']);
        
        // Мероприятие для другой группы
        $event2 = Event::factory()->create([
            'title' => 'Мероприятие для Т-193',
        ]);
        $event2->eventGroups()->create(['group' => 'Т-193']);

        $response = $this->actingAs($curator)->get('/events');

        $response->assertStatus(200);
        $response->assertSee('Мероприятие для Т-192');
        $response->assertDontSee('Мероприятие для Т-193');
    }

    /**
     * Тест просмотра списка мероприятий студентом.
     *
     * @return void
     */
    public function test_student_can_view_events_for_their_group()
    {
        $student = User::factory()->create([
            'role' => 'student',
            'group' => 'Т-192',
        ]);
        
        // Мероприятие для группы студента
        $event1 = Event::factory()->create([
            'title' => 'Мероприятие для Т-192',
        ]);
        $event1->eventGroups()->create(['group' => 'Т-192']);
        
        // Мероприятие для другой группы
        $event2 = Event::factory()->create([
            'title' => 'Мероприятие для Т-193',
        ]);
        $event2->eventGroups()->create(['group' => 'Т-193']);
        
        // Мероприятие без указания группы (доступно всем)
        $event3 = Event::factory()->create([
            'title' => 'Общее мероприятие',
        ]);

        $response = $this->actingAs($student)->get('/events');

        $response->assertStatus(200);
        $response->assertSee('Мероприятие для Т-192');
        $response->assertSee('Общее мероприятие');
        $response->assertDontSee('Мероприятие для Т-193');
    }

    /**
     * Тест создания мероприятия администратором.
     *
     * @return void
     */
    public function test_admin_can_create_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/events/create');
        $response->assertStatus(200);

        $eventData = [
            'title' => 'Новое тестовое мероприятие',
            'description' => 'Описание нового тестового мероприятия',
            'date' => now()->addDays(7)->format('Y-m-d'),
            'time' => '14:00',
            'location' => 'Тестовое место проведения',
            'type' => 'lecture',
            'max_participants' => 50,
            'groups' => ['Т-192', 'Т-193'],
        ];

        $response = $this->actingAs($admin)->post('/events', $eventData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('events', [
            'title' => 'Новое тестовое мероприятие',
            'description' => 'Описание нового тестового мероприятия',
        ]);
    }

    /**
     * Тест запрета создания мероприятия студентом.
     *
     * @return void
     */
    public function test_student_cannot_create_event()
    {
        $student = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);

        $response = $this->actingAs($student)->get('/events/create');
        $response->assertStatus(403);

        $eventData = [
            'title' => 'Новое тестовое мероприятие',
            'description' => 'Описание нового тестового мероприятия',
            'date' => now()->addDays(7)->format('Y-m-d'),
            'time' => '14:00',
            'location' => 'Тестовое место проведения',
            'type' => 'lecture',
            'max_participants' => 50,
        ];

        $response = $this->actingAs($student)->post('/events', $eventData);
        $response->assertStatus(403);
    }

    /**
     * Тест редактирования мероприятия администратором.
     *
     * @return void
     */
    public function test_admin_can_edit_event()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::factory()->create([
            'title' => 'Тестовое мероприятие',
            'description' => 'Описание тестового мероприятия',
        ]);

        $response = $this->actingAs($admin)->get("/events/{$event->id}/edit");
        $response->assertStatus(200);

        $updatedData = [
            'title' => 'Обновленное тестовое мероприятие',
            'description' => 'Обновленное описание тестового мероприятия',
            'date' => now()->addDays(7)->format('Y-m-d'),
            'time' => '15:00',
            'location' => 'Обновленное место проведения',
            'type' => 'workshop',
            'max_participants' => 60,
        ];

        $response = $this->actingAs($admin)->put("/events/{$event->id}", $updatedData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Обновленное тестовое мероприятие',
            'description' => 'Обновленное описание тестового мероприятия',
        ]);
    }

    /**
     * Тест регистрации на мероприятие студентом.
     *
     * @return void
     */
    public function test_student_can_register_for_event()
    {
        $student = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);
        $event = Event::factory()->create([
            'title' => 'Тестовое мероприятие',
            'max_participants' => 50,
            'date' => now()->addDays(7)->format('Y-m-d'),
        ]);
        $event->eventGroups()->create(['group' => 'Т-192']);

        $response = $this->actingAs($student)->post("/events/{$event->id}/register");
        
        $response->assertRedirect();
        $this->assertDatabaseHas('registrations', [
            'user_id' => $student->id,
            'event_id' => $event->id,
        ]);
    }

    /**
     * Тест отмены регистрации на мероприятие студентом.
     *
     * @return void
     */
    public function test_student_can_unregister_from_event()
    {
        $student = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);
        $event = Event::factory()->create([
            'title' => 'Тестовое мероприятие',
            'max_participants' => 50,
            'date' => now()->addDays(7)->format('Y-m-d'),
        ]);
        
        Registration::create([
            'user_id' => $student->id,
            'event_id' => $event->id,
        ]);

        $response = $this->actingAs($student)->delete("/events/{$event->id}/unregister");
        
        $response->assertRedirect();
        $this->assertDatabaseMissing('registrations', [
            'user_id' => $student->id,
            'event_id' => $event->id,
        ]);
    }

    /**
     * Тест просмотра QR-кода мероприятия администратором.
     *
     * @return void
     */
    public function test_admin_can_view_event_qr_code()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::factory()->create([
            'title' => 'Тестовое мероприятие',
        ]);

        $response = $this->actingAs($admin)->get("/events/{$event->id}/qrcode");
        
        $response->assertStatus(200);
        $response->assertSee('QR-код для мероприятия');
    }

    /**
     * Тест запрета просмотра QR-кода мероприятия студентом.
     *
     * @return void
     */
    public function test_student_cannot_view_event_qr_code()
    {
        $student = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);
        $event = Event::factory()->create([
            'title' => 'Тестовое мероприятие',
        ]);

        $response = $this->actingAs($student)->get("/events/{$event->id}/qrcode");
        
        $response->assertStatus(403);
    }

    /**
     * Тест поиска мероприятий.
     *
     * @return void
     */
    public function test_search_events()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        Event::factory()->create(['title' => 'Лекция по математике']);
        Event::factory()->create(['title' => 'Семинар по физике']);
        Event::factory()->create(['title' => 'Практикум по программированию']);

        $response = $this->actingAs($admin)->get('/events?search=математике');
        
        $response->assertStatus(200);
        $response->assertSee('Лекция по математике');
        $response->assertDontSee('Семинар по физике');
        $response->assertDontSee('Практикум по программированию');
    }
}
