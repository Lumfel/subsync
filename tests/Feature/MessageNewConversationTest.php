<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageNewConversationTest extends TestCase
{
    use RefreshDatabase;

    private function resident(): Resident
    {
        return Resident::create(['name' => 'Res', 'email' => 'res@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function officer(): Officer
    {
        return Officer::create(['name' => 'Off', 'email' => 'off@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
    }

    private function as(string $role, $user): void
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();
        $this->actingAs($user, $role);
    }

    public function test_resident_can_list_officers_to_message(): void
    {
        $this->officer();

        $res = $this->actingAs($this->resident(), 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/messages/officers');

        $res->assertOk()->assertJsonFragment(['name' => 'Off']);
    }

    public function test_resident_can_start_a_conversation(): void
    {
        $officer = $this->officer();
        $resident = $this->resident();

        $res = $this->actingAs($resident, 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/messages/start', [
                'title' => 'Gate pass request',
                'recipient_type' => 'officer',
                'recipient_id' => $officer->id,
            ]);

        $res->assertOk()->assertJsonPath('success', true);
        $convId = $res->json('conversation.id');
        $this->assertDatabaseHas('conversations', ['id' => $convId, 'title' => 'Gate pass request']);
        $this->assertDatabaseHas('conv_participants', ['conversation_id' => $convId, 'resident_id' => $resident->id]);
        $this->assertDatabaseHas('conv_participants', ['conversation_id' => $convId, 'officer_id' => $officer->id]);
    }

    public function test_admin_can_start_a_general_conversation(): void
    {
        $admin = Admin::create(['name' => 'A', 'email' => 'a@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);

        $res = $this->withSession(['admin_id' => $admin->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/messages/start', ['title' => 'General notice']);

        $res->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('conversations', ['title' => 'General notice']);
    }

    public function test_staff_can_list_active_residents_to_message(): void
    {
        $active = $this->resident();
        Resident::create(['name' => 'Inactive Res', 'email' => 'inactive@example.test', 'password' => 'Secret@12345', 'status' => 'Inactive']);

        $res = $this->actingAs($this->officer(), 'officer')
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/messages/residents');

        $res->assertOk();
        $names = array_column($res->json(), 'name');
        $this->assertContains($active->name, $names);
        $this->assertNotContains('Inactive Res', $names);
        // Lightweight shape only.
        $this->assertSame(['id', 'name'], array_keys($res->json()[0]));
    }

    public function test_resident_cannot_list_residents_endpoint(): void
    {
        $this->actingAs($this->resident(), 'resident')
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/api/messages/residents')
            ->assertStatus(401);
    }

    public function test_new_conversation_starts_as_a_message_request(): void
    {
        $officer = $this->officer();
        $resident = $this->resident();

        $this->as('resident', $resident);
        $convId = $this->postJson('/api/messages/start', [
            'title' => 'Hi', 'recipient_type' => 'officer', 'recipient_id' => $officer->id,
        ])->assertOk()->json('conversation.id');
        $this->postJson("/api/messages/{$convId}", ['content' => 'Hello'])->assertOk();

        // The initiator sees their thread normally.
        $this->assertContains($convId, array_column($this->getJson('/api/messages/threads')->assertOk()->json(), 'id'));

        // The recipient sees a request, not a thread.
        $this->as('officer', $officer);
        $this->assertContains($convId, array_column($this->getJson('/api/messages/requests')->assertOk()->json(), 'id'));
        $this->assertNotContains($convId, array_column($this->getJson('/api/messages/threads')->assertOk()->json(), 'id'));

        // The recipient can read the request but cannot reply until they accept.
        $this->getJson("/api/messages/{$convId}")->assertOk();
        $this->postJson("/api/messages/{$convId}", ['content' => 'reply'])->assertStatus(403);

        // Accepting moves it into the inbox and allows replies.
        $this->postJson("/api/messages/{$convId}/accept")->assertOk();
        $this->assertContains($convId, array_column($this->getJson('/api/messages/threads')->assertOk()->json(), 'id'));
        $this->postJson("/api/messages/{$convId}", ['content' => 'reply'])->assertOk();
        $this->assertNotContains($convId, array_column($this->getJson('/api/messages/requests')->assertOk()->json(), 'id'));
    }

    public function test_rejecting_a_message_request_deletes_it_for_both(): void
    {
        $officer = $this->officer();
        $resident = $this->resident();

        $this->as('resident', $resident);
        $convId = $this->postJson('/api/messages/start', [
            'title' => 'Spam', 'recipient_type' => 'officer', 'recipient_id' => $officer->id,
        ])->assertOk()->json('conversation.id');
        $this->postJson("/api/messages/{$convId}", ['content' => 'buy now'])->assertOk();

        $this->as('officer', $officer);
        $this->postJson("/api/messages/{$convId}/reject")->assertOk();

        $this->assertDatabaseMissing('conversations', ['id' => $convId]);
        $this->assertDatabaseMissing('messages', ['conversation_id' => $convId]);
        $this->assertDatabaseMissing('conv_participants', ['conversation_id' => $convId]);

        // The initiator no longer sees it either.
        $this->as('resident', $resident);
        $this->assertNotContains($convId, array_column($this->getJson('/api/messages/threads')->assertOk()->json(), 'id'));
    }

    public function test_admin_initiated_request_names_the_sender(): void
    {
        $admin = Admin::create(['name' => 'A', 'email' => 'a2@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
        $resident = $this->resident();

        $this->withSession(['admin_id' => $admin->id])
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/messages/start', ['title' => 'Water bill follow-up', 'resident_id' => $resident->id])
            ->assertOk();

        // The recipient must see WHO is trying to contact them, not "Someone".
        $this->as('resident', $resident);
        $requests = $this->getJson('/api/messages/requests')->assertOk()->json();

        $this->assertCount(1, $requests);
        $this->assertSame('HOA Admin', $requests[0]['from']);
    }

    public function test_accept_is_rejected_for_non_pending_conversations(): void
    {
        $officer = $this->officer();
        $resident = $this->resident();

        $this->as('resident', $resident);
        $convId = $this->postJson('/api/messages/start', [
            'title' => 'Hi', 'recipient_type' => 'officer', 'recipient_id' => $officer->id,
        ])->assertOk()->json('conversation.id');

        // A non-participant cannot accept.
        $other = Resident::create(['name' => 'Other', 'email' => 'other@example.test', 'password' => 'Secret@12345', 'status' => 'Active']);
        $this->as('resident', $other);
        $this->postJson("/api/messages/{$convId}/accept")->assertStatus(403);
        $this->postJson("/api/messages/{$convId}/reject")->assertStatus(403);
    }
}
