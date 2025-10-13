<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Invitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тест создания приглашения администратором.
     *
     * @return void
     */
    public function test_admin_can_create_invitation()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/invitations/create');
        $response->assertStatus(200);

        $invitationData = [
            'role' => 'curator',
            'group' => 'Т-192',
            'expires_at' => now()->addDays(7)->format('Y-m-d'),
        ];

        $response = $this->actingAs($admin)->post('/invitations', $invitationData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('invitations', [
            'role' => 'curator',
            'group' => 'Т-192',
            'created_by' => $admin->id,
        ]);
    }

    /**
     * Тест создания приглашения куратором (только для студентов).
     *
     * @return void
     */
    public function test_curator_can_create_invitation_for_students_only()
    {
        $curator = User::factory()->create([
            'role' => 'curator',
            'curator_groups' => ['Т-192'],
        ]);

        $response = $this->actingAs($curator)->get('/invitations/create');
        $response->assertStatus(200);

        // Приглашение для студента (должно быть успешным)
        $invitationData = [
            'role' => 'student',
            'group' => 'Т-192',
            'expires_at' => now()->addDays(7)->format('Y-m-d'),
        ];

        $response = $this->actingAs($curator)->post('/invitations', $invitationData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('invitations', [
            'role' => 'student',
            'group' => 'Т-192',
            'created_by' => $curator->id,
        ]);

        // Приглашение для куратора (должно быть запрещено)
        $invitationData = [
            'role' => 'curator',
            'group' => 'Т-193',
            'expires_at' => now()->addDays(7)->format('Y-m-d'),
        ];

        $response = $this->actingAs($curator)->post('/invitations', $invitationData);
        
        $response->assertStatus(403);
        $this->assertDatabaseMissing('invitations', [
            'role' => 'curator',
            'group' => 'Т-193',
            'created_by' => $curator->id,
        ]);
    }

    /**
     * Тест запрета создания приглашения студентом.
     *
     * @return void
     */
    public function test_student_cannot_create_invitation()
    {
        $student = User::factory()->create(['role' => 'student', 'group' => 'Т-192']);

        $response = $this->actingAs($student)->get('/invitations/create');
        $response->assertStatus(403);

        $invitationData = [
            'role' => 'student',
            'group' => 'Т-192',
            'expires_at' => now()->addDays(7)->format('Y-m-d'),
        ];

        $response = $this->actingAs($student)->post('/invitations', $invitationData);
        $response->assertStatus(403);
    }

    /**
     * Тест регистрации по приглашению.
     *
     * @return void
     */
    public function test_registration_with_invitation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $invitation = Invitation::create([
            'token' => Str::random(64),
            'role' => 'student',
            'group' => 'Т-192',
            'created_by' => $admin->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->get('/register/' . $invitation->token);
        $response->assertStatus(200);
        $response->assertSee('Т-192'); // Группа должна быть предзаполнена

        $userData = [
            'name' => 'Тестовый Студент',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'invitation_token' => $invitation->token,
        ];

        $response = $this->post('/register', $userData);
        
        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'name' => 'Тестовый Студент',
            'email' => 'test@example.com',
            'role' => 'student',
            'group' => 'Т-192',
        ]);
        
        // Проверка, что приглашение помечено как использованное
        $this->assertDatabaseHas('invitations', [
            'id' => $invitation->id,
            'used' => true,
        ]);
    }

    /**
     * Тест просмотра списка приглашений администратором.
     *
     * @return void
     */
    public function test_admin_can_view_all_invitations()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $curator = User::factory()->create(['role' => 'curator', 'curator_groups' => ['Т-192']]);
        
        $invitation1 = Invitation::create([
            'token' => Str::random(64),
            'role' => 'curator',
            'created_by' => $admin->id,
            'expires_at' => now()->addDays(7),
        ]);
        
        $invitation2 = Invitation::create([
            'token' => Str::random(64),
            'role' => 'student',
            'group' => 'Т-192',
            'created_by' => $curator->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($admin)->get('/invitations');
        
        $response->assertStatus(200);
        $response->assertSee('curator');
        $response->assertSee('student');
        $response->assertSee('Т-192');
    }

    /**
     * Тест просмотра списка приглашений куратором (только свои).
     *
     * @return void
     */
    public function test_curator_can_view_only_own_invitations()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $curator = User::factory()->create(['role' => 'curator', 'curator_groups' => ['Т-192']]);
        
        $invitation1 = Invitation::create([
            'token' => Str::random(64),
            'role' => 'curator',
            'created_by' => $admin->id,
            'expires_at' => now()->addDays(7),
        ]);
        
        $invitation2 = Invitation::create([
            'token' => Str::random(64),
            'role' => 'student',
            'group' => 'Т-192',
            'created_by' => $curator->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($curator)->get('/invitations');
        
        $response->assertStatus(200);
        $response->assertDontSee('curator'); // Не должен видеть приглашения для кураторов
        $response->assertSee('student');
        $response->assertSee('Т-192');
    }

    /**
     * Тест удаления приглашения администратором.
     *
     * @return void
     */
    public function test_admin_can_delete_invitation()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        $invitation = Invitation::create([
            'token' => Str::random(64),
            'role' => 'student',
            'group' => 'Т-192',
            'created_by' => $admin->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($admin)->delete('/invitations/' . $invitation->id);
        
        $response->assertRedirect();
        $this->assertDatabaseMissing('invitations', [
            'id' => $invitation->id,
        ]);
    }
}
