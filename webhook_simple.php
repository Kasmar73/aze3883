<?php
// Sadə webhook faylı
header('Content-Type: application/json');

// Log faylı
$log_file = 'logs/webhook_simple_' . date('Y-m-d') . '.log';

function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
}

writeLog("Webhook çağırıldı - Method: " . $_SERVER['REQUEST_METHOD']);

// GET sorğusu üçün
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    writeLog("GET sorğusu alındı");
    echo json_encode([
        'status' => 'success',
        'message' => 'Webhook işləyir',
        'method' => 'GET',
        'timestamp' => date('Y-m-d H:i:s')
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
    
    // Sadə cavab
    $response = [
        'status' => 'success',
        'message' => 'Mesaj emal edildi',
        'chat_id' => $chat_id,
        'text' => $text,
        'user' => $user,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    echo json_encode($response);
} else {
    writeLog("Mesaj tapılmadı");
    echo json_encode([
        'status' => 'success',
        'message' => 'Update alındı, lakin mesaj yoxdur',
        'update_type' => array_keys($update)[0] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

writeLog("Webhook tamamlandı");
?>