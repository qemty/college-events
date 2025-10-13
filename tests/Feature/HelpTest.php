<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест доступа к главной странице справки.
     *
     * @return void
     */
    public function test_help_index_page_is_accessible()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/help');

        $response->assertStatus(200);
        $response->assertSee('Справочная система');
    }

    /**
     * Тест доступа к разделу справки для администратора.
     *
     * @return void
     */
    public function test_admin_help_page_is_accessible_for_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/help/admin');

        $response->assertStatus(200);
        $response->assertSee('Справка для администратора');
    }

    /**
     * Тест запрета доступа к разделу справки для администратора другими ролями.
     *
     * @return void
     */
    public function test_admin_help_page_is_not_accessible_for_non_admin()
    {
        $curator = User::factory()->create(['role' => 'curator', 'curator_groups' => ['Т-192']]);
        $student = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);

        $this->actingAs($curator)->get('/help/admin')->assertStatus(403);
        $this->actingAs($student)->get('/help/admin')->assertStatus(403);
    }

    /**
     * Тест доступа к разделу справки для куратора.
     *
     * @return void
     */
    public function test_curator_help_page_is_accessible_for_admin_and_curator()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $curator = User::factory()->create(['role' => 'curator', 'curator_groups' => ['Т-192']]);
        $student = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);

        $this->actingAs($admin)->get('/help/curator')->assertStatus(200);
        $this->actingAs($curator)->get('/help/curator')->assertStatus(200);
        $this->actingAs($student)->get('/help/curator')->assertStatus(403);
    }

    /**
     * Тест доступа к разделу справки для студента.
     *
     * @return void
     */
    public function test_student_help_page_is_accessible_for_all()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $curator = User::factory()->create(['role' => 'curator', 'curator_groups' => ['Т-192']]);
        $student = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);

        $this->actingAs($admin)->get('/help/student')->assertStatus(200);
        $this->actingAs($curator)->get('/help/student')->assertStatus(200);
        $this->actingAs($student)->get('/help/student')->assertStatus(200);
    }

    /**
     * Тест доступа к разделу справки по QR-кодам.
     *
     * @return void
     */
    public function test_qr_codes_help_page_is_accessible()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/help/qr-codes');

        $response->assertStatus(200);
        $response->assertSee('QR-коды');
    }

    /**
     * Тест доступа к разделу справки по отчетам.
     *
     * @return void
     */
    public function test_reports_help_page_is_accessible()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/help/reports');

        $response->assertStatus(200);
        $response->assertSee('Отчеты');
    }

    /**
     * Тест доступа к разделу справки по экспорту данных.
     *
     * @return void
     */
    public function test_export_help_page_is_accessible()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/help/export');

        $response->assertStatus(200);
        $response->assertSee('Экспорт данных');
    }
}
