<?php
$accessToken = '4XkgoI/CBhUW4x05oKNsOsnc0TehxUmv4X86TZXxgfk/6ZTSMvzQ3rbdejj4UN/1EDfCk5oTuhWsfL5EmIkwhvLThVrQBCtIzO1vUWKuMRlAxsv2aM9+iqcL1O6RPZgrQ9uYQATqPXFxpz/9sc26igdB04t89/1O/w1cDnyilFU=';
$reminderFile = 'reminder.json';

if (!file_exists($reminderFile)) exit;

$reminder = json_decode(file_get_contents($reminderFile), true);
$now = date("Y-m-d H:i");

if (substr($reminder['time'], 0, 16) == $now) {
    $userId = $reminder['userId'];
    $text = "⏰ 提醒你：「" . $reminder['text'] . "」";

    $url = 'https://api.line.me/v2/bot/message/push';
    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer $accessToken"
    ];
    $body = [
        'to' => $userId,
        'messages' => [['type' => 'text', 'text' => $text]]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $response = curl_exec($ch);
    curl_close($ch);

    file_put_contents('check_log.txt', "[" . date("Y-m-d H:i:s") . "] 發送結果：" . $response . PHP_EOL, FILE_APPEND);
    unlink($reminderFile); // 傳完就刪掉
} else {
    file_put_contents('check_log.txt', "[" . date("Y-m-d H:i:s") . "] 未達提醒時間，無發送" . PHP_EOL, FILE_APPEND);
}
?>
