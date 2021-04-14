<?php

namespace App\Support;

class TelegramWebhookHandler
{
    const GET_METRICS_BUTTON_TEXT = 'Get Metrics';

    private TelegramClient $telegramClient;

    public function __construct(TelegramClient $telegramClient)
    {
        $this->telegramClient = $telegramClient;
    }

    public function handle(array $payload): void
    {
        $replyMarkup = [
            'keyboard' => [
                [['text' => self::GET_METRICS_BUTTON_TEXT]],
            ],
            // Requests clients to resize the keyboard vertically for optimal fit
            'resize_keyboard' => true,
        ];

        switch ($payload['message']['text']) {
            case '/start':
                $this->telegramClient->call('sendMessage', [
                    'chat_id' => $payload['message']['from']['id'],
                    'text' => 'Hello! Please, click the "'.self::GET_METRICS_BUTTON_TEXT.'" button to get metrics...',
                    'parse_mode' => 'markdown',
                    'reply_markup' => $replyMarkup,
                ]);
                return;
            case self::GET_METRICS_BUTTON_TEXT: // If user clicks the "Get Metrics" button
                // Metrics collection takes some time, we'll inform the user about that.
                $this->telegramClient->call('sendMessage', [
                    'chat_id' => $payload['message']['from']['id'],
                    'text' => 'Please wait. Metrics are being collected...',
                ]);

                // Here we generate the metrics text.
                $metricsText = $this->getMetricsText();

                // Here we send generated metrics.
                $this->telegramClient->call('sendMessage', [
                    'chat_id' => $payload['message']['from']['id'],
                    'text' => $metricsText,
                    'reply_markup' => $replyMarkup,
                ]);
                return;
        }
    }

    public function getMetricsText(): string
    {
        $covidMetrics = app(CovidMetrics::class);

        // Here we create the collection with the metric title as the key, metric value as the value.
        $text = collect([
            'New infections in the last 24h' => $covidMetrics->getNewInfectionsInLastDay(),
            'Total infections' => $covidMetrics->getTotalInfections(),
            'Increase of infections in the last 24h' => $covidMetrics->getInfectionsIncreaseInLastDay(),
            'Average increase of the last '.$covidMetrics::NUMBER_OF_DAYS_FOR_AVERAGE.' days' => $covidMetrics->getAverageInfectionsIncreaseInLastDays(),
            'Average decrease of the last '.$covidMetrics::NUMBER_OF_DAYS_FOR_AVERAGE.' days' => $covidMetrics->getAverageInfectionsDecreaseInLastDays(),
            'Incidence value for whole Germany' => $covidMetrics->getIncidenceValueForWholeGermany(),
            'Target total infection' => $covidMetrics->getTargetTotalInfection(),
            'Prediction of the days of lockdown still required until a defined incidence is reached' => $covidMetrics->getPredictionOfDaysOfLockdownStillRequired(),
        ])
            // Here we generate the human-readable string
            // For example, "New infections in the last 24h: 5890"
            ->map(function ($value, string $title) {
                return "{$title}: ${value}";
            })
            // Here we join all the metrics to the single string. Each
            // metric is separated with a line separator.
            ->implode(PHP_EOL);

        return 'Covid situation Germany'.PHP_EOL.PHP_EOL.$text;
    }
}
