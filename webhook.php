<?php
require_once 'config/database.php';
require_once 'config/telegram.php';

// Webhook məlumatlarını al
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!$update) {
    http_response_code(400);
    exit('Invalid JSON');
}

// Veritabanını başlat
$database = new Database();
$conn = $database->getConnection();
createTables($conn);
insertDefaultData($conn);

// Mesajı emal et
if (isset($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $user = $message['from'];
    
    // İstifadəçini veritabanına əlavə et
    $stmt = $conn->prepare("
        INSERT IGNORE INTO users (telegram_id, username, first_name, last_name) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $user['id'],
        $user['username'] ?? null,
        $user['first_name'] ?? null,
        $user['last_name'] ?? null
    ]);
    
    switch ($text) {
        case '/start':
            $welcome_text = "🎉 Salam! SMM Panel-ə xoş gəlmisiniz!\n\n";
            $welcome_text .= "Bu bot ilə sosial media xidmətləri sifariş edə bilərsiniz:\n";
            $welcome_text .= "• Instagram followers, likes, views\n";
            $welcome_text .= "• Facebook likes, followers\n";
            $welcome_text .= "• YouTube views, subscribers\n";
            $welcome_text .= "• TikTok followers, likes\n";
            $welcome_text .= "• Twitter followers, retweets\n\n";
            $welcome_text .= "Panel-i açmaq üçün aşağıdakı düyməni basın:";
            
            $telegram->sendWebApp($chat_id, $welcome_text, $WEBAPP_URL);
            break;
            
        case '/help':
            $help_text = "📋 Yardım\n\n";
            $help_text .= "/start - Botu başlat\n";
            $help_text .= "/help - Bu yardım mesajı\n";
            $help_text .= "/balance - Balansınızı yoxlayın\n";
            $help_text .= "/orders - Sifarişlərinizi görün\n";
            $help_text .= "/panel - SMM panelini açın\n\n";
            $help_text .= "Suallarınız üçün: @support_username";
            
            $telegram->sendMessage($chat_id, $help_text);
            break;
            
        case '/balance':
            $user_id = getUserByTelegramId($conn, $user['id']);
            if ($user_id) {
                $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $balance = $stmt->fetch(PDO::FETCH_ASSOC)['balance'];
                
                $balance_text = "💰 Balansınız: " . number_format($balance, 2) . " AZN\n\n";
                $balance_text .= "Balans artırmaq üçün panel-i açın və 'Balans' bölməsinə keçin.";
                
                $telegram->sendWebApp($chat_id, $balance_text, $WEBAPP_URL);
            }
            break;
            
        case '/orders':
            $user_id = getUserByTelegramId($conn, $user['id']);
            if ($user_id) {
                $orders_text = "📋 Son Sifarişləriniz:\n\n";
                
                $stmt = $conn->prepare("
                    SELECT o.*, s.name as service_name, s.platform
                    FROM orders o
                    JOIN services s ON o.service_id = s.id
                    WHERE o.user_id = ?
                    ORDER BY o.created_at DESC
                    LIMIT 5
                ");
                $stmt->execute([$user_id]);
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($orders)) {
                    $orders_text .= "Hələ sifarişiniz yoxdur.\n";
                    $orders_text .= "İlk sifarişinizi vermək üçün panel-i açın.";
                } else {
                    foreach ($orders as $order) {
                        $status_emoji = getStatusEmoji($order['status']);
                        $orders_text .= "{$status_emoji} {$order['service_name']}\n";
                        $orders_text .= "Miqdar: {$order['quantity']}\n";
                        $orders_text .= "Qiymət: " . number_format($order['price'], 2) . " AZN\n";
                        $orders_text .= "Status: " . getStatusText($order['status']) . "\n\n";
                    }
                }
                
                $telegram->sendWebApp($chat_id, $orders_text, $WEBAPP_URL);
            }
            break;
            
        case '/panel':
            $telegram->sendWebApp($chat_id, "🚀 SMM Panel-i açmaq üçün aşağıdakı düyməni basın:", $WEBAPP_URL);
            break;
            
        default:
            $default_text = "❓ Məlum olmayan əmr.\n\n";
            $default_text .= "Yardım üçün /help yazın.\n";
            $default_text .= "Panel-i açmaq üçün /panel yazın.";
            
            $telegram->sendMessage($chat_id, $default_text);
    }
}

// Callback query-ləri emal et
if (isset($update['callback_query'])) {
    $callback_query = $update['callback_query'];
    $chat_id = $callback_query['message']['chat']['id'];
    $data = $callback_query['data'];
    
    // Callback query cavabını göndər
    $telegram->makeRequest("https://api.telegram.org/bot{$BOT_TOKEN}/answerCallbackQuery", [
        'callback_query_id' => $callback_query['id']
    ]);
}

function getUserByTelegramId($conn, $telegram_id) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE telegram_id = ?");
    $stmt->execute([$telegram_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ? $user['id'] : null;
}

function getStatusEmoji($status) {
    switch ($status) {
        case 'pending': return '⏳';
        case 'in_progress': return '🔄';
        case 'completed': return '✅';
        case 'cancelled': return '❌';
        case 'refunded': return '💰';
        default: return '❓';
    }
}

function getStatusText($status) {
    switch ($status) {
        case 'pending': return 'Gözləyir';
        case 'in_progress': return 'İşlənir';
        case 'completed': return 'Tamamlandı';
        case 'cancelled': return 'Ləğv edildi';
        case 'refunded': return 'Geri qaytarıldı';
        default: return 'Naməlum';
    }
}

http_response_code(200);
echo 'OK';
?>