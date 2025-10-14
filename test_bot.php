<?php
// Bot test faylı
header('Content-Type: application/json');

// Bot token (buraya öz tokeninizi yazın)
$bot_token = "7739231947:YOUR_BOT_TOKEN_HERE";

// Bot məlumatlarını yoxla
function getBotInfo($bot_token) {
    $url = "https://api.telegram.org/bot{$bot_token}/getMe";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
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

// Test mesajı göndər
function sendTestMessage($bot_token, $chat_id) {
    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => "✅ Bot işləyir!\n\nTarix: " . date('Y-m-d H:i:s')
    ];
    
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

// Bot məlumatlarını yoxla
$bot_info = getBotInfo($bot_token);

echo "<h2>Bot Test Nəticələri</h2>";

if ($bot_info['http_code'] === 200 && $bot_info['result']['ok']) {
    echo "<p style='color: green;'>✅ Bot token düzgündür!</p>";
    echo "<pre>" . json_encode($bot_info['result'], JSON_PRETTY_PRINT) . "</pre>";
    
    // Test mesajı göndər
    if (isset($_GET['chat_id'])) {
        $chat_id = $_GET['chat_id'];
        $test_result = sendTestMessage($bot_token, $chat_id);
        
        echo "<h3>Test Mesajı Nəticəsi:</h3>";
        echo "<pre>" . json_encode($test_result, JSON_PRETTY_PRINT) . "</pre>";
        
        if ($test_result['http_code'] === 200 && $test_result['result']['ok']) {
            echo "<p style='color: green;'>✅ Test mesajı göndərildi!</p>";
        } else {
            echo "<p style='color: red;'>❌ Test mesajı göndərilmədi!</p>";
        }
    } else {
        echo "<h3>Test Mesajı Göndərmək üçün:</h3>";
        echo "<p>Bu URL-i açın: <a href='?chat_id=1143980741'>Test Mesajı Göndər</a></p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Bot token düzgün deyil!</p>";
    echo "<pre>" . json_encode($bot_info, JSON_PRETTY_PRINT) . "</pre>";
}

// Webhook statusunu yoxla
function getWebhookInfo($bot_token) {
    $url = "https://api.telegram.org/bot{$bot_token}/getWebhookInfo";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $result = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($result, true);
}

$webhook_info = getWebhookInfo($bot_token);

echo "<h2>Webhook Status</h2>";
echo "<pre>" . json_encode($webhook_info, JSON_PRETTY_PRINT) . "</pre>";

if ($webhook_info['ok']) {
    $result = $webhook_info['result'];
    
    if (isset($result['last_error_message'])) {
        echo "<p style='color: red;'>❌ Webhook xətası: " . $result['last_error_message'] . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Webhook düzgün işləyir!</p>";
    }
}
?>