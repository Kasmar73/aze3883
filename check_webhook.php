<?php
// Webhook statusunu yoxlamaq üçün
$BOT_TOKEN = "YOUR_BOT_TOKEN_HERE"; // Buraya bot tokeninizi yazın

function checkWebhook($bot_token) {
    $url = "https://api.telegram.org/bot{$bot_token}/getWebhookInfo";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($result, true);
}

function setWebhook($bot_token, $webhook_url) {
    $url = "https://api.telegram.org/bot{$bot_token}/setWebhook";
    $data = ['url' => $webhook_url];
    
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

// Webhook statusunu yoxla
$webhook_info = checkWebhook($BOT_TOKEN);

echo "<h2>Webhook Status</h2>";
echo "<pre>" . json_encode($webhook_info, JSON_PRETTY_PRINT) . "</pre>";

if ($webhook_info['ok']) {
    $result = $webhook_info['result'];
    
    echo "<h3>Webhook Məlumatları:</h3>";
    echo "<p><strong>URL:</strong> " . ($result['url'] ?? 'Yoxdur') . "</p>";
    echo "<p><strong>Pending Updates:</strong> " . ($result['pending_update_count'] ?? 0) . "</p>";
    
    if (isset($result['last_error_message'])) {
        echo "<p><strong>Son Xəta:</strong> " . $result['last_error_message'] . "</p>";
        echo "<p><strong>Xəta Tarixi:</strong> " . date('Y-m-d H:i:s', $result['last_error_date']) . "</p>";
    }
    
    // Webhook-u yenilə
    echo "<h3>Webhook Yenilə</h3>";
    
    if (isset($_GET['update_webhook'])) {
        $webhook_url = "https://smmaze.duckdns.org/webhook_simple.php";
        $set_result = setWebhook($BOT_TOKEN, $webhook_url);
        
        echo "<h4>Webhook Yeniləndi:</h4>";
        echo "<pre>" . json_encode($set_result, JSON_PRETTY_PRINT) . "</pre>";
        
        if ($set_result['ok']) {
            echo "<p style='color: green;'>✅ Webhook uğurla yeniləndi!</p>";
        } else {
            echo "<p style='color: red;'>❌ Webhook yenilənmədi: " . $set_result['description'] . "</p>";
        }
    } else {
        echo "<a href='?update_webhook=1' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Webhook-u Yenilə</a>";
    }
    
    // Pending updates-ləri təmizlə
    if (isset($_GET['clear_updates']) && $result['pending_update_count'] > 0) {
        $clear_url = "https://api.telegram.org/bot{$BOT_TOKEN}/deleteWebhook?drop_pending_updates=true";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $clear_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $clear_result = curl_exec($ch);
        curl_close($ch);
        
        $clear_data = json_decode($clear_result, true);
        
        echo "<h4>Pending Updates Təmizləndi:</h4>";
        echo "<pre>" . json_encode($clear_data, JSON_PRETTY_PRINT) . "</pre>";
        
        if ($clear_data['ok']) {
            echo "<p style='color: green;'>✅ Pending updates təmizləndi!</p>";
        }
    } elseif ($result['pending_update_count'] > 0) {
        echo "<a href='?clear_updates=1' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Pending Updates Təmizlə</a>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Webhook məlumatları alına bilmədi: " . $webhook_info['description'] . "</p>";
}

// Bot məlumatlarını yoxla
function getBotInfo($bot_token) {
    $url = "https://api.telegram.org/bot{$bot_token}/getMe";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($result, true);
}

$bot_info = getBotInfo($BOT_TOKEN);

echo "<h2>Bot Məlumatları</h2>";
echo "<pre>" . json_encode($bot_info, JSON_PRETTY_PRINT) . "</pre>";

if ($bot_info['ok']) {
    $bot = $bot_info['result'];
    echo "<p><strong>Bot Adı:</strong> " . $bot['first_name'] . "</p>";
    echo "<p><strong>Bot Username:</strong> @" . $bot['username'] . "</p>";
    echo "<p><strong>Bot ID:</strong> " . $bot['id'] . "</p>";
}
?>