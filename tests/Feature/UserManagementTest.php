<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VenueSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
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

    public function test_switch_role_button_visible_in_local_and_hidden_in_production(): void
    {
        // 1. In Local / Dev Environment: Switch Role button should be visible
        config(['app.env' => 'local']);
        $responseDev = $this->actingAs($this->owner)->get(route('owner.dashboard'));
        $responseDev->assertStatus(200);
        $responseDev->assertSee('Switch Role');

        // 2. In Production Environment: Switch Role button must be hidden
        $this->app['env'] = 'production';
        $responseProd = $this->actingAs($this->owner)->get(route('owner.dashboard'));
        $responseProd->assertStatus(200);
        $responseProd->assertDontSee('Switch Role');
    }

    public function test_court_owner_and_system_admin_can_access_user_management(): void
    {
        // Court Owner
        $ownerResp = $this->actingAs($this->owner)->get(route('owner.users.index'));
        $ownerResp->assertStatus(200);
        $ownerResp->assertSee('User Management');
        $ownerResp->assertSee('Registered Accounts');

        // System Admin
        $adminResp = $this->actingAs($this->admin)->get(route('owner.users.index'));
        $adminResp->assertStatus(200);
        $adminResp->assertSee('User Management');
    }

    public function test_regular_client_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->client)->get(route('owner.users.index'));
        $response->assertStatus(403);
    }

    public function test_admin_assistant_without_users_module_access_is_forbidden(): void
    {
        // Assistant default permissions: ['schedule', 'approvals']
        $this->assertFalse($this->assistant->hasModuleAccess('users'));

        $response = $this->actingAs($this->assistant)->get(route('owner.users.index'));
        $response->assertStatus(403);
        $response->assertSee('User Management');
    }

    public function test_admin_assistant_with_users_module_access_can_access_user_management(): void
    {
        // Grant 'users' module access to assistant
        $this->assistant->update([
            'permissions' => ['schedule', 'approvals', 'users'],
        ]);

        $this->assertTrue($this->assistant->hasModuleAccess('users'));

        $response = $this->actingAs($this->assistant)->get(route('owner.users.index'));
        $response->assertStatus(200);
        $response->assertSee('User Management');
        $response->assertSee('Registered Accounts');
    }

    public function test_available_modules_includes_user_management(): void
    {
        $modules = User::availableModules();
        $this->assertArrayHasKey('users', $modules);
        $this->assertEquals('User Management', $modules['users']['name']);
        $this->assertFalse($modules['users']['default']);
    }

    public function test_court_owner_can_create_user_with_different_roles(): void
    {
        $email = 'new_client_' . time() . '@paddlefield.com';

        $response = $this->actingAs($this->owner)->post(route('owner.users.store'), [
            'name' => 'Roberto Carlos',
            'email' => $email,
            'phone' => '+63 918 111 2233',
            'role' => 'client',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('owner.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => $email,
            'role' => 'client',
            'is_active' => 1,
        ]);
    }

    public function test_court_owner_can_create_assistant_with_user_management_module(): void
    {
        $email = 'assistant_manager_' . time() . '@paddlefield.com';

        $response = $this->actingAs($this->owner)->post(route('owner.users.store'), [
            'name' => 'Sara Connor',
            'email' => $email,
            'phone' => '+63 918 333 4455',
            'role' => 'admin_assistant',
            'modules' => ['schedule', 'approvals', 'users'],
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('owner.users.index'));
        $created = User::where('email', $email)->first();
        $this->assertNotNull($created);
        $this->assertTrue($created->hasModuleAccess('users'));
        $this->assertTrue($created->hasModuleAccess('schedule'));
        $this->assertTrue($created->hasModuleAccess('approvals'));
        $this->assertFalse($created->hasModuleAccess('courts'));
    }

    public function test_admin_assistant_cannot_escalate_privileges_to_create_admin_or_owner(): void
    {
        $this->assistant->update([
            'permissions' => ['users'],
        ]);

        $response = $this->actingAs($this->assistant)->post(route('owner.users.store'), [
            'name' => 'Attacker Admin',
            'email' => 'fake_admin@paddlefield.com',
            'role' => 'admin',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'fake_admin@paddlefield.com']);
    }

    public function test_user_management_can_update_user_details(): void
    {
        $target = User::where('role', 'client')->first();

        $response = $this->actingAs($this->owner)->put(route('owner.users.update', $target->id), [
            'name' => 'Updated Name',
            'email' => $target->email,
            'phone' => '+63 999 888 7777',
            'role' => 'client',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('owner.users.index'));
        $this->assertEquals('Updated Name', $target->fresh()->name);
        $this->assertEquals('+63 999 888 7777', $target->fresh()->phone);
    }

    public function test_user_management_toggle_user_status(): void
    {
        $target = User::where('role', 'client')->first();
        $this->assertTrue($target->is_active);

        $response = $this->actingAs($this->owner)->post(route('owner.users.toggle', $target->id));
        $response->assertSessionHas('success');
        $this->assertFalse($target->fresh()->is_active);

        // Cannot deactivate own account
        $selfResp = $this->actingAs($this->owner)->post(route('owner.users.toggle', $this->owner->id));
        $selfResp->assertSessionHas('error');
        $this->assertTrue($this->owner->fresh()->is_active);
    }

    public function test_user_management_delete_user_protection(): void
    {
        $target = User::where('role', 'client')->first();

        $response = $this->actingAs($this->owner)->delete(route('owner.users.destroy', $target->id));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $target->id]);

        // Cannot delete oneself
        $selfDelete = $this->actingAs($this->owner)->delete(route('owner.users.destroy', $this->owner->id));
        $selfDelete->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->owner->id]);

        // Cannot delete primary system admin
        $adminDelete = $this->actingAs($this->owner)->delete(route('owner.users.destroy', $this->admin->id));
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_user_management_reset_password_to_default(): void
    {
        $target = User::where('role', 'client')->first();

        // Target initial password is not PaddleField2026!
        $this->assertFalse(\Illuminate\Support\Facades\Hash::check('PaddleField2026!', $target->password));

        $response = $this->actingAs($this->owner)->post(route('owner.users.reset_password', $target->id));
        $response->assertSessionHas('success');

        // Password should now be PaddleField2026!
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('PaddleField2026!', $target->fresh()->password));

        // Owner cannot reset admin password
        $ownerResetAdmin = $this->actingAs($this->owner)->post(route('owner.users.reset_password', $this->admin->id));
        $ownerResetAdmin->assertForbidden();

        // Assistant cannot reset owner password
        $assistantResetOwner = $this->actingAs($this->assistant)->post(route('owner.users.reset_password', $this->owner->id));
        $assistantResetOwner->assertForbidden();
    }

    public function test_user_management_ajax_deactivate_with_reason(): void
    {
        $target = User::where('role', 'client')->first();
        $this->assertTrue($target->is_active);

        $response = $this->actingAs($this->owner)
            ->postJson(route('owner.users.toggle', $target->id), [
                'is_active' => false,
                'reason' => 'Repeated unpaid reservation holds',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'is_active' => false,
            'status_label' => 'Deactivated',
            'deactivation_reason' => 'Repeated unpaid reservation holds',
        ]);

        $this->assertFalse($target->fresh()->is_active);
        $this->assertEquals('Repeated unpaid reservation holds', $target->fresh()->deactivation_reason);

        // Reactivate via AJAX
        $reactivateResp = $this->actingAs($this->owner)
            ->postJson(route('owner.users.toggle', $target->id), [
                'is_active' => true,
            ]);

        $reactivateResp->assertOk();
        $reactivateResp->assertJson([
            'success' => true,
            'is_active' => true,
            'status_label' => 'Active',
            'deactivation_reason' => null,
        ]);

        $this->assertTrue($target->fresh()->is_active);
        $this->assertNull($target->fresh()->deactivation_reason);
    }

    public function test_user_management_view_has_no_delete_button_and_has_deactivate_modal(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.users.index'));
        $response->assertOk();

        // Ensure confirmDeleteUser is removed
        $response->assertDontSee('confirmDeleteUser(');
        $response->assertDontSee('title="Delete User"');

        // Ensure deactivate modal and status handler exist
        $response->assertSee('deactivateUserModal');
        $response->assertSee('Reason for Deactivation');
        $response->assertSee('handleStatusClick');
    }
}
