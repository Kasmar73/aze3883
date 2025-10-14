<?php
// Final webhook faylı
header('Content-Type: application/json');

// Log faylı
$log_file = 'logs/webhook_final_' . date('Y-m-d') . '.log';

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
    
    // Mesajı emal et
    if ($text === '/start') {
        $response_text = "🎉 Salam! SMM Panel-ə xoş gəlmisiniz!\n\n";
        $response_text .= "Bu bot ilə sosial media xidmətləri sifariş edə bilərsiniz:\n";
        $response_text .= "• Instagram followers, likes, views\n";
        $response_text .= "• Facebook likes, followers\n";
        $response_text .= "• YouTube views, subscribers\n";
        $response_text .= "• TikTok followers, likes\n";
        $response_text .= "• Twitter followers, retweets\n\n";
        $response_text .= "Panel-i açmaq üçün aşağıdakı düyməni basın:";
        
        // WebApp düyməsi ilə cavab göndər
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🚀 SMM Panel Aç',
                        'web_app' => ['url' => 'https://smmaze.duckdns.org/index.php']
                    ]
                ]
            ]
        ];
        
        sendMessage($bot_token, $chat_id, $response_text, $keyboard);
        
    } elseif ($text === '/help') {
        $help_text = "📋 Yardım\n\n";
        $help_text .= "/start - Botu başlat\n";
        $help_text .= "/help - Bu yardım mesajı\n";
        $help_text .= "/balance - Balansınızı yoxlayın\n";
        $help_text .= "/orders - Sifarişlərinizi görün\n";
        $help_text .= "/panel - SMM panelini açın\n\n";
        $help_text .= "Suallarınız üçün: @support_username";
        
        sendMessage($bot_token, $chat_id, $help_text);
        
    } elseif ($text === '/balance') {
        $balance_text = "💰 Balansınız: 0.00 AZN\n\n";
        $balance_text .= "Balans artırmaq üçün panel-i açın və 'Balans' bölməsinə keçin.";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '💰 Balans Artır',
                        'web_app' => ['url' => 'https://smmaze.duckdns.org/index.php']
                    ]
                ]
            ]
        ];
        
        sendMessage($bot_token, $chat_id, $balance_text, $keyboard);
        
    } elseif ($text === '/orders') {
        $orders_text = "📋 Son Sifarişləriniz:\n\n";
        $orders_text .= "Hələ sifarişiniz yoxdur.\n";
        $orders_text .= "İlk sifarişinizi vermək üçün panel-i açın.";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '📋 Sifarişlərim',
                        'web_app' => ['url' => 'https://smmaze.duckdns.org/index.php']
                    ]
                ]
            ]
        ];
        
        sendMessage($bot_token, $chat_id, $orders_text, $keyboard);
        
    } elseif ($text === '/panel') {
        $panel_text = "🚀 SMM Panel-i açmaq üçün aşağıdakı düyməni basın:";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '🚀 SMM Panel Aç',
                        'web_app' => ['url' => 'https://smmaze.duckdns.org/index.php']
                    ]
                ]
            ]
        ];
        
        sendMessage($bot_token, $chat_id, $panel_text, $keyboard);
        
    } else {
        $default_text = "❓ Məlum olmayan əmr.\n\n";
        $default_text .= "Yardım üçün /help yazın.\n";
        $default_text .= "Panel-i açmaq üçün /panel yazın.";
        
        sendMessage($bot_token, $chat_id, $default_text);
    }
    
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
        'update_type' => array_keys($update)[0] ?? 'unknown',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function sendMessage($bot_token, $chat_id, $text, $reply_markup = null) {
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    
    if ($reply_markup) {
        $data['reply_markup'] = json_encode($reply_markup);
    }
    
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

writeLog("Webhook tamamlandı");
?>