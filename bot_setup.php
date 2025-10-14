<?php
require_once 'config/database.php';
require_once 'config/telegram.php';

echo "🤖 Telegram Bot Quraşdırılması\n\n";

// Veritabanını yarat
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    echo "❌ Veritabanı bağlantısı uğursuz!\n";
    echo "Zəhmət olmasa config/database.php faylında veritabanı məlumatlarını yoxlayın.\n";
    exit;
}

echo "✅ Veritabanı bağlantısı uğurlu\n";

// Cədvəlləri yarat
if (createTables($conn)) {
    echo "✅ Veritabanı cədvəlləri yaradıldı\n";
} else {
    echo "❌ Cədvəl yaratma xətası\n";
    exit;
}

// Əsas məlumatları doldur
insertDefaultData($conn);
echo "✅ Əsas məlumatlar əlavə edildi\n";

// Webhook-u quraşdır
$webhook_result = $telegram->setWebhook();
if ($webhook_result['ok']) {
    echo "✅ Webhook quraşdırıldı: {$WEBHOOK_URL}\n";
} else {
    echo "❌ Webhook quraşdırma xətası: " . $webhook_result['description'] . "\n";
}

// Webhook məlumatlarını yoxla
$webhook_info = $telegram->getWebhookInfo();
if ($webhook_info['ok']) {
    echo "📊 Webhook məlumatları:\n";
    echo "URL: " . $webhook_info['result']['url'] . "\n";
    echo "Pending updates: " . $webhook_info['result']['pending_update_count'] . "\n";
    echo "Last error: " . ($webhook_info['result']['last_error_message'] ?? 'Yoxdur') . "\n";
}

echo "\n🎉 Bot quraşdırılması tamamlandı!\n";
echo "\n📝 Növbəti addımlar:\n";
echo "1. Bot tokeninizi config/telegram.php faylında yeniləyin\n";
echo "2. Webhook URL-inizi config/telegram.php faylında yeniləyin\n";
echo "3. Webapp URL-inizi config/telegram.php faylında yeniləyin\n";
echo "4. SMM API məlumatlarını config/smm_api.php faylında təyin edin\n";
echo "5. Botu test edin!\n";
?>