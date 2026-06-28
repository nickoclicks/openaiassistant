<?php

namespace Tests\Unit;

use Tests\TestCase;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_conversation_with_messages()
    {
        // 1. Create User
        $user = User::create([
            'name' => 'Test Master',
            'email' => 'master@test.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ]);

        // 2. Create Conversation linked to User
        $conversation = $user->conversations()->create([
            'title' => 'OpenAI Chat Session'
        ]);

        // 3. Create a Message linked to Conversation
        $message = $conversation->messages()->create([
            'sender_role' => 'user',
            'content' => 'Hello AI!'
        ]);

        // Assertions
        $this->assertDatabaseHas('users', ['email' => 'master@test.com']);
        $this->assertDatabaseHas('conversations', ['title' => 'OpenAI Chat Session']);
        $this->assertDatabaseHas('messages', ['content' => 'Hello AI!']);
        
        // Verify Eloquent relationships map correctly
        $this->assertEquals($user->id, $conversation->user_id);
        $this->assertEquals($conversation->id, $message->conversation_id);
    }
}
