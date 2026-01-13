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
     * Stream response from n8n webhook.
     */
    private function streamChatResponse(AgentSession $session, string $message): StreamedResponse
    {
        // Get webhook URL from user
        $user = \App\Models\User::find($session->user_id);
        $webhookUrl = $user?->n8n_webhook_url 
            ?? config('services.n8n.ai_analysis_webhook_url')
            ?? 'https://n8n.getaxia.de/webhook/d2336f92-eb51-4b66-b92d-c9e7d9cf4b7d';

        return new StreamedResponse(function () use ($session, $message, $webhookUrl) {
            try {
                // Build POST payload - same structure as other AI tasks
                $payload = [
                    'task' => 'chat',
                    'system_message' => 'You are a helpful AI assistant for startup founders. Provide concise, actionable advice.',
                    'user_prompt' => $message,
                    'temperature' => 0.7,
                    'session_id' => $session->session_id,
                    'user_id' => $session->user_id,
                    'company_id' => $session->meta['company_id'] ?? null,
                    'mode' => $session->mode,
                ];

                Log::channel('stack')->info('Calling n8n ai-analysis webhook for chat', [
                    'user_id' => $session->user_id,
                    'session_id' => $session->session_id,
                    'url' => $webhookUrl,
                ]);

                $response = Http::timeout(120)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post($webhookUrl, $payload);

                $body = $response->body();
                
                Log::channel('stack')->info('Chat response received', [
                    'user_id' => $session->user_id,
                    'session_id' => $session->session_id,
                    'status' => $response->status(),
                    'response_preview' => substr($body, 0, 200),
                ]);

                // Parse n8n response (standard ai-analysis format)
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Expected format: {success: true, data: "response text", tokens_used: 123}
                    if (isset($data['success']) && $data['success']) {
                        echo "data: " . json_encode([
                            'type' => 'message',
                            'sessionId' => $session->session_id,
                            'content' => $data['data'] ?? '',
                            'tokens' => $data['tokens_used'] ?? null,
                        ]) . "\n\n";
                    } else {
                        echo "data: " . json_encode([
                            'type' => 'error',
                            'error' => $data['error'] ?? 'Unknown error from AI service',
                        ]) . "\n\n";
                    }
                } else {
                    throw new \Exception('Webhook returned status ' . $response->status());
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
