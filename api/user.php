<?php
header('Content-Type: application/json');
require_once '../config/database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Giriş tələb olunur']);
    exit;
}

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'profile':
            getProfile($conn);
            break;
        case 'balance':
            getBalance($conn);
            break;
        case 'stats':
            getStats($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Etibarsız əməliyyat']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_profile':
            updateProfile($conn);
            break;
        case 'add_balance':
            addBalance($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Etibarsız əməliyyat']);
    }
}

function getProfile($conn) {
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $conn->prepare("SELECT id, telegram_id, username, first_name, last_name, email, balance, created_at FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo json_encode([
                'success' => true,
                'user' => $user
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'İstifadəçi tapılmadı'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Xəta: ' . $e->getMessage()
        ]);
    }
}

function getBalance($conn) {
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'balance' => $user['balance'] ?? 0
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Xəta: ' . $e->getMessage()
        ]);
    }
}

function getStats($conn) {
    $user_id = $_SESSION['user_id'];
    
    try {
        // Ümumi sifarişlər
        $stmt = $conn->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];
        
        // Aktiv sifarişlər
        $stmt = $conn->prepare("SELECT COUNT(*) as active_orders FROM orders WHERE user_id = ? AND status IN ('pending', 'in_progress')");
        $stmt->execute([$user_id]);
        $active_orders = $stmt->fetch(PDO::FETCH_ASSOC)['active_orders'];
        
        // Ümumi xidmətlər
        $stmt = $conn->prepare("SELECT COUNT(*) as total_services FROM services WHERE status = 'active'");
        $stmt->execute();
        $total_services = $stmt->fetch(PDO::FETCH_ASSOC)['total_services'];
        
        // Balans
        $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $balance = $stmt->fetch(PDO::FETCH_ASSOC)['balance'];
        
        echo json_encode([
            'success' => true,
            'stats' => [
                'total_orders' => $total_orders,
                'active_orders' => $active_orders,
                'total_services' => $total_services,
                'balance' => $balance
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Xəta: ' . $e->getMessage()
        ]);
    }
}

function updateProfile($conn) {
    $user_id = $_SESSION['user_id'];
    $email = $_POST['email'] ?? '';
    
    try {
        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$email, $user_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Profil uğurla yeniləndi'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Xəta: ' . $e->getMessage()
        ]);
    }
}

function addBalance($conn) {
    $user_id = $_SESSION['user_id'];
    $amount = $_POST['amount'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? '';
    
    if ($amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Məbləğ 0-dan böyük olmalıdır']);
        return;
    }
    
    try {
        // Balansı artır
        $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $user_id]);
        
        // Transaction qeydini yarat
        $stmt = $conn->prepare("
            INSERT INTO transactions (user_id, type, amount, description, status) 
            VALUES (?, 'deposit', ?, 'Balans artırıldı - ' . ?, 'completed')
        ");
        $stmt->execute([$user_id, $amount, $payment_method]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Balans uğurla artırıldı'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Xəta: ' . $e->getMessage()
        ]);
    }
}
?>