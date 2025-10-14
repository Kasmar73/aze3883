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
    getOrders($conn);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    createOrder($conn);
}

function getOrders($conn) {
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $conn->prepare("
            SELECT o.*, s.name as service_name, s.platform, c.name as category_name
            FROM orders o
            JOIN services s ON o.service_id = s.id
            JOIN categories c ON s.category_id = c.id
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'orders' => $orders
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Xəta: ' . $e->getMessage()
        ]);
    }
}

function createOrder($conn) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $user_id = $_SESSION['user_id'];
    $service_id = $input['service_id'] ?? null;
    $link = $input['link'] ?? null;
    $quantity = $input['quantity'] ?? null;
    
    if (!$service_id || !$link || !$quantity) {
        echo json_encode(['success' => false, 'message' => 'Bütün sahələr tələb olunur']);
        return;
    }
    
    try {
        // Xidmət məlumatlarını al
        $stmt = $conn->prepare("SELECT * FROM services WHERE id = ? AND status = 'active'");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$service) {
            echo json_encode(['success' => false, 'message' => 'Xidmət tapılmadı']);
            return;
        }
        
        // Miqdarı yoxla
        if ($quantity < $service['min_quantity'] || $quantity > $service['max_quantity']) {
            echo json_encode([
                'success' => false, 
                'message' => "Miqdar {$service['min_quantity']} - {$service['max_quantity']} arasında olmalıdır"
            ]);
            return;
        }
        
        // Qiyməti hesabla
        $price = $service['price'] * $quantity;
        
        // İstifadəçinin balansını yoxla
        $stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user['balance'] < $price) {
            echo json_encode(['success' => false, 'message' => 'Kifayət qədər balans yoxdur']);
            return;
        }
        
        // Balansı azalt
        $stmt = $conn->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$price, $user_id]);
        
        // Sifarişi yarat
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, service_id, link, quantity, price, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$user_id, $service_id, $link, $quantity, $price]);
        $order_id = $conn->lastInsertId();
        
        // Transaction qeydini yarat
        $stmt = $conn->prepare("
            INSERT INTO transactions (user_id, type, amount, description, status) 
            VALUES (?, 'order_payment', ?, 'Sifariş #' . ?, 'completed')
        ");
        $stmt->execute([$user_id, $price, $order_id]);
        
        // SMM API-yə göndər (bu hissə sonra əlavə ediləcək)
        $smm_result = sendToSMMAPI($service, $link, $quantity, $order_id);
        
        if ($smm_result['success']) {
            // Sifariş statusunu yenilə
            $stmt = $conn->prepare("UPDATE orders SET smm_order_id = ?, status = 'in_progress' WHERE id = ?");
            $stmt->execute([$smm_result['order_id'], $order_id]);
        }
        
        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'message' => 'Sifariş uğurla yaradıldı'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Xəta: ' . $e->getMessage()
        ]);
    }
}

function sendToSMMAPI($service, $link, $quantity, $order_id) {
    // Bu hissə SMM API inteqrasiyası üçündür
    // Həqiqi SMM API ilə əvəz edilməlidir
    
    // Nümunə API çağırışı
    $api_url = "https://example-smm-api.com/api/order";
    $api_key = "YOUR_SMM_API_KEY";
    
    $data = [
        'service' => $service['id'],
        'link' => $link,
        'quantity' => $quantity,
        'key' => $api_key
    ];
    
    // cURL ilə API-yə sorğu göndər
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($result && isset($result['order'])) {
        return [
            'success' => true,
            'order_id' => $result['order']
        ];
    } else {
        return [
            'success' => false,
            'message' => 'SMM API xətası'
        ];
    }
}
?>