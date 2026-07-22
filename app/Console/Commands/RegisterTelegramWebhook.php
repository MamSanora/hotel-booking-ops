<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * RegisterTelegramWebhook
 *
 * Registers this application''s webhook URL with the Telegram Bot API.
 * Run this once after setting TELEGRAM_BOT_TOKEN in .env and every time
 * your public URL changes (e.g. new ngrok tunnel).
 *
 * Usage:
 *   php artisan telegram:register-webhook
 *   php artisan telegram:register-webhook --delete    (unregister)
 *   php artisan telegram:register-webhook --info      (show current webhook info)
 *
 * File: app/Console/Commands/RegisterTelegramWebhook.php
 */
class RegisterTelegramWebhook extends Command
{
    protected $signature = ''telegram:register-webhook
                            {--delete : Remove the currently registered webhook}
                            {--info   : Show the currently registered webhook info}'';

    protected $description = ''Register (or remove) the Telegram bot webhook URL with the Telegram API'';

    protected const TELEGRAM_API = ''https://api.telegram.org'';

    public function handle(): int
    {
        $token = config(''telegram.bot_token'');

        if (empty($token)) {
            $this->error(''TELEGRAM_BOT_TOKEN is not set in .env. Aborting.'');
            return self::FAILURE;
        }

        // ── --info ──────────────────────────────────────────────────────────
        if ($this->option(''info'')) {
            $response = Http::get(self::TELEGRAM_API . "/bot{$token}/getWebhookInfo");
            $data = $response->json();
            $this->info(''Current webhook info:'');
            $this->line(json_encode($data[''result''] ?? $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        // ── --delete ─────────────────────────────────────────────────────────
        if ($this->option(''delete'')) {
            $response = Http::post(self::TELEGRAM_API . "/bot{$token}/deleteWebhook");
            if ($response->json(''ok'')) {
                $this->info(''Webhook successfully removed.'');
            } else {
                $this->error(''Failed to remove webhook: '' . ($response->json(''description'') ?? ''Unknown error''));
            }
            return self::SUCCESS;
        }

        // ── Register ─────────────────────────────────────────────────────────
        $webhookUrl = rtrim(config(''app.url''), ''/'') . ''/webhooks/telegram'';
        $secret     = config(''telegram.webhook_secret'');

        $this->info("Registering webhook: {$webhookUrl}");

        $payload = [''url'' => $webhookUrl];

        if (! empty($secret)) {
            $payload[''secret_token''] = $secret;
            $this->line(''  → Using secret token for verification'');
        }

        $response = Http::post(self::TELEGRAM_API . "/bot{$token}/setWebhook", $payload);
        $data     = $response->json();

        if ($data[''ok''] ?? false) {
            $this->info(''✓ Webhook registered successfully!'');
            $this->line("  URL: {$webhookUrl}");
        } else {
            $this->error(''Failed to register webhook:'');
            $this->line(json_encode($data, JSON_PRETTY_PRINT));
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
