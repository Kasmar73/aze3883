<?php
// Sadə quraşdırma skripti
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
        .container { max-width: 800px; margin: 50px auto; }
        .step { margin-bottom: 30px; }
        .step-header { background: linear-gradient(45deg, #007bff, #0056b3); color: white; padding: 15px; border-radius: 8px; margin-bottom: 15px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .code { background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
<div class='container'>
    <div class='text-center mb-5'>
        <h1>🤖 SMM Panel Quraşdırma</h1>
        <p class='lead'>Telegram Bot üçün SMM Panel Webapp</p>
    </div>";

// Step 1: Fayl icazələrini yoxla
echo "<div class='step'>
    <div class='step-header'>
        <h4>1. Fayl İcazələri Yoxlanılması</h4>
    </div>";

$writable_dirs = ['config', 'api', 'assets', 'logs'];
$permissions_ok = true;

foreach ($writable_dirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p class='success'>✅ {$dir}/ qovluğu yaradıldı</p>";
        } else {
            echo "<p class='error'>❌ {$dir}/ qovluğu yaradıla bilmədi</p>";
            $permissions_ok = false;
        }
    } else {
        echo "<p class='success'>✅ {$dir}/ qovluğu mövcuddur</p>";
    }
    
    if (is_writable($dir)) {
        echo "<p class='success'>✅ {$dir}/ qovluğu yazılabilir</p>";
    } else {
        echo "<p class='error'>❌ {$dir}/ qovluğu yazıla bilmir</p>";
        echo "<p class='warning'>Həll: <code>chmod 755 {$dir}</code> əmrini işlədin</p>";
        $permissions_ok = false;
    }
}

if (!$permissions_ok) {
    echo "<div class='alert alert-warning'>
        <h5>⚠️ Fayl İcazələri Problemi</h5>
        <p>Aşağıdakı əmrləri terminaldə işlədin:</p>
        <div class='code'>
            chmod 755 config<br>
            chmod 755 api<br>
            chmod 755 assets<br>
            chmod 755 logs<br>
            chmod 644 config/*.php
        </div>
    </div>";
}

// Step 2: Veritabanı konfiqurasiyası
echo "</div><div class='step'>
    <div class='step-header'>
        <h4>2. Veritabanı Konfiqurasiyası</h4>
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
}

// Veritabanı cədvəllərini yaratmaq üçün SQL
function createTables(\$conn) {
    \$sql = \"
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        telegram_id BIGINT UNIQUE NOT NULL,
        username VARCHAR(255),
        first_name VARCHAR(255),
        last_name VARCHAR(255),
        email VARCHAR(255),
        balance DECIMAL(10,2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        icon VARCHAR(255),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,4) NOT NULL,
        min_quantity INT DEFAULT 1,
        max_quantity INT DEFAULT 1000,
        service_type ENUM('followers', 'likes', 'views', 'comments', 'shares') NOT NULL,
        platform VARCHAR(100) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    );

    CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        service_id INT NOT NULL,
        link VARCHAR(500) NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,4) NOT NULL,
        status ENUM('pending', 'in_progress', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
        smm_order_id VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (service_id) REFERENCES services(id)
    );

    CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type ENUM('deposit', 'withdrawal', 'order_payment') NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        description TEXT,
        status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    );

    CREATE TABLE IF NOT EXISTS smm_api_config (
        id INT AUTO_INCREMENT PRIMARY KEY,
        api_name VARCHAR(100) NOT NULL,
        api_url VARCHAR(500) NOT NULL,
        api_key VARCHAR(500) NOT NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    \";

    try {
        \$conn->exec(\$sql);
        return true;
    } catch(PDOException \$e) {
        echo \"Cədvəl yaratma xətası: \" . \$e->getMessage();
        return false;
    }
}

// Əsas məlumatları doldurmaq
function insertDefaultData(\$conn) {
    // Kateqoriyalar
    \$categories = [
        ['Instagram', 'Instagram xidmətləri', 'fab fa-instagram'],
        ['Facebook', 'Facebook xidmətləri', 'fab fa-facebook'],
        ['YouTube', 'YouTube xidmətləri', 'fab fa-youtube'],
        ['TikTok', 'TikTok xidmətləri', 'fab fa-tiktok'],
        ['Twitter', 'Twitter xidmətləri', 'fab fa-twitter']
    ];

    foreach (\$categories as \$category) {
        \$stmt = \$conn->prepare(\"INSERT IGNORE INTO categories (name, description, icon) VALUES (?, ?, ?)\");
        \$stmt->execute(\$category);
    }

    // Nümunə xidmətlər
    \$services = [
        [1, 'Instagram Followers', 'Real Instagram followers', 0.50, 100, 10000, 'followers', 'Instagram'],
        [1, 'Instagram Likes', 'Instagram post likes', 0.30, 50, 5000, 'likes', 'Instagram'],
        [1, 'Instagram Views', 'Instagram video views', 0.20, 100, 10000, 'views', 'Instagram'],
        [2, 'Facebook Likes', 'Facebook page likes', 0.40, 100, 5000, 'likes', 'Facebook'],
        [3, 'YouTube Views', 'YouTube video views', 0.10, 1000, 100000, 'views', 'YouTube'],
        [4, 'TikTok Followers', 'TikTok followers', 0.60, 100, 10000, 'followers', 'TikTok']
    ];

    foreach (\$services as \$service) {
        \$stmt = \$conn->prepare(\"INSERT IGNORE INTO services (category_id, name, description, price, min_quantity, max_quantity, service_type, platform) VALUES (?, ?, ?, ?, ?, ?, ?, ?)\");
        \$stmt->execute(\$service);
    }
}
?>";
        
        if (file_put_contents('config/database.php', $config_content)) {
            chmod('config/database.php', 0644);
            echo "<p class='success'>✅ Veritabanı konfiqurasiyası yaradıldı</p>";
            
            // Cədvəlləri yarat
            require_once 'config/database.php';
            $database = new Database();
            $conn = $database->getConnection();
            
            if (createTables($conn)) {
                echo "<p class='success'>✅ Veritabanı cədvəlləri yaradıldı</p>";
                insertDefaultData($conn);
                echo "<p class='success'>✅ Əsas məlumatlar əlavə edildi</p>";
            } else {
                echo "<p class='error'>❌ Cədvəl yaratma xətası</p>";
            }
        } else {
            echo "<p class='error'>❌ Veritabanı konfiqurasiyası yaradıla bilmədi</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p class='error'>❌ Veritabanı bağlantı xətası: " . $e->getMessage() . "</p>";
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

// Step 3: Bot konfiqurasiyası
echo "</div><div class='step'>
    <div class='step-header'>
        <h4>3. Telegram Bot Konfiqurasiyası</h4>
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
        \$data = ['url' => \$webhook_url];
        
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
        
        // Webhook-u quraşdır
        $telegram = new TelegramBot($bot_token, $webhook_url);
        $webhook_result = $telegram->setWebhook();
        
        if ($webhook_result['ok']) {
            echo "<p class='success'>✅ Webhook quraşdırıldı</p>";
        } else {
            echo "<p class='error'>❌ Webhook xətası: " . $webhook_result['description'] . "</p>";
        }
    } else {
        echo "<p class='error'>❌ Bot konfiqurasiyası yaradıla bilmədi</p>";
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
            <input type='url' class='form-control' name='webhook_url' placeholder='https://smmaze.duckdns.org/webhook.php' required>
        </div>
        <div class='col-12'>
            <label class='form-label'>Webapp URL</label>
            <input type='url' class='form-control' name='webapp_url' placeholder='https://smmaze.duckdns.org/index.php' required>
        </div>
        <div class='col-12'>
            <button type='submit' class='btn btn-primary'>Bot Konfiqurasiyasını Yadda Saxla</button>
        </div>
    </form>";
}

// Nəticə
echo "</div><div class='step'>
    <div class='step-header'>
        <h4>🎉 Quraşdırma Tamamlandı!</h4>
    </div>
    <div class='alert alert-success'>
        <h5>✅ SMM Panel hazırdır!</h5>
        <p>İndi botunuzu istifadə edə bilərsiniz.</p>
        <hr>
        <h6>Növbəti addımlar:</h6>
        <ul>
            <li>Botu Telegram-da tapın və /start yazın</li>
            <li>SMM API məlumatlarını config/smm_api.php faylında təyin edin</li>
            <li>Cron job əlavə edin: <code>*/5 * * * * php /path/to/cron.php</code></li>
        </ul>
    </div>
</div></div>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>