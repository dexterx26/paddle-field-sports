<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\User;
use App\Models\VenueSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAssistantManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $admin;
    protected User $client;
    protected User $assistant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->owner = User::where('role', 'court_owner')->first();
        $this->admin = User::where('role', 'admin')->first();
        $this->client = User::where('role', 'client')->first();
        $this->assistant = User::where('role', 'admin_assistant')->first();
    }

    public function test_court_owner_can_access_assistants_management_page(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.assistants.index'));
        $response->assertStatus(200);
        $response->assertSee('Admin Assistants Management');
        $response->assertSee('Maria Santos');
        $response->assertSee('Viewing of Schedule');
        $response->assertSee('Approval of Reservation');
    }

    public function test_admin_assistant_cannot_access_assistants_management_page(): void
    {
        $response = $this->actingAs($this->assistant)->get(route('owner.assistants.index'));
        $response->assertStatus(403);
    }

    public function test_regular_client_cannot_access_owner_portal(): void
    {
        $response = $this->actingAs($this->client)->get(route('owner.dashboard'));
        $response->assertStatus(403);
    }

    public function test_court_owner_can_create_new_assistant_with_default_permissions(): void
    {
        $uniqueEmail = 'new_assistant_' . time() . '@paddlefield.com';

        $response = $this->actingAs($this->owner)->post(route('owner.assistants.store'), [
            'name' => 'Carlos Ramos',
            'email' => $uniqueEmail,
            'phone' => '0918-123-9999',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'modules' => ['schedule', 'approvals'],
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('owner.assistants.index'));

        $this->assertDatabaseHas('users', [
            'email' => $uniqueEmail,
            'role' => 'admin_assistant',
            'court_owner_id' => $this->owner->id,
            'is_active' => 1,
        ]);

        $created = User::where('email', $uniqueEmail)->first();
        $this->assertTrue($created->hasModuleAccess('schedule'));
        $this->assertTrue($created->hasModuleAccess('approvals'));
        $this->assertFalse($created->hasModuleAccess('courts'));
        $this->assertFalse($created->hasModuleAccess('settings'));
    }

    public function test_admin_assistant_with_default_permissions_can_access_schedule_and_approvals(): void
    {
        // Allowed: Schedule / Dashboard
        $response = $this->actingAs($this->assistant)->get(route('owner.dashboard'));
        $response->assertStatus(200);

        // Allowed: All Bookings (Schedule)
        $response = $this->actingAs($this->assistant)->get(route('owner.bookings.index'));
        $response->assertStatus(200);

        // Allowed: Approvals
        $response = $this->actingAs($this->assistant)->get(route('owner.approvals'));
        $response->assertStatus(200);
    }

    public function test_admin_assistant_with_default_permissions_is_blocked_from_unauthorized_modules(): void
    {
        // Forbidden: Courts & Pricing
        $response = $this->actingAs($this->assistant)->get(route('owner.courts.index'));
        $response->assertStatus(403);

        // Forbidden: Website Photos
        $response = $this->actingAs($this->assistant)->get(route('owner.photos.index'));
        $response->assertStatus(403);

        // Forbidden: Settings
        $response = $this->actingAs($this->assistant)->get(route('owner.settings'));
        $response->assertStatus(403);
    }

    public function test_admin_assistant_with_only_approvals_redirects_from_dashboard_to_approvals(): void
    {
        $approvalsOnlyAssistant = User::create([
            'name' => 'Approvals Only Assistant',
            'email' => 'approvals_only_' . time() . '@paddlefield.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_assistant',
            'court_owner_id' => $this->owner->id,
            'permissions' => ['approvals'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($approvalsOnlyAssistant)->get(route('owner.dashboard'));
        $response->assertRedirect(route('owner.approvals'));

        // Can access approvals
        $response = $this->actingAs($approvalsOnlyAssistant)->get(route('owner.approvals'));
        $response->assertStatus(200);

        // Blocked from courts
        $response = $this->actingAs($approvalsOnlyAssistant)->get(route('owner.courts.index'));
        $response->assertStatus(403);
    }

    public function test_court_owner_can_update_assistant_modules(): void
    {
        $testAssistant = User::create([
            'name' => 'To Update Assistant',
            'email' => 'to_update_' . time() . '@paddlefield.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_assistant',
            'court_owner_id' => $this->owner->id,
            'permissions' => ['schedule'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->put(route('owner.assistants.update', $testAssistant->id), [
            'name' => 'Updated Name Assistant',
            'email' => $testAssistant->email,
            'modules' => ['schedule', 'approvals', 'courts'],
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('owner.assistants.index'));

        $testAssistant->refresh();
        $this->assertEquals('Updated Name Assistant', $testAssistant->name);
        $this->assertTrue($testAssistant->hasModuleAccess('courts'));
        $this->assertTrue($testAssistant->hasModuleAccess('approvals'));
    }

    public function test_court_owner_can_toggle_assistant_active_status(): void
    {
        $testAssistant = User::create([
            'name' => 'Toggle Assistant',
            'email' => 'toggle_' . time() . '@paddlefield.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_assistant',
            'court_owner_id' => $this->owner->id,
            'permissions' => ['schedule', 'approvals'],
            'is_active' => true,
        ]);

        // Toggle to inactive
        $this->actingAs($this->owner)->post(route('owner.assistants.toggle', $testAssistant->id));
        $testAssistant->refresh();
        $this->assertFalse($testAssistant->is_active);

        // Inactive assistant should be blocked / logged out
        $response = $this->actingAs($testAssistant)->get(route('owner.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_court_owner_can_delete_assistant(): void
    {
        $testAssistant = User::create([
            'name' => 'To Delete Assistant',
            'email' => 'to_delete_' . time() . '@paddlefield.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_assistant',
            'court_owner_id' => $this->owner->id,
            'permissions' => ['schedule', 'approvals'],
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner)->delete(route('owner.assistants.destroy', $testAssistant->id));
        $response->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $testAssistant->id]);
    }

    public function test_quick_login_supports_admin_assistant(): void
    {
        $response = $this->get(route('quick.login', 'assistant'));
        $response->assertRedirect(route('owner.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('admin_assistant', auth()->user()->role);
    }
}
