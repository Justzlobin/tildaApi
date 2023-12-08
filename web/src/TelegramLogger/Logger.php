<?php

namespace App\TelegramLogger;

class Logger
{
    public static function sendTelegramMessage($message)
    {
        $token = $_ENV['TOKEN'];
        $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
        $params = [
            "chat_id" => $_ENV['CHAT_ID'],
            "text" => $message,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }
}