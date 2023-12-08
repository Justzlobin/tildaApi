<?php

use App\TelegramLogger\Logger;

require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

Logger::sendTelegramMessage('повідомлення з SCRIPT  : START');
Logger::sendTelegramMessage('повідомлення з TILDA: ' . print_r($_POST, true));
try {

    $payment_method_id = 1;
    if ($_POST['paymentsystem'] !== 'cash') {
        $payment_method_id = 2;
    }

    empty($_POST['Phone']) ? $phone = $_POST['phone'] : $phone = $_POST['Phone'];
    empty($_POST['Email']) ? $email = $_POST['email'] : $email = $_POST['Email'];

    $products = [];
    foreach ($_POST['payment']['products'] as $product) {
        $products[] = itemProducts($product);
    }

    $data = [
        "source_id" => 1,
        "source_uuid" => $_POST['payment']['orderid'],
        "buyer" => [
            "full_name" => $_POST['payment']['delivery_fio'],
            "email" => $email,
            "phone" => $phone
        ],
        "shipping" => [
            "delivery_service_id" => 1,
            "shipping_service" => "Нова Пошта",
            "shipping_address_city" => $_POST['payment']['delivery_address'],
            "shipping_address_zip" => $_POST['payment']['delivery_zip'],
            "recipient_full_name" => $_POST['payment']['delivery_fio'],
            "warehouse_ref" => $_POST['payment']['delivery_pickup_id'],
            "recipient_phone" => $phone
        ],
        "products" => $products,
        "payments" => [
            [
                "payment_method_id" => $payment_method_id,
                "payment_method" => $_POST['paymentsystem'],
                "amount" => $_POST['payment']['amount'],
                "status" => "not_paid"
            ]
        ]
    ];

    $result = request(json_encode($data));
    Logger::sendTelegramMessage('повідомлення з KEYCRM: ' . $result);
} catch (Exception $e) {
    Logger::sendTelegramMessage('повідомлення з SCRIPT: ERROR ' . $e->getMessage());
}

function itemProducts(array $items): array
{
    if ($items['sku']) {
        return [
            'price' => $items['price'],
            'quantity' => $items['quantity'],
            'name' => $items['name'],
            'sku' => $items['sku']
        ];
    }

    return [
        'price' => $items['price'],
        'quantity' => $items['quantity'],
        'name' => $items['name']
    ];
}

function request($dataString)
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
