<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Services\LineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketStaffAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_a_ticket_to_staff(): void
    {
        [$admin, $staff] = $this->createAdminAndStaff();
        $ticket = $this->createTicket(User::factory()->create(['role_id' => null]));

        $this->actingAs($admin)
            ->patch(route('tickets.assign-staff', $ticket), ['assigned_staff_id' => $staff->id])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_staff_id' => $staff->id,
        ]);
    }

    public function test_admin_ticket_submission_does_not_send_a_line_alert(): void
    {
        [$admin] = $this->createAdminAndStaff();
        $category = Category::create(['category_name' => 'ทดสอบ']);
        config(['services.line.admin_id' => 'line-admin-id']);

        $lineService = \Mockery::mock(LineService::class);
        $lineService->shouldNotReceive('sendPush');
        $this->app->instance(LineService::class, $lineService);

        $this->actingAs($admin)
            ->post(route('tickets.store'), [
                'category_id' => $category->id,
                'location' => 'ห้องทดสอบ',
                'symptom' => 'ทดสอบการแจ้งซ่อม',
            ])
            ->assertRedirect();
    }

    public function test_non_admin_ticket_submission_still_sends_a_line_alert(): void
    {
        $user = User::factory()->create(['role_id' => null]);
        $category = Category::create(['category_name' => 'ทดสอบ']);
        config(['services.line.admin_id' => 'line-admin-id']);

        $lineService = \Mockery::mock(LineService::class);
        $lineService->shouldReceive('sendPush')
            ->once()
            ->with('line-admin-id', \Mockery::type('string'))
            ->andReturn(true);
        $this->app->instance(LineService::class, $lineService);

        $this->actingAs($user)
            ->post(route('tickets.store'), [
                'category_id' => $category->id,
                'location' => 'ห้องทดสอบ',
                'symptom' => 'ทดสอบการแจ้งซ่อม',
            ])
            ->assertRedirect();
    }

    public function test_admin_can_change_a_users_role_to_staff(): void
    {
        [$admin, $staff] = $this->createAdminAndStaff();
        $targetUser = User::factory()->create([
            'username' => 'role-target',
            'role_id' => Role::where('name', 'user')->value('id'),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $targetUser), [
                'name' => $targetUser->name,
                'username' => $targetUser->username,
                'phone' => $targetUser->phone,
                'department_id' => $targetUser->department_id,
                'role_id' => $staff->role_id,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'role_id' => $staff->role_id,
        ]);
    }

    public function test_staff_can_view_assigned_tickets_and_unassigned_pending_tickets(): void
    {
        [, $staff] = $this->createAdminAndStaff();
        $reporter = User::factory()->create(['role_id' => null]);
        $assignedTicket = $this->createTicket($reporter, ['assigned_staff_id' => $staff->id]);
        $unassignedTicket = $this->createTicket($reporter);

        $this->actingAs($staff)
            ->get(route('tickets.show', $assignedTicket))
            ->assertOk();

        $this->get(route('tickets.index'))
            ->assertOk()
            ->assertSee($assignedTicket->ticket_no)
            ->assertSee($unassignedTicket->ticket_no)
            ->assertSee(route('tickets.claim', $unassignedTicket->id), false)
            ->assertSee('รับงานนี้')
            ->assertDontSee(route('tickets.claim', $assignedTicket->id), false);

        $this->actingAs($staff)
            ->get(route('tickets.show', $unassignedTicket))
            ->assertOk();
    }

    public function test_staff_can_claim_an_unassigned_pending_ticket_without_changing_its_status(): void
    {
        [, $staff] = $this->createAdminAndStaff();
        $reporter = User::factory()->create(['role_id' => null]);
        $ticket = $this->createTicket($reporter);

        $this->actingAs($staff)
            ->patch(route('tickets.claim', $ticket))
            ->assertRedirect()
            ->assertSessionHas('success', 'รับผิดชอบรายการแจ้งซ่อมเรียบร้อยแล้ว');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_staff_id' => $staff->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $reporter->id,
            'ticket_id' => $ticket->id,
        ]);
    }

    public function test_only_the_first_staff_member_can_claim_an_unassigned_ticket(): void
    {
        [, $firstStaff] = $this->createAdminAndStaff();
        [, $secondStaff] = $this->createAdminAndStaff('other');
        $ticket = $this->createTicket(User::factory()->create(['role_id' => null]));

        $this->actingAs($firstStaff)
            ->patch(route('tickets.claim', $ticket))
            ->assertSessionHas('success');

        $this->actingAs($secondStaff)
            ->patch(route('tickets.claim', $ticket))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_staff_id' => $firstStaff->id,
        ]);
    }

    public function test_non_staff_cannot_claim_an_unassigned_ticket(): void
    {
        $user = User::factory()->create(['role_id' => null]);
        $ticket = $this->createTicket($user);

        $this->actingAs($user)
            ->patch(route('tickets.claim', $ticket))
            ->assertForbidden();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'assigned_staff_id' => null,
        ]);
    }

    public function test_search_matches_tickets_in_multiple_categories_with_the_same_term(): void
    {
        [$admin] = $this->createAdminAndStaff();
        $reporter = User::factory()->create(['role_id' => null]);
        $firstCategory = Category::create(['category_name' => 'ซ่อมคอมพิวเตอร์']);
        $secondCategory = Category::create(['category_name' => 'ซ่อมเครื่องพิมพ์']);
        $firstTicket = $this->createTicket($reporter, [
            'category_id' => $firstCategory->id,
            'ticket_no' => 'SEARCH-COMPUTER',
        ]);
        $secondTicket = $this->createTicket($reporter, [
            'category_id' => $secondCategory->id,
            'ticket_no' => 'SEARCH-PRINTER',
        ]);

        $this->actingAs($admin)
            ->get(route('tickets.index', ['search' => 'ซ่อม']))
            ->assertOk()
            ->assertSee($firstTicket->ticket_no)
            ->assertSee($secondTicket->ticket_no);
    }

    public function test_assigned_staff_can_progress_and_complete_their_ticket(): void
    {
        [, $staff] = $this->createAdminAndStaff();
        $ticket = $this->createTicket(User::factory()->create(['role_id' => null]), [
            'assigned_staff_id' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->patch(route('tickets.update-status', $ticket), ['status' => 'in_progress'])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'in_progress']);

        $this->patch(route('tickets.update-status', $ticket), ['status' => 'completed'])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'completed']);
    }

    public function test_assigned_staff_can_set_and_view_the_repair_method(): void
    {
        [, $staff] = $this->createAdminAndStaff();
        $ticket = $this->createTicket(User::factory()->create(['role_id' => null]), [
            'assigned_staff_id' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->patch(route('tickets.update-status', $ticket), [
                'status' => 'in_progress',
                'repair_method' => 'external',
                'external_repair_location' => 'ร้านซ่อมทดสอบ',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
            'repair_method' => 'external',
            'external_repair_location' => 'ร้านซ่อมทดสอบ',
        ]);

        $this->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('ส่งซ่อมภายนอก')
            ->assertSee('ร้านซ่อมทดสอบ')
            ->assertSee('ยังไม่ได้รับของคืน');

        $this->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('ส่งซ่อมภายนอก');
    }

    public function test_selecting_external_repair_automatically_sets_status_to_in_progress(): void
    {
        [$admin, $staff] = $this->createAdminAndStaff();
        $ticket = $this->createTicket(User::factory()->create(['role_id' => null]));

        $this->actingAs($admin)
            ->patch(route('tickets.update-status', $ticket), [
                'status' => 'pending',
                'repair_method' => 'external',
                'external_repair_location' => 'บริษัทซ่อมภายนอก',
                'assigned_staff_id' => $staff->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'อัปเดตสถานะการแจ้งซ่อมสำเร็จ');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
            'repair_method' => 'external',
            'external_repair_location' => 'บริษัทซ่อมภายนอก',
            'assigned_staff_id' => $staff->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $staff->id,
            'ticket_id' => $ticket->id,
        ]);
    }

    public function test_admin_must_assign_an_internal_coordinator_for_external_repair(): void
    {
        [$admin] = $this->createAdminAndStaff();
        $ticket = $this->createTicket(User::factory()->create(['role_id' => null]));

        $this->actingAs($admin)
            ->from(route('tickets.show', $ticket))
            ->patch(route('tickets.update-status', $ticket), [
                'status' => 'pending',
                'repair_method' => 'external',
            ])
            ->assertSessionHasErrors('assigned_staff_id');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'pending',
            'repair_method' => null,
            'assigned_staff_id' => null,
        ]);
    }

    public function test_external_repair_cannot_be_completed_without_a_return_date(): void
    {
        [, $staff] = $this->createAdminAndStaff();
        $ticket = $this->createTicket(User::factory()->create(['role_id' => null]), [
            'assigned_staff_id' => $staff->id,
            'status' => 'in_progress',
            'repair_method' => 'external',
            'external_repair_location' => 'ร้านซ่อมทดสอบ',
        ]);

        $this->actingAs($staff)
            ->from(route('tickets.show', $ticket))
            ->patch(route('tickets.update-status', $ticket), [
                'status' => 'completed',
                'repair_method' => 'external',
                'external_repair_location' => 'ร้านซ่อมทดสอบ',
                'external_repair_returned_at' => '',
            ])
            ->assertSessionHasErrors('external_repair_returned_at');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
            'external_repair_returned_at' => null,
        ]);
    }

    public function test_staff_can_record_return_date_and_complete_external_repair(): void
    {
        [, $staff] = $this->createAdminAndStaff();
        $ticket = $this->createTicket(User::factory()->create(['role_id' => null]), [
            'assigned_staff_id' => $staff->id,
            'status' => 'in_progress',
            'repair_method' => 'external',
            'external_repair_location' => 'ร้านซ่อมทดสอบ',
        ]);
        $returnedAt = now()->toDateString();

        $this->actingAs($staff)
            ->patch(route('tickets.update-status', $ticket), [
                'status' => 'completed',
                'repair_method' => 'external',
                'external_repair_location' => 'ร้านซ่อมทดสอบ',
                'external_repair_returned_at' => $returnedAt,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'completed',
            'external_repair_location' => 'ร้านซ่อมทดสอบ',
            'external_repair_returned_at' => $returnedAt,
        ]);
    }

    public function test_repair_method_must_be_one_of_the_supported_options(): void
    {
        [, $staff] = $this->createAdminAndStaff();
        $ticket = $this->createTicket(User::factory()->create(['role_id' => null]), [
            'assigned_staff_id' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->from(route('tickets.show', $ticket))
            ->patch(route('tickets.update-status', $ticket), [
                'status' => 'in_progress',
                'repair_method' => 'unknown',
            ])
            ->assertSessionHasErrors('repair_method');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'pending',
            'repair_method' => null,
        ]);
    }

    public function test_staff_cannot_update_a_ticket_assigned_to_someone_else(): void
    {
        [, $staff] = $this->createAdminAndStaff();
        [, $otherStaff] = $this->createAdminAndStaff('other');
        $ticket = $this->createTicket(User::factory()->create(['role_id' => null]), [
            'assigned_staff_id' => $otherStaff->id,
        ]);

        $this->actingAs($staff)
            ->patch(route('tickets.update-status', $ticket), ['status' => 'in_progress'])
            ->assertForbidden();

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'pending']);
    }

    public function test_staff_cannot_skip_directly_from_pending_to_completed(): void
    {
        [, $staff] = $this->createAdminAndStaff();
        $ticket = $this->createTicket(User::factory()->create(['role_id' => null]), [
            'assigned_staff_id' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->from(route('tickets.show', $ticket))
            ->patch(route('tickets.update-status', $ticket), ['status' => 'completed'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'pending']);
    }

    private function createAdminAndStaff(string $suffix = ''): array
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['guard_name' => 'web']);
        $staffRole = Role::firstOrCreate(['name' => 'staff'], ['guard_name' => 'web']);

        $admin = User::factory()->create([
            'username' => 'admin'.$suffix,
            'role_id' => $adminRole->id,
        ]);
        $staff = User::factory()->create([
            'username' => 'staff'.$suffix,
            'role_id' => $staffRole->id,
        ]);

        return [$admin, $staff];
    }

    private function createTicket(User $reporter, array $attributes = []): Ticket
    {
        $category = Category::create(['category_name' => 'ทดสอบ']);

        return Ticket::create(array_merge([
            'ticket_no' => 'TEST-'.$reporter->id.'-'.uniqid(),
            'user_id' => $reporter->id,
            'category_id' => $category->id,
            'location' => 'ห้องทดสอบ',
            'symptom' => 'ทดสอบการมอบหมาย',
            'status' => 'pending',
        ], $attributes));
    }
}
