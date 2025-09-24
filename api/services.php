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
        case 'categories':
            getCategories($conn);
            break;
        case 'services':
            getServices($conn);
            break;
        case 'service':
            getService($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Etibarsız əməliyyat']);
    }
}

function getCategories($conn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Xəta: ' . $e->getMessage()
        ]);
    }
}

function getServices($conn) {
    $category_id = $_GET['category_id'] ?? null;
    
    try {
        if ($category_id) {
            $stmt = $conn->prepare("
                SELECT s.*, c.name as category_name 
                FROM services s 
                JOIN categories c ON s.category_id = c.id 
                WHERE s.status = 'active' AND s.category_id = ? 
                ORDER BY s.name
            ");
            $stmt->execute([$category_id]);
        } else {
            $stmt = $conn->prepare("
                SELECT s.*, c.name as category_name 
                FROM services s 
                JOIN categories c ON s.category_id = c.id 
                WHERE s.status = 'active' 
                ORDER BY c.name, s.name
            ");
            $stmt->execute();
        }
        
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'services' => $services
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Xəta: ' . $e->getMessage()
        ]);
    }
}

function getService($conn) {
    $service_id = $_GET['service_id'] ?? null;
    
    if (!$service_id) {
        echo json_encode(['success' => false, 'message' => 'Xidmət ID tələb olunur']);
        return;
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT s.*, c.name as category_name 
            FROM services s 
            JOIN categories c ON s.category_id = c.id 
            WHERE s.id = ? AND s.status = 'active'
        ");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($service) {
            echo json_encode([
                'success' => true,
                'service' => $service
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Xidmət tapılmadı'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Xəta: ' . $e->getMessage()
        ]);
    }
}
?>