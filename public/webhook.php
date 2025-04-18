<?php
file_put_contents('log.txt', "[" . date("Y-m-d H:i:s") . "] Webhook triggered\n", FILE_APPEND);

$accessToken = '4XkgoI/CBhUW4x05oKNsOsnc0TehxUmv4X86TZXxgfk/6ZTSMvzQ3rbdejj4UN/1EDfCk5oTuhWsfL5EmIkwhv LThVrQBCtIzO1vUWKuMRlAxsv2aM9+iqcL1O6RPZgrQ9uYQATqPXFxpz/9sc26igdB04t89/1O/w1cDnyilFU=';

$jsonStr = file_get_contents('php://input');
file_put_contents('log.txt', "[" . date("Y-m-d H:i:s") . "] " . $jsonStr . PHP_EOL, FILE_APPEND);
$jsonObj = json_decode($jsonStr);

http_response_code(200);

foreach ($jsonObj->events as $event) {
    $replyToken = $event->replyToken ?? '';
    $msg = $event->message->text ?? '';
    $userId = $event->source->userId ?? '';

    if (strpos($msg, '蓁蓁咪') !== false) {
        reply($replyToken, "你喊我嗎～💕");
    } elseif (strpos($msg, '提醒') !== false) {
        file_put_contents('reminder.json', json_encode([
            'time' => date("Y-m-d H:i", strtotime("+1 minutes")),
            'text' => $msg,
            'userId' => $userId
        ], JSON_UNESCAPED_UNICODE));
        reply($replyToken, "提醒已設定！");
    }
}

function reply($replyToken, $text) {
    $url = 'https://api.line.me/v2/bot/message/reply';
    $accessToken = 'YOUR_CHANNEL_ACCESS_TOKEN';
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
    curl_exec($ch);
    curl_close($ch);
}
?>
