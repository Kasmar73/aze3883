<?php
// Cron job skripti - SMM API sinxronizasiyası
require_once 'config/database.php';
require_once 'config/smm_api.php';

// Log faylı
$log_file = 'logs/cron_' . date('Y-m-d') . '.log';

function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[{$timestamp}] {$message}\n", FILE_APPEND | LOCK_EX);
}

writeLog("Cron job başladı");

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        writeLog("ERROR: Veritabanı bağlantısı uğursuz");
        exit(1);
    }
    
    $smm_manager = new SMMAPIManager($database);
    
    // 1. Sifariş statuslarını yenilə
    writeLog("Sifariş statusları yenilənir...");
    
    $stmt = $conn->prepare("
        SELECT id, smm_order_id, status 
        FROM orders 
        WHERE status IN ('pending', 'in_progress') 
        AND smm_order_id IS NOT NULL
        AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
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
                writeLog("Sifariş #{$order['id']} statusu yeniləndi: {$order['status']} -> {$new_status}");
            }
        }
    }
    
    writeLog("{$updated_count} sifariş statusu yeniləndi");
    
    // 2. Xidmətləri sinxronizasiya et (hər gün bir dəfə)
    $last_sync = file_get_contents('logs/last_service_sync.txt') ?? '';
    $today = date('Y-m-d');
    
    if ($last_sync !== $today) {
        writeLog("Xidmətlər sinxronizasiya edilir...");
        
        $apis = $smm_manager->getAllAPIs();
        $synced_count = 0;
        
        foreach ($apis as $api_name => $api) {
            $result = $api->getServices();
            
            if ($result['success']) {
                foreach ($result['services'] as $service) {
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
        
        file_put_contents('logs/last_service_sync.txt', $today);
        writeLog("{$synced_count} xidmət sinxronizasiya edildi");
    }
    
    // 3. Köhnə log fayllarını təmizlə (30 gündən köhnə)
    $log_dir = 'logs/';
    $files = glob($log_dir . 'cron_*.log');
    $cutoff_date = date('Y-m-d', strtotime('-30 days'));
    
    foreach ($files as $file) {
        $file_date = basename($file, '.log');
        $file_date = str_replace('cron_', '', $file_date);
        
        if ($file_date < $cutoff_date) {
            unlink($file);
            writeLog("Köhnə log faylı silindi: {$file}");
        }
    }
    
    // 4. Köhnə sifarişləri arxivlə (90 gündən köhnə)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM orders 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
        AND status IN ('completed', 'cancelled', 'refunded')
    ");
    $stmt->execute();
    $old_orders = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($old_orders > 0) {
        // Arxiv cədvəli yarat (əgər yoxdursa)
        $conn->exec("
            CREATE TABLE IF NOT EXISTS orders_archive LIKE orders
        ");
        
        // Köhnə sifarişləri arxivlə
        $stmt = $conn->prepare("
            INSERT INTO orders_archive 
            SELECT * FROM orders 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
            AND status IN ('completed', 'cancelled', 'refunded')
        ");
        $stmt->execute();
        
        // Köhnə sifarişləri sil
        $stmt = $conn->prepare("
            DELETE FROM orders 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
            AND status IN ('completed', 'cancelled', 'refunded')
        ");
        $stmt->execute();
        
        writeLog("{$old_orders} köhnə sifariş arxivləndi");
    }
    
    writeLog("Cron job tamamlandı");
    
} catch (Exception $e) {
    writeLog("ERROR: " . $e->getMessage());
    exit(1);
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
?>