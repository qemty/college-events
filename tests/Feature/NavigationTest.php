<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест доступности навигационных элементов для администратора.
     *
     * @return void
     */
    public function test_admin_can_see_all_navigation_items()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Мероприятия');
        $response->assertSee('Пользователи');
        $response->assertSee('Отчеты');
        $response->assertSee('Справка');
    }

    /**
     * Тест доступности навигационных элементов для куратора.
     *
     * @return void
     */
    public function test_curator_can_see_limited_navigation_items()
    {
        $curator = User::factory()->create([
            'role' => 'curator',
            'curator_groups' => ['Т-192'],
        ]);

        $response = $this->actingAs($curator)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Мероприятия');
        $response->assertSee('Отчеты');
        $response->assertSee('Справка');
        $response->assertDontSee('Пользователи');
    }

    /**
     * Тест доступности навигационных элементов для студента.
     *
     * @return void
     */
    public function test_student_can_see_limited_navigation_items()
    {
        $student = User::factory()->create([
            'role' => 'student',
            'group' => 'Т-192',
        ]);

        $response = $this->actingAs($student)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Мероприятия');
        $response->assertSee('Справка');
        $response->assertDontSee('Пользователи');
        $response->assertDontSee('Отчеты');
    }

    /**
     * Тест редиректа неавторизованных пользователей.
     *
     * @return void
     */
    public function test_unauthenticated_users_are_redirected_to_login()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Тест активного состояния вкладки Dashboard.
     *
     * @return void
     */
    public function test_dashboard_tab_is_active_when_on_dashboard_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('class="inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out"', false);
    }

    /**
     * Тест доступа к разделу пользователей для администратора.
     *
     * @return void
     */
    public function test_admin_can_access_users_page()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200);
    }

    /**
     * Тест запрета доступа к разделу пользователей для куратора.
     *
     * @return void
     */
    public function test_curator_cannot_access_users_page()
    {
        $curator = User::factory()->create([
            'role' => 'curator',
            'curator_groups' => ['Т-192'],
        ]);

        $response = $this->actingAs($curator)->get('/users');

        $response->assertStatus(403);
    }

    /**
     * Тест запрета доступа к разделу пользователей для студента.
     *
     * @return void
     */
    public function test_student_cannot_access_users_page()
    {
        $student = User::factory()->create([
            'role' => 'student',
            'group' => 'Т-192',
        ]);

        $response = $this->actingAs($student)->get('/users');

        $response->assertStatus(403);
    }

    /**
     * Тест доступа к разделу отчетов для администратора.
     *
     * @return void
     */
    public function test_admin_can_access_reports_page()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/reports');

        $response->assertStatus(200);
    }

    /**
     * Тест доступа к разделу отчетов для куратора.
     *
     * @return void
     */
    public function test_curator_can_access_reports_page()
    {
        $curator = User::factory()->create([
            'role' => 'curator',
            'curator_groups' => ['Т-192'],
        ]);

        $response = $this->actingAs($curator)->get('/reports');

        $response->assertStatus(200);
    }

    /**
     * Тест запрета доступа к разделу отчетов для студента.
     *
     * @return void
     */
    public function test_student_cannot_access_reports_page()
    {
        $student = User::factory()->create([
            'role' => 'student',
            'group' => 'Т-192',
        ]);

        $response = $this->actingAs($student)->get('/reports');

        $response->assertStatus(403);
    }

    /**
     * Тест доступа к разделу справки для всех пользователей.
     *
     * @return void
     */
    public function test_all_users_can_access_help_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $curator = User::factory()->create(['role' => 'curator', 'curator_groups' => ['Т-192']]);
        $student = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);

        $this->actingAs($admin)->get('/help')->assertStatus(200);
        $this->actingAs($curator)->get('/help')->assertStatus(200);
        $this->actingAs($student)->get('/help')->assertStatus(200);
    }
}
