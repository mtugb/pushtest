<?php
require __DIR__ . '../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (empty($data)) {
    exit("Invalid subscription data");
}

$auth = [
    'VAPID' => [
        'subject' => 'mailto:me@example.com',
        'publicKey' => $_ENV['PUBLIC_KEY'],
        'privateKey' => $_ENV['PRIVATE_KEY'],
    ],
];

$webPush = new WebPush($auth);

// Subscriptionオブジェクトを作成
$subscription = Subscription::create([
    'endpoint' => $data['endpoint'],
    'keys' => $data['keys'],
]);

// プッシュ通知を送信
$report = $webPush->sendOneNotification(
    $subscription,
    "Hello from the server!"
);

$webPush->flush();

if ($report->isSuccess()) {
    echo "Notification sent successfully!";
} else {
    echo "Notification failed to send.";
}