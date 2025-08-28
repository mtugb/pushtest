<?php
// .envファイルから環境変数を読み込むライブラリを使用する場合
require __DIR__ . '/../vendor/autoload.php';
// Dotenv::createImmutable(__DIR__ . '/../')->load(); // 例: Dotenvライブラリを使う場合
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

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

// Subscription情報を保存するファイル
$subscriptionFile = __DIR__ . '/subscriptions.json';

if ($data['action'] === 'register') {
    // 購読（Subscription）登録の処理
    $subscriptions = file_exists($subscriptionFile) ? json_decode(file_get_contents($subscriptionFile), true) : [];
    $subscriptions[] = $data['subscription'];
    file_put_contents($subscriptionFile, json_encode($subscriptions));
    echo json_encode(['success' => true, 'message' => 'Subscription registered successfully.']);
} elseif ($data['action'] === 'send') {
    // プッシュ通知送信の処理
    $subscriptions = file_exists($subscriptionFile) ? json_decode(file_get_contents($subscriptionFile), true) : [];

    if (empty($subscriptions)) {
        http_response_code(404);
        echo json_encode(['error' => 'No subscriptions found.']);
        exit();
    }

    $auth = [
        'VAPID' => [
            'subject' => 'mailto:me@example.com',
            'publicKey' => $_ENV['PUBLIC_KEY'],
            'privateKey' => $_ENV['PRIVATE_KEY'],
        ],
    ];

    $webPush = new WebPush($auth);
    $payload = $data['message'] ?? 'Default message from server!';

    $successful = 0;
    foreach ($subscriptions as $subscriptionData) {
        $subscription = Subscription::create([
            'endpoint' => $subscriptionData['endpoint'],
            'keys' => $subscriptionData['keys'],
        ]);
        $report = $webPush->sendOneNotification($subscription, $payload);
        if ($report->isSuccess()) {
            $successful++;
        }
    }

    $webPush->flush();
    echo json_encode(['success' => true, 'message' => $successful . ' notifications sent successfully.']);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action parameter.']);
}