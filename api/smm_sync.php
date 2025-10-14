<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/smm_api.php';

// SMM API ilə sinxronizasiya
$database = new Database();
$conn = $database->getConnection();
$smm_manager = new SMMAPIManager($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'sync_services':
            syncServices($conn, $smm_manager);
            break;
        case 'update_order_status':
            updateOrderStatus($conn, $smm_manager);
            break;
        case 'sync_balance':
            syncBalance($conn, $smm_manager);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Etibarsız əməliyyat']);
    }
}

function syncServices($conn, $smm_manager) {
    try {
        $apis = $smm_manager->getAllAPIs();
        $synced_count = 0;
        
        foreach ($apis as $api_name => $api) {
            $result = $api->getServices();
            
            if ($result['success']) {
                foreach ($result['services'] as $service) {
                    // Xidməti veritabanına əlavə et və ya yenilə
                    $stmt = $conn->prepare("
                        INSERT INTO services (name, description, price, min_quantity, max_quantity, service_type, platform, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
                        ON DUPLICATE KEY UPDATE 
                        price = VALUES(price),
                        min_quantity = VALUES(min_quantity),
                        max_quantity = VALUES(max_quantity),
                        status = 'active'
                    ");
                    
                    $stmt->execute([
                        $service['name'] ?? 'Unknown Service',
                        $service['description'] ?? '',
                        $service['rate'] ?? 0,
                        $service['min'] ?? 1,
                        $service['max'] ?? 1000,
                        $service['type'] ?? 'followers',
                        $service['category'] ?? 'Other'
                    ]);
                    
                    $synced_count++;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => "{$synced_count} xidmət sinxronizasiya edildi"
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Sinxronizasiya xətası: ' . $e->getMessage()
        ]);
    }
}

function updateOrderStatus($conn, $smm_manager) {
    try {
        // Gözləyən və ya işləyən sifarişləri al
        $stmt = $conn->prepare("
            SELECT id, smm_order_id, status 
            FROM orders 
            WHERE status IN ('pending', 'in_progress') 
            AND smm_order_id IS NOT NULL
        ");
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $updated_count = 0;
        
        foreach ($orders as $order) {
            $result = $smm_manager->getOrderStatus($order['smm_order_id']);
            
            if ($result['success']) {
                $new_status = mapSMMStatus($result['status']);
                
                if ($new_status !== $order['status']) {
                    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
                    $stmt->execute([$new_status, $order['id']]);
                    $updated_count++;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => "{$updated_count} sifariş yeniləndi"
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Status yeniləmə xətası: ' . $e->getMessage()
        ]);
    }
}

function syncBalance($conn, $smm_manager) {
    try {
        $apis = $smm_manager->getAllAPIs();
        $total_balance = 0;
        
        foreach ($apis as $api_name => $api) {
            $result = $api->getBalance();
            
            if ($result['success']) {
                $total_balance += $result['balance'];
            }
        }
        
        // Balansı admin panelində göstərmək üçün log yarat
        $stmt = $conn->prepare("
            INSERT INTO admin_logs (action, details, created_at) 
            VALUES ('balance_sync', ?, NOW())
        ");
        $stmt->execute([json_encode(['total_balance' => $total_balance])]);
        
        echo json_encode([
            'success' => true,
            'total_balance' => $total_balance,
            'message' => 'Balans sinxronizasiya edildi'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Balans sinxronizasiya xətası: ' . $e->getMessage()
        ]);
    }
}

function mapSMMStatus($smm_status) {
    $status_map = [
        'Pending' => 'pending',
        'In Progress' => 'in_progress',
        'Completed' => 'completed',
        'Partial' => 'in_progress',
        'Processing' => 'in_progress',
        'Canceled' => 'cancelled',
        'Refunded' => 'refunded'
    ];
    
    return $status_map[$smm_status] ?? 'pending';
}

// Cron job üçün avtomatik sinxronizasiya
if (php_sapi_name() === 'cli') {
    echo "SMM API Sinxronizasiya başladı...\n";
    
    // Xidmətləri sinxronizasiya et
    syncServices($conn, $smm_manager);
    
    // Sifariş statuslarını yenilə
    updateOrderStatus($conn, $smm_manager);
    
    // Balansı sinxronizasiya et
    syncBalance($conn, $smm_manager);
    
    echo "Sinxronizasiya tamamlandı.\n";
}
?>