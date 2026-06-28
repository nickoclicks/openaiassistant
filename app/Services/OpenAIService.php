<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Conversation;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    /**
     * Send chat context to OpenAI and log the system token metrics.
     */
    public function chat(Conversation $conversation, string $userMessageText): string
    {
        $startTime = microtime(true);

        // 1. Persist the User's Message to the database
        $conversation->messages()->create([
            'sender_role' => 'user',
            'content' => $userMessageText,
        ]);

        // 2. Fetch past conversation history format for OpenAI API context
        $formattedMessages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'role' => $msg->sender_role,
                    'content' => $msg->content,
                ];
            })->toArray();

        try {
            // 3. Dispatch payload to OpenAI
            $response = OpenAI::chat()->create([
                'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
                'messages' => $formattedMessages,
            ]);

            $assistantResponse = $response->choices[0]->message->content;

            // 4. Save Assistant Response to DB
            $conversation->messages()->create([
                'sender_role' => 'assistant',
                'content' => $assistantResponse,
            ]);

            // 5. Calculate Metrics and write to normalized ApiLogs
            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);

            ApiLog::create([
                'user_id' => $conversation->user_id,
                'prompt_tokens' => $response->usage->promptTokens,
                'completion_tokens' => $response->usage->completionTokens,
                'total_tokens' => $response->usage->totalTokens,
                'response_time_ms' => $responseTimeMs,
            ]);

            return $assistantResponse;

        } catch (\Exception $e) {
            Log::error('OpenAI Service Error: ' . $e->getMessage());
            throw new \Exception('Failed to communicate with OpenAI.');
        }
    }
}