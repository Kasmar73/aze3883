<?php
// Brauzer üçün webhook faylı
header('Content-Type: text/html; charset=utf-8');

// Log faylı
$log_file = 'logs/webhook_browser_' . date('Y-m-d') . '.log';

function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
}

writeLog("Browser webhook çağırıldı - Method: " . $_SERVER['REQUEST_METHOD']);

// Bot token (buraya öz tokeninizi yazın)
$bot_token = "7739231947:AAHXh7wdpocOqVNPl-Nmu9fEFrxUbDLaZc0";

// Əgər bot token təyin edilməyibsə
if ($bot_token === "7739231947:YOUR_BOT_TOKEN_HERE") {
    echo "<!DOCTYPE html>
    <html lang='az'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Webhook - Bot Token Tələb Olunur</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 50px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🤖 Webhook Konfiqurasiyası</h1>
            <div class='error'>
                <h3>❌ Bot Token Tələb Olunur</h3>
                <p>Webhook-un işləməsi üçün bot tokeninizi daxil etməlisiniz.</p>
                <div class='code'>
                    \$bot_token = \"7739231947:YOUR_BOT_TOKEN_HERE\";
                </div>
                <p>Bu sətri redaktə edin və bot tokeninizi yazın.</p>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

// GET sorğusu üçün
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    writeLog("GET sorğusu alındı");
    
    echo "<!DOCTYPE html>
    <html lang='az'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Webhook - Bot İşləyir</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 50px; background: #f5f5f5; }
            .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 20px 0; }
            .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
            .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
            .btn:hover { background: #0056b3; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>🤖 Webhook İşləyir</h1>
            <div class='success'>
                <h3>✅ Webhook Aktivdir</h3>
                <p>Bot webhook-u düzgün işləyir və mesajları qəbul edir.</p>
            </div>
            <div class='info'>
                <h3>📱 Bot Test</h3>
                <p>Botu Telegram-da tapın və aşağıdakı əmrləri yazın:</p>
                <div class='code'>
                    /start - Botu başlat<br>
                    /help - Yardım məlumatları<br>
                    /balance - Balansı yoxla<br>
                    /orders - Sifarişləri gör<br>
                    /panel - SMM panelini aç
                </div>
            </div>
            <div class='info'>
                <h3>🔗 Faydalı Linklər</h3>
                <a href='https://smmaze.duckdns.org/index.php' class='btn'>SMM Panel</a>
                <a href='https://smmaze.duckdns.org/test_bot.php' class='btn'>Bot Test</a>
                <a href='https://smmaze.duckdns.org/logs/' class='btn'>Log Faylları</a>
            </div>
        </div>
    </body>
    </html>";
    exit;
}

// POST sorğusu üçün (Telegram-dan gələn mesajlar)
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
        
        $result = sendMessage($bot_token, $chat_id, $response_text, $keyboard);
        writeLog("Start cavabı göndərildi: " . json_encode($result));
        
    } elseif ($text === '/help') {
        $help_text = "📋 Yardım\n\n";
        $help_text .= "/start - Botu başlat\n";
        $help_text .= "/help - Bu yardım mesajı\n";
        $help_text .= "/balance - Balansınızı yoxlayın\n";
        $help_text .= "/orders - Sifarişlərinizi görün\n";
        $help_text .= "/panel - SMM panelini açın\n\n";
        $help_text .= "Suallarınız üçün: @support_username";
        
        $result = sendMessage($bot_token, $chat_id, $help_text);
        writeLog("Help cavabı göndərildi: " . json_encode($result));
        
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
        
        $result = sendMessage($bot_token, $chat_id, $balance_text, $keyboard);
        writeLog("Balance cavabı göndərildi: " . json_encode($result));
        
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
        
        $result = sendMessage($bot_token, $chat_id, $orders_text, $keyboard);
        writeLog("Orders cavabı göndərildi: " . json_encode($result));
        
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
        
        $result = sendMessage($bot_token, $chat_id, $panel_text, $keyboard);
        writeLog("Panel cavabı göndərildi: " . json_encode($result));
        
    } else {
        $default_text = "❓ Məlum olmayan əmr.\n\n";
        $default_text .= "Yardım üçün /help yazın.\n";
        $default_text .= "Panel-i açmaq üçün /panel yazın.";
        
        $result = sendMessage($bot_token, $chat_id, $default_text);
        writeLog("Default cavabı göndərildi: " . json_encode($result));
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
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'http_code' => $http_code,
        'result' => json_decode($result, true)
    ];
}

writeLog("Browser webhook tamamlandı");
?>