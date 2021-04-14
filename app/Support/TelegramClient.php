<?php

namespace App\Support;

use App\Exceptions\TelgramException;
use Illuminate\Support\Facades\Http;

/**
 * This is a simple wrapper around Telegram Bot API.
 */
class TelegramClient
{
    private function getToken(): string
    {
        return config('services.telegram.bot_token');
    }

    public function call(string $method, array $data = []): array
    {
        $url = 'https://api.telegram.org/bot'.$this->getToken().'/'.$method;

        $response = Http::post($url, $data)->json();

        if (! $response['ok']) {
            throw new TelgramException($response['description']);
        }

        return $response;
    }
}
