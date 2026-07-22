<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\AbaTelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * TelegramWebhookController
 *
 * Receives webhook updates from the Telegram Bot API and processes
 * payment confirmation messages forwarded from ABA Bank bot.
 *
 * Security:
 *   1. CSRF is excluded for this route (it's a server-to-server webhook).
 *   2. The "X-Telegram-Bot-Api-Secret-Token" header is verified against
 *      TELEGRAM_WEBHOOK_SECRET in .env, if configured.
 *   3. Only messages from the configured TELEGRAM_GROUP_CHAT_ID are processed.
 *
 * Route:
 *   POST /webhooks/telegram   ? handle()
 *
 * File: app/Http/Controllers/Webhook/TelegramWebhookController.php
 */
class TelegramWebhookController extends Controller
{
    public function __construct(
        protected AbaTelegramService $telegramService
    ) {}

    /**
     * Handle an incoming Telegram webhook update.
     *
     * Always responds with HTTP 200 to prevent Telegram from retrying.
     * Processing errors are logged but do not affect the response status.
     */
    public function handle(Request $request): JsonResponse
    {
        // -- 1. Verify the secret token header ------------------------------
        $configuredSecret = config('telegram.webhook_secret');

        if (! empty($configuredSecret)) {
            $incoming = $request->header('X-Telegram-Bot-Api-Secret-Token', '');

            if (! hash_equals($configuredSecret, $incoming)) {
                Log::warning('TelegramWebhook: invalid secret token', [
                    'ip' => $request->ip(),
                ]);
                // Still return 200 to avoid Telegram thinking the webhook is broken
                return response()->json(['ok' => false, 'reason' => 'Forbidden'], 200);
            }
        }

        $body = $request->all();

        Log::debug('TelegramWebhook: received update', [
            'update_id' => $body['update_id'] ?? null,
        ]);

        // -- 2. Extract message (handles forwarded messages too) -------------
        $message = $body['message'] ?? $body['channel_post'] ?? null;

        if (! $message) {
            // Not a message update (e.g. callback_query, inline_query) — ignore.
            return response()->json(['ok' => true]);
        }

        // -- 3. Verify the message came from our configured group ------------
        $configuredChatId = config('telegram.group_chat_id');
        $incomingChatId   = (string) ($message['chat']['id'] ?? '');

        if (! empty($configuredChatId) && $incomingChatId !== (string) $configuredChatId) {
            Log::warning('TelegramWebhook: message from unexpected chat', [
                'expected_chat_id' => $configuredChatId,
                'received_chat_id' => $incomingChatId,
            ]);
            return response()->json(['ok' => true]); // Silently ignore
        }

        // -- 4. Extract text (supports caption for photo messages too) -------
        $text = $message['text'] ?? $message['caption'] ?? null;

        if (empty($text)) {
            return response()->json(['ok' => true]); // Non-text message, nothing to do
        }

        // -- 5. Delegate to the service for parsing and confirmation ---------
        try {
            $confirmed = $this->telegramService->processIncomingMessage($text);

            Log::info('TelegramWebhook: processing result', [
                'confirmed' => $confirmed,
                'chat_id'   => $incomingChatId,
            ]);
        } catch (\Throwable $e) {
            Log::error('TelegramWebhook: exception during processing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Always return 200 to Telegram
        return response()->json(['ok' => true]);
    }
}
