<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class QrCodeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест доступа к QR-коду мероприятия для администратора.
     *
     * @return void
     */
    public function test_admin_can_access_qr_code()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::factory()->create([
            'title' => 'Тестовое мероприятие',
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($admin)->get("/events/{$event->id}/qrcode");

        $response->assertStatus(200);
        $response->assertSee('QR-код для мероприятия');
        $response->assertSee($event->title);
    }

    /**
     * Тест доступа к QR-коду мероприятия для куратора группы.
     *
     * @return void
     */
    public function test_curator_can_access_qr_code_for_their_group()
    {
        $curator = User::factory()->create([
            'role' => 'curator',
            'curator_groups' => ['Т-192'],
        ]);
        
        $event = Event::factory()->create([
            'title' => 'Мероприятие для Т-192',
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);
        $event->eventGroups()->create(['group' => 'Т-192']);

        $response = $this->actingAs($curator)->get("/events/{$event->id}/qrcode");

        $response->assertStatus(200);
        $response->assertSee('QR-код для мероприятия');
        $response->assertSee($event->title);
    }

    /**
     * Тест запрета доступа к QR-коду мероприятия для куратора другой группы.
     *
     * @return void
     */
    public function test_curator_cannot_access_qr_code_for_other_groups()
    {
        $curator = User::factory()->create([
            'role' => 'curator',
            'curator_groups' => ['Т-192'],
        ]);
        
        $event = Event::factory()->create([
            'title' => 'Мероприятие для Т-193',
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);
        $event->eventGroups()->create(['group' => 'Т-193']);

        $response = $this->actingAs($curator)->get("/events/{$event->id}/qrcode");

        $response->assertStatus(403);
    }

    /**
     * Тест запрета доступа к QR-коду мероприятия для студента.
     *
     * @return void
     */
    public function test_student_cannot_access_qr_code()
    {
        $student = User::factory()->create([
            'role' => 'student',
            'group' => 'Т-192',
        ]);
        
        $event = Event::factory()->create([
            'title' => 'Мероприятие для Т-192',
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);
        $event->eventGroups()->create(['group' => 'Т-192']);

        $response = $this->actingAs($student)->get("/events/{$event->id}/qrcode");

        $response->assertStatus(403);
    }

    /**
     * Тест скачивания QR-кода мероприятия.
     *
     * @return void
     */
    public function test_admin_can_download_qr_code()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::factory()->create([
            'title' => 'Тестовое мероприятие',
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($admin)->get("/events/{$event->id}/qrcode/download");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertHeader('Content-Disposition', 'attachment; filename=qrcode_event_' . $event->id . '.png');
    }

    /**
     * Тест верификации посещаемости через QR-код.
     *
     * @return void
     */
    public function test_verify_attendance_through_qr_code()
    {
        $student = User::factory()->create([
            'role' => 'student',
            'group' => 'Т-192',
        ]);
        
        $event = Event::factory()->create([
            'title' => 'Тестовое мероприятие',
            'date' => now()->format('Y-m-d'),
        ]);
        
        // Генерируем токен для верификации посещаемости
        $token = Str::random(64);
        $verificationUrl = "/events/attendance/verify/{$token}";
        
        // Сохраняем токен в кэше или базе данных (в реальном приложении)
        // В тесте мы просто мокаем этот процесс
        
        $response = $this->actingAs($student)->get($verificationUrl);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Проверяем, что запись о посещаемости создана
        $this->assertDatabaseHas('attendances', [
            'user_id' => $student->id,
            'event_id' => $event->id,
        ]);
    }

    /**
     * Тест ручного добавления посещаемости администратором.
     *
     * @return void
     */
    public function test_admin_can_manually_add_attendance()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'group' => 'Т-192',
        ]);
        
        $event = Event::factory()->create([
            'title' => 'Тестовое мероприятие',
            'date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($admin)->get('/events/attendance/manual');
        $response->assertStatus(200);

        $attendanceData = [
            'event_id' => $event->id,
            'user_id' => $student->id,
        ];

        $response = $this->actingAs($admin)->post('/events/attendance/manual', $attendanceData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'user_id' => $student->id,
            'event_id' => $event->id,
        ]);
    }
}
