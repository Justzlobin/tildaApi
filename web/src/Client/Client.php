<?php

namespace App\Client;

class Client
{
    public static function request($dataString)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://openapi.keycrm.app/v1/order");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-type: application/json",
                "Accept: application/json",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                'Authorization:  Bearer ' . $_ENV['API_KEYCRM_TOKEN'])
        );
        $result = curl_exec($ch);
        $orderUUID = json_decode($result, true)['source_uuid'];
        curl_close($ch);
        return $orderUUID;
    }
}