<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Conversation;
use App\Services\OpenAIService;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OpenAIServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_openai_service_saves_chat_and_logs_metrics()
    {
        // Mock OpenAI Facade response structure
        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'This is a mocked response from AI.',
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 10,
                    'completion_tokens' => 8,
                    'total_tokens' => 18,
                ],
            ]),
        ]);

        // Setup Test Data
        $user = User::create([
            'name' => 'Master Developer',
            'email' => 'dev@master.com',
            'password' => bcrypt('password'),
        ]);

        $conversation = $user->conversations()->create(['title' => 'AI Test Unit']);

        // Execute Service Layer
        $service = new OpenAIService();
        $reply = $service->chat($conversation, 'Hello, can you help me build this system?');

        // Confirm System State
        $this->assertEquals('This is a mocked response from AI.', $reply);
        $this->assertDatabaseHas('messages', ['content' => 'Hello, can you help me build this system?', 'sender_role' => 'user']);
        $this->assertDatabaseHas('messages', ['content' => 'This is a mocked response from AI.', 'sender_role' => 'assistant']);
        $this->assertDatabaseHas('api_logs', [
            'user_id' => $user->id,
            'total_tokens' => 18,
        ]);
    }
}
