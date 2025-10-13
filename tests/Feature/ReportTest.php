<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест доступа к странице отчетов для администратора.
     *
     * @return void
     */
    public function test_admin_can_access_reports()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/reports');
        
        $response->assertStatus(200);
        $response->assertSee('Отчеты');
        $response->assertSee('Посещаемость по группам');
        $response->assertSee('Посещаемость по студентам');
    }

    /**
     * Тест доступа к странице отчетов для куратора.
     *
     * @return void
     */
    public function test_curator_can_access_reports()
    {
        $curator = User::factory()->create([
            'role' => 'curator',
            'curator_groups' => ['Т-192'],
        ]);

        $response = $this->actingAs($curator)->get('/reports');
        
        $response->assertStatus(200);
        $response->assertSee('Отчеты');
        $response->assertSee('Посещаемость по группам');
        $response->assertSee('Посещаемость по студентам');
    }

    /**
     * Тест запрета доступа к странице отчетов для студента.
     *
     * @return void
     */
    public function test_student_cannot_access_reports()
    {
        $student = User::factory()->create([
            'role' => 'student',
            'group' => 'Т-192',
        ]);

        $response = $this->actingAs($student)->get('/reports');
        
        $response->assertStatus(403);
    }

    /**
     * Тест формирования отчета по посещаемости групп.
     *
     * @return void
     */
    public function test_admin_can_generate_group_attendance_report()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Создаем студентов из разных групп
        $student1 = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);
        $student2 = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);
        $student3 = User::factory()->create(['role' => 'student', 'group' => 'Т-193']);
        
        // Создаем мероприятия
        $event1 = Event::factory()->create([
            'title' => 'Мероприятие 1',
            'date' => now()->subDays(5)->format('Y-m-d'),
        ]);
        
        $event2 = Event::factory()->create([
            'title' => 'Мероприятие 2',
            'date' => now()->subDays(3)->format('Y-m-d'),
        ]);
        
        // Создаем регистрации и посещения
        Registration::create(['user_id' => $student1->id, 'event_id' => $event1->id]);
        Registration::create(['user_id' => $student2->id, 'event_id' => $event1->id]);
        Registration::create(['user_id' => $student3->id, 'event_id' => $event1->id]);
        Registration::create(['user_id' => $student1->id, 'event_id' => $event2->id]);
        
        Attendance::create(['user_id' => $student1->id, 'event_id' => $event1->id]);
        Attendance::create(['user_id' => $student2->id, 'event_id' => $event1->id]);
        Attendance::create(['user_id' => $student1->id, 'event_id' => $event2->id]);

        // Формируем отчет по группам
        $response = $this->actingAs($admin)->get('/reports/group-attendance?start_date=' . 
            now()->subDays(10)->format('Y-m-d') . '&end_date=' . now()->format('Y-m-d'));
        
        $response->assertStatus(200);
        $response->assertSee('Т-192');
        $response->assertSee('Т-193');
    }

    /**
     * Тест формирования отчета по посещаемости студентов.
     *
     * @return void
     */
    public function test_admin_can_generate_student_attendance_report()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Создаем студентов
        $student1 = User::factory()->create([
            'name' => 'Иванов Иван',
            'role' => 'student', 
            'group' => 'Т-192'
        ]);
        
        $student2 = User::factory()->create([
            'name' => 'Петров Петр',
            'role' => 'student', 
            'group' => 'Т-192'
        ]);
        
        // Создаем мероприятия
        $event1 = Event::factory()->create([
            'title' => 'Мероприятие 1',
            'date' => now()->subDays(5)->format('Y-m-d'),
        ]);
        
        $event2 = Event::factory()->create([
            'title' => 'Мероприятие 2',
            'date' => now()->subDays(3)->format('Y-m-d'),
        ]);
        
        // Создаем регистрации и посещения
        Registration::create(['user_id' => $student1->id, 'event_id' => $event1->id]);
        Registration::create(['user_id' => $student2->id, 'event_id' => $event1->id]);
        Registration::create(['user_id' => $student1->id, 'event_id' => $event2->id]);
        
        Attendance::create(['user_id' => $student1->id, 'event_id' => $event1->id]);
        Attendance::create(['user_id' => $student1->id, 'event_id' => $event2->id]);

        // Формируем отчет по студентам
        $response = $this->actingAs($admin)->get('/reports/student-attendance?group=Т-192&start_date=' . 
            now()->subDays(10)->format('Y-m-d') . '&end_date=' . now()->format('Y-m-d'));
        
        $response->assertStatus(200);
        $response->assertSee('Иванов Иван');
        $response->assertSee('Петров Петр');
    }

    /**
     * Тест экспорта отчета в CSV.
     *
     * @return void
     */
    public function test_admin_can_export_report_to_csv()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Создаем студентов и мероприятия
        $student = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);
        $event = Event::factory()->create(['title' => 'Тестовое мероприятие']);
        
        Attendance::create(['user_id' => $student->id, 'event_id' => $event->id]);

        $response = $this->actingAs($admin)->get('/reports/export-csv?group=Т-192&start_date=' . 
            now()->subDays(10)->format('Y-m-d') . '&end_date=' . now()->format('Y-m-d'));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=attendance_report.csv');
    }

    /**
     * Тест экспорта отчета в Excel.
     *
     * @return void
     */
    public function test_admin_can_export_report_to_excel()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Создаем студентов и мероприятия
        $student = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);
        $event = Event::factory()->create(['title' => 'Тестовое мероприятие']);
        
        Attendance::create(['user_id' => $student->id, 'event_id' => $event->id]);

        $response = $this->actingAs($admin)->get('/reports/export-excel?group=Т-192&start_date=' . 
            now()->subDays(10)->format('Y-m-d') . '&end_date=' . now()->format('Y-m-d'));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename=attendance_report.xlsx');
    }

    /**
     * Тест экспорта отчета в PDF.
     *
     * @return void
     */
    public function test_admin_can_export_report_to_pdf()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Создаем студентов и мероприятия
        $student = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);
        $event = Event::factory()->create(['title' => 'Тестовое мероприятие']);
        
        Attendance::create(['user_id' => $student->id, 'event_id' => $event->id]);

        $response = $this->actingAs($admin)->get('/reports/export-pdf?group=Т-192&start_date=' . 
            now()->subDays(10)->format('Y-m-d') . '&end_date=' . now()->format('Y-m-d'));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'attachment; filename=attendance_report.pdf');
    }
}
