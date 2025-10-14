<?php
// Telegram Bot Polling
require_once 'config/telegram.php';

$BOT_TOKEN = "YOUR_BOT_TOKEN_HERE";
$last_update_id = 0;

function getUpdates($bot_token, $offset = 0) {
    $url = "https://api.telegram.org/bot{$bot_token}/getUpdates";
    $data = ['offset' => $offset, 'timeout' => 30];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($result, true);
}

function processUpdate($update) {
    if (isset($update['message'])) {
        $message = $update['message'];
        $chat_id = $message['chat']['id'];
        $text = $message['text'] ?? '';
        
        echo "Mesaj alındı: Chat ID: {$chat_id}, Text: {$text}\n";
        
        // Burada mesajı emal edin
        // ...
    }
}

echo "Polling başladı...\n";

while (true) {
    $updates = getUpdates($BOT_TOKEN, $last_update_id + 1);
    
    if ($updates['ok'] && !empty($updates['result'])) {
        foreach ($updates['result'] as $update) {
            $last_update_id = $update['update_id'];
            processUpdate($update);
        }
    }
    
    sleep(1);
}
?>