<?php
header('Content-Type: application/json');
require_once '../config/database.php';

// Telegram webapp init data-nı yoxlamaq
function validateTelegramData($init_data) {
    $bot_token = "YOUR_BOT_TOKEN_HERE"; // Bot tokeninizi buraya yazın
    
    $data_check_arr = [];
    parse_str($init_data, $data_check_arr);
    
    $hash = $data_check_arr['hash'];
    unset($data_check_arr['hash']);
    
    ksort($data_check_arr);
    $data_check_string = '';
    foreach ($data_check_arr as $key => $value) {
        $data_check_string .= $key . '=' . $value . "\n";
    }
    
    $secret_key = hash('sha256', $bot_token, true);
    $hash_check = hash_hmac('sha256', $data_check_string, $secret_key);
    
    return hash_equals($hash, $hash_check);
}

// İstifadəçini yaratmaq və ya yeniləmək
function createOrUpdateUser($user_data) {
    $database = new Database();
    $conn = $database->getConnection();
    
    $telegram_id = $user_data['id'];
    $username = $user_data['username'] ?? null;
    $first_name = $user_data['first_name'] ?? null;
    $last_name = $user_data['last_name'] ?? null;
    
    // İstifadəçinin mövcud olub-olmadığını yoxla
    $stmt = $conn->prepare("SELECT id FROM users WHERE telegram_id = ?");
    $stmt->execute([$telegram_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // İstifadəçini yenilə
        $stmt = $conn->prepare("UPDATE users SET username = ?, first_name = ?, last_name = ?, updated_at = CURRENT_TIMESTAMP WHERE telegram_id = ?");
        $stmt->execute([$username, $first_name, $last_name, $telegram_id]);
        return $user['id'];
    } else {
        // Yeni istifadəçi yarat
        $stmt = $conn->prepare("INSERT INTO users (telegram_id, username, first_name, last_name) VALUES (?, ?, ?, ?)");
        $stmt->execute([$telegram_id, $username, $first_name, $last_name]);
        return $conn->lastInsertId();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['init_data'])) {
        if (validateTelegramData($input['init_data'])) {
            parse_str($input['init_data'], $user_data);
            
            if (isset($user_data['user'])) {
                $user = json_decode($user_data['user'], true);
                $user_id = createOrUpdateUser($user);
                
                // Session başlat
                session_start();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['telegram_id'] = $user['id'];
                $_SESSION['username'] = $user['username'] ?? '';
                
                echo json_encode([
                    'success' => true,
                    'user_id' => $user_id,
                    'message' => 'Giriş uğurlu'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'İstifadəçi məlumatları tapılmadı'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Etibarsız Telegram məlumatları'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'init_data tələb olunur'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Yalnız POST sorğuları qəbul edilir'
    ]);
}
?>