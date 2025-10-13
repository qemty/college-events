<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест просмотра страницы профиля.
     *
     * @return void
     */
    public function test_profile_page_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    /**
     * Тест обновления информации профиля.
     *
     * @return void
     */
    public function test_profile_information_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'Новое Имя',
            'email' => 'new-email@example.com',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $user->refresh();
        $this->assertSame('Новое Имя', $user->name);
        $this->assertSame('new-email@example.com', $user->email);
    }

    /**
     * Тест обновления пароля.
     *
     * @return void
     */
    public function test_password_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
    }

    /**
     * Тест валидации при обновлении пароля.
     *
     * @return void
     */
    public function test_password_validation()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    /**
     * Тест обновления настроек уведомлений.
     *
     * @return void
     */
    public function test_notification_settings_can_be_updated()
    {
        $user = User::factory()->create([
            'email_notifications' => true,
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'email_notifications' => false,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $user->refresh();
        $this->assertFalse($user->email_notifications);
    }

    /**
     * Тест обновления групп куратора.
     *
     * @return void
     */
    public function test_curator_can_update_groups()
    {
        $curator = User::factory()->create([
            'role' => 'curator',
            'curator_groups' => ['Т-192'],
        ]);

        $response = $this->actingAs($curator)->patch('/profile', [
            'name' => $curator->name,
            'email' => $curator->email,
            'curator_groups' => ['Т-192', 'Т-193'],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $curator->refresh();
        $this->assertEquals(['Т-192', 'Т-193'], $curator->curator_groups);
    }

    /**
     * Тест запрета обновления групп для студента.
     *
     * @return void
     */
    public function test_student_cannot_update_groups()
    {
        $student = User::factory()->create([
            'role' => 'student',
            'group' => 'Т-192',
        ]);

        $response = $this->actingAs($student)->patch('/profile', [
            'name' => $student->name,
            'email' => $student->email,
            'group' => 'Т-193', // Студент не должен иметь возможность менять группу
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $student->refresh();
        $this->assertEquals('Т-192', $student->group); // Группа не должна измениться
    }
}
