<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// webhook 是否被呼叫記錄
file_put_contents("webhook_debug.txt", "[" . date("Y-m-d H:i:s") . "] 有人呼叫 webhook" . PHP_EOL, FILE_APPEND);

$accessToken = '4XkgoI/CBhUW4x05oKNsOsnc0TehxUmv4X86TZXxgfk/6ZTSMvzQ3rbdejj4UN/1EDfCk5oTuhWsfL5EmIkwhvLThVrQBCtIzO1vUWKuMRlAxsv2aM9+iqcL1O6RPZgrQ9uYQATqPXFxpz/9sc26igdB04t89/1O/w1cDnyilFU=';

$jsonStr = file_get_contents('php://input');
file_put_contents('log.txt', "[" . date("Y-m-d H:i:s") . "] 內容：" . $jsonStr . PHP_EOL, FILE_APPEND);

$jsonObj = json_decode($jsonStr);
if (!isset($jsonObj->events) || !is_array($jsonObj->events)) {
    file_put_contents('log.txt', "[" . date("Y-m-d H:i:s") . "] ❌ events 不存在或格式錯誤" . PHP_EOL, FILE_APPEND);
    exit;
}

foreach ($jsonObj->events as $event) {
    $replyToken = $event->replyToken ?? null;
    $msg = $event->message->text ?? '';
    $userId = $event->source->userId ?? 'UNKNOWN';

    if (!$replyToken) {
        file_put_contents('log.txt', "[" . date("Y-m-d H:i:s") . "] ❌ 缺少 replyToken，無法回應" . PHP_EOL, FILE_APPEND);
        continue;
    }

    // debug 測試
    if ($msg == '/debug') {
        reply($replyToken, "✅ 我收到你的訊息了！\n你的 userId 是：$userId");
        continue;
    }

    if (preg_match("/提醒\s*(\d+\/\d+)\s*(早上|下午)?\s*(\d+:\d+)\s*(.+)/u", $msg, $m)) {
        list($month, $day) = explode('/', $m[1]);
        $ampm = $m[2] ?? '';
        list($hour, $minute) = explode(':', $m[3]);
        $text = $m[4];

        if ($ampm == '下午' && $hour < 12) $hour += 12;
        if ($ampm == '早上' && $hour == 12) $hour = 0;

        $year = date('Y');
        $remindTime = sprintf('%s-%02d-%02d %02d:%02d:00', $year, $month, $day, $hour, $minute);

        $reminder = [
            'time' => $remindTime,
            'text' => $text,
            'userId' => $userId
        ];

        file_put_contents('reminder.json', json_encode($reminder, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        reply($replyToken, "✅ 提醒已設定：$remindTime\n內容：$text");
    }
}

function reply($replyToken, $text) {
    global $accessToken;

    $url = 'https://api.line.me/v2/bot/message/reply';
    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer $accessToken"
    ];

    $body = [
        'replyToken' => $replyToken,
        'messages' => [['type' => 'text', 'text' => $text]]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $response = curl_exec($ch);
    curl_close($ch);

    file_put_contents("log.txt", "[" . date("Y-m-d H:i:s") . "] 回應結果：" . $response . PHP_EOL, FILE_APPEND);
}
?>
