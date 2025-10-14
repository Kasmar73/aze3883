<?php
// Webhook test faylı
header('Content-Type: application/json');

// Debug mode
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log faylı
$log_file = 'logs/webhook_test_' . date('Y-m-d') . '.log';

function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
}

writeLog("Webhook test çağırıldı");

// GET parametrlərini yoxla
if (isset($_GET['test'])) {
    writeLog("GET test: " . $_GET['test']);
    echo json_encode([
        'status' => 'success',
        'message' => 'Webhook işləyir',
        'method' => 'GET',
        'test' => $_GET['test'],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

// POST məlumatlarını yoxla
$input = file_get_contents('php://input');
writeLog("POST input: " . $input);

if (empty($input)) {
    writeLog("Boş input");
    echo json_encode([
        'status' => 'error',
        'message' => 'Boş input',
        'method' => $_SERVER['REQUEST_METHOD'],
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

writeLog("JSON parse uğurlu: " . json_encode($update));

// Telegram update-i emal et
if (isset($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $text = $message['text'] ?? '';
    
    writeLog("Mesaj alındı: Chat ID: {$chat_id}, Text: {$text}");
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Mesaj emal edildi',
        'chat_id' => $chat_id,
        'text' => $text,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    writeLog("Mesaj tapılmadı");
    echo json_encode([
        'status' => 'success',
        'message' => 'Update alındı, lakin mesaj yoxdur',
        'update' => $update,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>