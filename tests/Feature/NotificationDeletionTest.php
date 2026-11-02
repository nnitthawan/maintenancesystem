<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_soft_delete_their_notification(): void
    {
        $user = User::factory()->create(['role_id' => null]);
        $notification = $this->createNotificationFor($user);

        $response = $this
            ->actingAs($user)
            ->from('/notifications')
            ->delete(route('notifications.destroy', $notification));

        $response
            ->assertRedirect('/notifications')
            ->assertSessionHas('success', 'ลบการแจ้งเตือนแล้ว');

        $this->assertSoftDeleted('notifications', ['id' => $notification->id]);
        $this->assertNull(Notification::find($notification->id));
    }

    public function test_user_cannot_delete_another_users_notification(): void
    {
        $owner = User::factory()->create(['role_id' => null]);
        $otherUser = User::factory()->create(['role_id' => null]);
        $notification = $this->createNotificationFor($owner);

        $this
            ->actingAs($otherUser)
            ->delete(route('notifications.destroy', $notification))
            ->assertForbidden();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'deleted_at' => null,
        ]);
    }

    private function createNotificationFor(User $user): Notification
    {
        $category = Category::create(['category_name' => 'ทดสอบ']);
        $ticket = Ticket::create([
            'ticket_no' => 'TEST-' . $user->id,
            'user_id' => $user->id,
            'category_id' => $category->id,
            'location' => 'ห้องทดสอบ',
            'symptom' => 'ทดสอบการแจ้งเตือน',
        ]);

        return Notification::create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'message' => 'มีรายการแจ้งซ่อมใหม่',
        ]);
    }
}
