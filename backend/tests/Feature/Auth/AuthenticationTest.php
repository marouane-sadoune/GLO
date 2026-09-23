<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_login_returns_the_user_with_role_permissions_and_scope(): void
    {
        $user = User::factory()->superAdmin()->create([
            'email' => 'admin@glo.ma',
            'password' => 'password',
        ]);

        $response = $this->loginAsSpa([
            'email' => 'admin@glo.ma',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.email', 'admin@glo.ma')
            ->assertJsonPath('data.role', UserRole::SUPER_ADMIN->value)
            ->assertJsonPath('data.department_id', null)
            ->assertJsonPath('data.establishment_id', null)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'active',
                    'role',
                    'permissions',
                    'department_id',
                    'establishment_id',
                    'department',
                    'establishment',
                    'last_login_at',
                ],
            ]);

        $this->assertContains('requests.approve', $response->json('data.permissions'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->superAdmin()->create([
            'email' => 'admin@glo.ma',
            'password' => 'password',
        ]);

        $this->loginAsSpa([
            'email' => 'admin@glo.ma',
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_rejects_inactive_users(): void
    {
        User::factory()->inactive()->superAdmin()->create([
            'email' => 'disabled@glo.ma',
            'password' => 'password',
        ]);

        $this->loginAsSpa([
            'email' => 'disabled@glo.ma',
            'password' => 'password',
        ])->assertForbidden()
            ->assertJsonPath('message', 'Ce compte est désactivé.');

        $this->assertGuest();
    }

    public function test_me_requires_authentication(): void
    {
        $this->fromFrontend()
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }

    public function test_me_returns_the_authenticated_user(): void
    {
        $user = User::factory()->departmentAdmin()->create();

        $this->actingAsUser($user)
            ->fromFrontend()
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.role', UserRole::DP_AGENT->value)
            ->assertJsonPath('data.department_id', $user->department_id);
    }

    public function test_inactive_authenticated_user_is_blocked_from_me(): void
    {
        $user = User::factory()->superAdmin()->create(['active' => false]);

        $this->actingAsUser($user)
            ->fromFrontend()
            ->getJson('/api/v1/me')
            ->assertForbidden();
    }

    public function test_user_can_change_their_own_password(): void
    {
        $user = User::factory()->superAdmin()->create(['password' => 'old-password']);

        $this->actingAsUser($user)
            ->fromFrontend()
            ->putJson('/api/v1/me/password', [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertOk();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_changing_password_requires_the_correct_current_password(): void
    {
        $user = User::factory()->superAdmin()->create(['password' => 'old-password']);

        $this->actingAsUser($user)
            ->fromFrontend()
            ->putJson('/api/v1/me/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_logout_clears_the_session(): void
    {
        $user = User::factory()->superAdmin()->create([
            'email' => 'admin@glo.ma',
            'password' => 'password',
        ]);

        $this->loginAsSpa([
            'email' => 'admin@glo.ma',
            'password' => 'password',
        ])->assertOk();

        $this->fromFrontend()
            ->postJson('/api/v1/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Déconnexion réussie.');

        // The same Application instance is reused across HTTP calls in tests, so the
        // in-memory guard can still hold the user after logout. Forget it and prove
        // the session no longer re-authenticates (this is what the browser relies on).
        $this->app['auth']->forgetGuards();

        $this->fromFrontend()
            ->getJson('/api/v1/me')
            ->assertUnauthorized();
    }
}
