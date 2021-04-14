<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Support\TelegramClient;
use App\Support\TelegramWebhookHandler;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function __invoke()
    {
        // Here we resolve the webhook handler from the container.
        $telegramWebhookHandler = app(TelegramWebhookHandler::class);

        // Here we get all the webhook payload sent by Telegram.
        $payload = request()->all();

        // And pass this data to the webhook handler.
        $telegramWebhookHandler->handle($payload);
    }
}
