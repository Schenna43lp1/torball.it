<?php
declare(strict_types=1);

function telegram_config(): array
{
    return [
        'bot_token' => getenv('TELEGRAM_BOT_TOKEN') ?: '',
        'chat_id' => getenv('TELEGRAM_CHAT_ID') ?: '',
    ];
}

function send_telegram_message(string $message): bool
{
    $config = telegram_config();

    if ($config['bot_token'] === '' || $config['chat_id'] === '') {
        return false;
    }

    $url = 'https://api.telegram.org/bot' . $config['bot_token'] . '/sendMessage';

    $payload = [
        'chat_id' => $config['chat_id'],
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true,
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $response !== false && $httpCode >= 200 && $httpCode < 300;
}
