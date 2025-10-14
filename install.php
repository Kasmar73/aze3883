<?php
// SMM Panel Quraşdırma Skripti
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='az'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>SMM Panel Quraşdırma</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background-color: #f8f9fa; }
        .install-container { max-width: 800px; margin: 50px auto; }
        .step { margin-bottom: 30px; }
        .step-header { background: linear-gradient(45deg, #007bff, #0056b3); color: white; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
    </style>
</head>
<body>
<div class='container install-container'>
    <div class='text-center mb-5'>
        <h1>🤖 SMM Panel Quraşdırma</h1>
        <p class='lead'>Telegram Bot üçün SMM Panel Webapp</p>
    </div>";

$steps = [];
$all_success = true;

// Step 1: PHP versiyasını yoxla
echo "<div class='step'>
    <div class='step-header'>
        <h4>1. PHP Versiyası Yoxlanılması</h4>
    </div>";

$php_version = phpversion();
if (version_compare($php_version, '7.4.0', '>=')) {
    echo "<p class='success'>✅ PHP versiyası: {$php_version} (Uyğundur)</p>";
    $steps[] = ['name' => 'PHP Version', 'status' => 'success'];
} else {
    echo "<p class='error'>❌ PHP versiyası: {$php_version} (7.4+ tələb olunur)</p>";
    $steps[] = ['name' => 'PHP Version', 'status' => 'error'];
    $all_success = false;
}

// Step 2: Tələb olunan extension-ları yoxla
echo "</div><div class='step'>
    <div class='step-header'>
        <h4>2. PHP Extension-ları Yoxlanılması</h4>
    </div>";

$required_extensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring'];
$extensions_ok = true;

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ {$ext} extension yüklüdür</p>";
    } else {
        echo "<p class='error'>❌ {$ext} extension yüklü deyil</p>";
        $extensions_ok = false;
        $all_success = false;
    }
}

$steps[] = ['name' => 'PHP Extensions', 'status' => $extensions_ok ? 'success' : 'error'];

// Step 3: Fayl icazələrini yoxla
echo "</div><div class='step'>
    <div class='step-header'>
        <h4>3. Fayl İcazələri Yoxlanılması</h4>
    </div>";

$writable_dirs = ['assets', 'config', 'api', 'logs'];
$permissions_ok = true;

foreach ($writable_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    if (is_writable($dir)) {
        echo "<p class='success'>✅ {$dir}/ qovluğu yazılabilir</p>";
    } else {
        echo "<p class='error'>❌ {$dir}/ qovluğu yazıla bilmir (chmod 755 tələb olunur)</p>";
        $permissions_ok = false;
        $all_success = false;
    }
}

$steps[] = ['name' => 'File Permissions', 'status' => $permissions_ok ? 'success' : 'error'];

// Step 4: Veritabanı bağlantısını yoxla
echo "</div><div class='step'>
    <div class='step-header'>
        <h4>4. Veritabanı Bağlantısı</h4>
    </div>";

if (isset($_POST['db_host']) && isset($_POST['db_name']) && isset($_POST['db_user'])) {
    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'] ?? '';
    
    try {
        $pdo = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<p class='success'>✅ Veritabanı bağlantısı uğurlu</p>";
        
        // Veritabanı konfiqurasiyasını yarat
        $config_content = "<?php
// Veritabanı konfiqurasiyası
class Database {
    private \$host = '{$db_host}';
    private \$db_name = '{$db_name}';
    private \$username = '{$db_user}';
    private \$password = '{$db_pass}';
    private \$conn;

    public function getConnection() {
        \$this->conn = null;
        
        try {
            \$this->conn = new PDO(\"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name, 
                                \$this->username, \$this->password);
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            \$this->conn->exec(\"set names utf8\");
        } catch(PDOException \$exception) {
            echo \"Bağlantı xətası: \" . \$exception->getMessage();
        }
        
        return \$this->conn;
    }
}";
        
        if (file_put_contents('config/database.php', $config_content)) {
            chmod('config/database.php', 0644);
            echo "<p class='success'>✅ Veritabanı konfiqurasiyası yaradıldı</p>";
        } else {
            echo "<p class='error'>❌ Veritabanı konfiqurasiyası yaradıla bilmədi. Fayl icazələrini yoxlayın.</p>";
            $all_success = false;
        }
        
        // Cədvəlləri yarat
        require_once 'config/database.php';
        $database = new Database();
        $conn = $database->getConnection();
        
        if (createTables($conn)) {
            echo "<p class='success'>✅ Veritabanı cədvəlləri yaradıldı</p>";
            insertDefaultData($conn);
            echo "<p class='success'>✅ Əsas məlumatlar əlavə edildi</p>";
            $steps[] = ['name' => 'Database Setup', 'status' => 'success'];
        } else {
            echo "<p class='error'>❌ Cədvəl yaratma xətası</p>";
            $steps[] = ['name' => 'Database Setup', 'status' => 'error'];
            $all_success = false;
        }
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Veritabanı bağlantı xətası: " . $e->getMessage() . "</p>";
        $steps[] = ['name' => 'Database Setup', 'status' => 'error'];
        $all_success = false;
    }
} else {
    echo "<form method='POST' class='row g-3'>
        <div class='col-md-6'>
            <label class='form-label'>Veritabanı Host</label>
            <input type='text' class='form-control' name='db_host' value='localhost' required>
        </div>
        <div class='col-md-6'>
            <label class='form-label'>Veritabanı Adı</label>
            <input type='text' class='form-control' name='db_name' value='smm_panel' required>
        </div>
        <div class='col-md-6'>
            <label class='form-label'>İstifadəçi Adı</label>
            <input type='text' class='form-control' name='db_user' required>
        </div>
        <div class='col-md-6'>
            <label class='form-label'>Şifrə</label>
            <input type='password' class='form-control' name='db_pass'>
        </div>
        <div class='col-12'>
            <button type='submit' class='btn btn-primary'>Veritabanını Test Et</button>
        </div>
    </form>";
}

// Step 5: Bot konfiqurasiyası
echo "</div><div class='step'>
    <div class='step-header'>
        <h4>5. Telegram Bot Konfiqurasiyası</h4>
    </div>";

if (isset($_POST['bot_token']) && isset($_POST['webhook_url']) && isset($_POST['webapp_url'])) {
    $bot_token = $_POST['bot_token'];
    $webhook_url = $_POST['webhook_url'];
    $webapp_url = $_POST['webapp_url'];
    
    // Bot konfiqurasiyasını yarat
    $telegram_config = "<?php
// Telegram Bot konfiqurasiyası
class TelegramBot {
    private \$bot_token;
    private \$webhook_url;
    
    public function __construct(\$bot_token, \$webhook_url) {
        \$this->bot_token = \$bot_token;
        \$this->webhook_url = \$webhook_url;
    }
    
    public function sendMessage(\$chat_id, \$text, \$reply_markup = null) {
        \$url = \"https://api.telegram.org/bot{\$this->bot_token}/sendMessage\";
        
        \$data = [
            'chat_id' => \$chat_id,
            'text' => \$text,
            'parse_mode' => 'HTML'
        ];
        
        if (\$reply_markup) {
            \$data['reply_markup'] = json_encode(\$reply_markup);
        }
        
        return \$this->makeRequest(\$url, \$data);
    }
    
    public function sendWebApp(\$chat_id, \$text, \$web_app_url) {
        \$url = \"https://api.telegram.org/bot{\$this->bot_token}/sendMessage\";
        
        \$keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => 'SMM Panel Aç',
                        'web_app' => ['url' => \$web_app_url]
                    ]
                ]
            ]
        ];
        
        \$data = [
            'chat_id' => \$chat_id,
            'text' => \$text,
            'reply_markup' => json_encode(\$keyboard)
        ];
        
        return \$this->makeRequest(\$url, \$data);
    }
    
    public function setWebhook() {
        \$url = \"https://api.telegram.org/bot{\$this->bot_token}/setWebhook\";
        \$data = ['url' => \$this->webhook_url];
        
        return \$this->makeRequest(\$url, \$data);
    }
    
    public function getWebhookInfo() {
        \$url = \"https://api.telegram.org/bot{\$this->bot_token}/getWebhookInfo\";
        return \$this->makeRequest(\$url, []);
    }
    
    private function makeRequest(\$url, \$data) {
        \$ch = curl_init();
        curl_setopt(\$ch, CURLOPT_URL, \$url);
        curl_setopt(\$ch, CURLOPT_POST, true);
        curl_setopt(\$ch, CURLOPT_POSTFIELDS, http_build_query(\$data));
        curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(\$ch, CURLOPT_SSL_VERIFYPEER, false);
        
        \$result = curl_exec(\$ch);
        curl_close(\$ch);
        
        return json_decode(\$result, true);
    }
}

// Bot konfiqurasiyası
\$BOT_TOKEN = \"{$bot_token}\";
\$WEBHOOK_URL = \"{$webhook_url}\";
\$WEBAPP_URL = \"{$webapp_url}\";

\$telegram = new TelegramBot(\$BOT_TOKEN, \$WEBHOOK_URL);
?>";
    
    if (file_put_contents('config/telegram.php', $telegram_config)) {
        chmod('config/telegram.php', 0644);
        echo "<p class='success'>✅ Bot konfiqurasiyası yaradıldı</p>";
    } else {
        echo "<p class='error'>❌ Bot konfiqurasiyası yaradıla bilmədi. Fayl icazələrini yoxlayın.</p>";
        $all_success = false;
    }
    
    // Webhook-u quraşdır
    $telegram = new TelegramBot($bot_token, $webhook_url);
    $webhook_result = $telegram->setWebhook();
    
    if ($webhook_result['ok']) {
        echo "<p class='success'>✅ Webhook quraşdırıldı</p>";
        $steps[] = ['name' => 'Bot Configuration', 'status' => 'success'];
    } else {
        echo "<p class='error'>❌ Webhook xətası: " . $webhook_result['description'] . "</p>";
        $steps[] = ['name' => 'Bot Configuration', 'status' => 'error'];
        $all_success = false;
    }
    
} else {
    echo "<form method='POST' class='row g-3'>
        <div class='col-12'>
            <label class='form-label'>Bot Token</label>
            <input type='text' class='form-control' name='bot_token' placeholder='123456789:ABCdefGHIjklMNOpqrsTUVwxyz' required>
            <div class='form-text'>@BotFather-dən alın</div>
        </div>
        <div class='col-12'>
            <label class='form-label'>Webhook URL</label>
            <input type='url' class='form-control' name='webhook_url' placeholder='https://yourdomain.com/webhook.php' required>
        </div>
        <div class='col-12'>
            <label class='form-label'>Webapp URL</label>
            <input type='url' class='form-control' name='webapp_url' placeholder='https://yourdomain.com/index.php' required>
        </div>
        <div class='col-12'>
            <button type='submit' class='btn btn-primary'>Bot Konfiqurasiyasını Yadda Saxla</button>
        </div>
    </form>";
}

// Nəticə
echo "</div><div class='step'>
    <div class='step-header'>
        <h4>📊 Quraşdırma Nəticəsi</h4>
    </div>";

$success_count = 0;
foreach ($steps as $step) {
    if ($step['status'] === 'success') {
        $success_count++;
    }
}

if ($all_success && count($steps) >= 4) {
    echo "<div class='alert alert-success'>
        <h5>🎉 Quraşdırma Tamamlandı!</h5>
        <p>Bütün addımlar uğurla tamamlandı. İndi botunuzu istifadə edə bilərsiniz.</p>
        <hr>
        <h6>Növbəti addımlar:</h6>
        <ul>
            <li>Botu Telegram-da tapın və /start yazın</li>
            <li>SMM API məlumatlarını config/smm_api.php faylında təyin edin</li>
            <li>Cron job-ları quraşdırın (tövsiyə olunur)</li>
        </ul>
    </div>";
} else {
    echo "<div class='alert alert-warning'>
        <h5>⚠️ Quraşdırma Tamamlanmadı</h5>
        <p>Bəzi addımlar uğursuz oldu. Zəhmət olmasa xətaları düzəldin və yenidən cəhd edin.</p>
    </div>";
}

echo "</div></div>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>