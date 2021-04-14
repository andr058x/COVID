<?php

namespace App\Console\Commands;

use App\Support\TelegramClient;
use Illuminate\Console\Command;

class UpdateTelegramBotWebhook extends Command
{
    protected $signature = 'update-telegram-bot-webhook';

    protected $description = 'Update Telegram Bot Webhook';

    public function handle()
    {
        // Here we delete the webhook that is currently set (in case
        // the bot has another webhook URL right now)
        $this->deleteWebhook();
        // Here we set the new webhook URL.
        $this->setWebhook();
    }

    private function deleteWebhook(): void
    {
        $response = app(TelegramClient::class)->call('deleteWebhook');

        $this->info($response['description']);
    }

    private function setWebhook(): void
    {
        $response = app(TelegramClient::class)->call('setWebhook', [
            // Here we use `route()` helper to generate the full URL to the webhook handler
            // and pass it then to the Telegram.
            'url' => route('webhooks.telegram'),
        ]);

        $this->info($response['description']);
    }
}
