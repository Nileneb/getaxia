<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgentSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    /**
     * Start a new chat session.
     */
    public function start(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'mode' => 'string|in:chat,workflow',
            'workflow_key' => 'nullable|string',
        ]);

        $user = $request->user();

        // Create new session
        $session = AgentSession::create([
            'user_id' => $user->id,
            'mode' => $validated['mode'] ?? 'chat',
            'workflow_key' => $validated['workflow_key'] ?? null,
            'meta' => [
                'user_email' => $user->email,
                'user_name' => $user->first_name . ' ' . $user->last_name,
                'company_id' => $user->company_id,
            ],
        ]);

        Log::channel('stack')->info('Agent session created', [
            'user_id' => $user->id,
            'session_id' => $session->session_id,
            'mode' => $session->mode,
        ]);

        // Stream response from n8n
        return $this->streamChatResponse($session, $validated['message']);
    }

    /**
     * Send a message in existing session.
     */
    public function message(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'session_id' => 'required|uuid',
            'message' => 'required|string',
        ]);

        $user = $request->user();

        // Find and validate session
        $session = AgentSession::where('session_id', $validated['session_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check if expired
        if ($session->isExpired()) {
            return response()->json([
                'error' => 'Session expired',
            ], 401);
        }

        // Extend session on activity
        $session->extend();

        Log::channel('stack')->info('Agent message sent', [
            'user_id' => $user->id,
            'session_id' => $session->session_id,
            'message_preview' => substr($validated['message'], 0, 100),
        ]);

        return $this->streamChatResponse($session, $validated['message']);
    }

    /**
     * Stream response from Langdock API (OpenAI-compatible).
     */
    private function streamChatResponse(AgentSession $session, string $message): StreamedResponse
    {
        // Get Langdock configuration from config/services.php
        $apiKey = config('services.langdock.api_key');
        $baseUrl = config('services.langdock.base_url');
        $model = config('services.langdock.model');

        if (empty($apiKey) || empty($baseUrl)) {
            throw new \Exception('Langdock API credentials not configured');
        }

        return new StreamedResponse(function () use ($session, $message, $apiKey, $baseUrl, $model) {
            try {
                // Build OpenAI-compatible messages
                $systemMessage = 'You are a helpful AI assistant for startup founders. Provide concise, actionable advice.';

                $messages = [
                    [
                        'role' => 'system',
                        'content' => $systemMessage,
                    ],
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ];

                Log::channel('stack')->info('Calling Langdock API for chat', [
                    'user_id' => $session->user_id,
                    'session_id' => $session->session_id,
                    'model' => $model,
                ]);

                // Non-streaming request first (as per requirement)
                $response = Http::timeout(120)
                    ->withHeaders([
                        'Authorization' => "Bearer {$apiKey}",
                        'Content-Type' => 'application/json',
                    ])
                    ->post("{$baseUrl}/chat/completions", [
                        'model' => $model,
                        'messages' => $messages,
                        'temperature' => 0.7,
                        'max_tokens' => 2000,
                    ]);

                Log::channel('stack')->info('Chat response received from Langdock', [
                    'user_id' => $session->user_id,
                    'session_id' => $session->session_id,
                    'status' => $response->status(),
                ]);

                // Parse OpenAI-compatible response
                if ($response->successful()) {
                    $data = $response->json();

                    // Extract content from OpenAI format
                    $content = $data['choices'][0]['message']['content'] ?? '';
                    $tokensUsed = $data['usage']['total_tokens'] ?? null;

                    echo "data: " . json_encode([
                        'type' => 'message',
                        'sessionId' => $session->session_id,
                        'content' => $content,
                        'tokens' => $tokensUsed,
                    ]) . "\n\n";
                } else {
                    throw new \Exception('Langdock API returned status ' . $response->status());
                }

                flush();

            } catch (\Exception $e) {
                Log::channel('stack')->error('Agent chat error', [
                    'user_id' => $session->user_id,
                    'session_id' => $session->session_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                echo "data: " . json_encode([
                    'type' => 'error',
                    'error' => 'Verbindung zum AI-Service fehlgeschlagen: ' . $e->getMessage(),
                ]) . "\n\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Get session info.
     */
    public function show(Request $request, string $sessionId)
    {
        $user = $request->user();

        $session = AgentSession::where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        return response()->json([
            'session_id' => $session->session_id,
            'mode' => $session->mode,
            'workflow_key' => $session->workflow_key,
            'expires_at' => $session->expires_at,
            'is_expired' => $session->isExpired(),
            'created_at' => $session->created_at,
        ]);
    }
}
