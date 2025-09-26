<?php
// Webhook debug faylı
header('Content-Type: application/json');

// Log faylı
$log_file = 'logs/debug_webhook_' . date('Y-m-d') . '.log';

function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
}

writeLog("Debug webhook çağırıldı - Method: " . $_SERVER['REQUEST_METHOD']);

// GET sorğusu üçün
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    writeLog("GET sorğusu alındı");
    echo json_encode([
        'status' => 'success',
        'message' => 'Debug webhook işləyir',
        'method' => 'GET',
        'timestamp' => date('Y-m-d H:i:s'),
        'server_info' => [
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'yoxdur',
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'yoxdur',
            'HTTPS' => $_SERVER['HTTPS'] ?? 'yoxdur',
            'SERVER_PORT' => $_SERVER['SERVER_PORT'] ?? 'yoxdur'
        ]
    ]);
    exit;
}

// POST sorğusu üçün
$input = file_get_contents('php://input');
writeLog("POST input: " . $input);

if (empty($input)) {
    writeLog("Boş input");
    echo json_encode([
        'status' => 'error',
        'message' => 'Boş input',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// JSON parse et
$update = json_decode($input, true);

if (!$update) {
    writeLog("JSON parse xətası: " . json_last_error_msg());
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON',
        'error' => json_last_error_msg(),
        'input' => $input,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

writeLog("JSON parse uğurlu");

// Telegram update-i emal et
if (isset($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $user = $message['from'] ?? [];
    
    writeLog("Mesaj alındı: Chat ID: {$chat_id}, Text: {$text}");
    
    // Bot token (buraya öz tokeninizi yazın)
    $bot_token = "YOUR_BOT_TOKEN_HERE";
    
    // Əgər bot token təyin edilməyibsə, log yaz
    if ($bot_token === "YOUR_BOT_TOKEN_HERE") {
        writeLog("XƏTA: Bot token təyin edilməyib!");
        echo json_encode([
            'status' => 'error',
            'message' => 'Bot token təyin edilməyib',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }
    
    // Sadə cavab göndər
    $response_text = "✅ Bot işləyir!\n\n";
    $response_text .= "Mesajınız: {$text}\n";
    $response_text .= "Chat ID: {$chat_id}\n";
    $response_text .= "Tarix: " . date('Y-m-d H:i:s');
    
    $result = sendMessage($bot_token, $chat_id, $response_text);
    writeLog("Cavab göndərildi: " . json_encode($result));
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Mesaj emal edildi',
        'chat_id' => $chat_id,
        'text' => $text,
        'bot_response' => $result,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    writeLog("Mesaj tapılmadı");
    echo json_encode([
        'status' => 'success',
        'message' => 'Update alındı, lakin mesaj yoxdur',
        'update_type' => array_keys($update)[0] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function sendMessage($bot_token, $chat_id, $text) {
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'http_code' => $http_code,
        'result' => json_decode($result, true),
        'error' => $error
    ];
}

writeLog("Debug webhook tamamlandı");
?>