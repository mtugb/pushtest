<?php
require __DIR__ . '/../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing action parameter']);
    exit();
}

$subscriptionFile = __DIR__ . '/subscriptions.json';

if ($data['action'] === 'register') {
    // 購読（Subscription）登録の処理
    $subscriptions = file_exists($subscriptionFile) ? json_decode(file_get_contents($subscriptionFile), true) : [];
    $subscriptions[] = $data['subscription'];
    file_put_contents($subscriptionFile, json_encode($subscriptions));
    echo json_encode(['success' => true, 'message' => 'Subscription registered successfully.']);
} elseif ($data['action'] === 'send') {
    $subscriptions = file_exists($subscriptionFile) ? json_decode(file_get_contents($subscriptionFile), true) : [];

    if (empty($subscriptions)) {
        http_response_code(404);
        echo json_encode(['error' => 'No subscriptions found.']);
        exit();
    }

    $auth = [
        'VAPID' => [
            'subject' => 'mailto:me@example.com',
            'publicKey' => 'YOUR_VAPID_PUBLIC_KEY',
            'privateKey' => 'YOUR_VAPID_PRIVATE_KEY',
        ],
    ];

    $webPush = new WebPush($auth);
    $payload = $data['message'] ?? 'Default message from server!';

    $successful = 0;
    $validSubscriptions = [];

    foreach ($subscriptions as $subscriptionData) {
        $subscription = Subscription::create([
            'endpoint' => $subscriptionData['endpoint'],
            'keys' => $subscriptionData['keys'],
        ]);

        $report = $webPush->sendOneNotification($subscription, $payload);

        if ($report->isSuccess()) {
            $successful++;
            // 成功した購読情報のみを保持
            $validSubscriptions[] = $subscriptionData;
        } else {
            // 失敗した場合はログを出力（デバッグ用）
            error_log("Failed to send notification: " . $report->getReason() . " to endpoint: " . $subscription->getEndpoint());
            // 無効な購読情報は新しい配列に追加しない
        }
    }

    // 成功した購読情報のみでファイルを上書き
    file_put_contents($subscriptionFile, json_encode($validSubscriptions));

    $webPush->flush();
    echo json_encode(['success' => true, 'message' => $successful . ' notifications sent successfully.']);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action parameter.']);
}