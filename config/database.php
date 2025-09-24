<?php
// Veritabanı konfiqurasiyası
class Database {
    private $host = 'localhost';
    private $db_name = 'smm_panel';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Bağlantı xətası: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}

// Veritabanı cədvəllərini yaratmaq üçün SQL
function createTables($conn) {
    $sql = "
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
    ";

    try {
        $conn->exec($sql);
        return true;
    } catch(PDOException $e) {
        echo "Cədvəl yaratma xətası: " . $e->getMessage();
        return false;
    }
}

// Əsas məlumatları doldurmaq
function insertDefaultData($conn) {
    // Kateqoriyalar
    $categories = [
        ['Instagram', 'Instagram xidmətləri', 'fab fa-instagram'],
        ['Facebook', 'Facebook xidmətləri', 'fab fa-facebook'],
        ['YouTube', 'YouTube xidmətləri', 'fab fa-youtube'],
        ['TikTok', 'TikTok xidmətləri', 'fab fa-tiktok'],
        ['Twitter', 'Twitter xidmətləri', 'fab fa-twitter']
    ];

    foreach ($categories as $category) {
        $stmt = $conn->prepare("INSERT IGNORE INTO categories (name, description, icon) VALUES (?, ?, ?)");
        $stmt->execute($category);
    }

    // Nümunə xidmətlər
    $services = [
        [1, 'Instagram Followers', 'Real Instagram followers', 0.50, 100, 10000, 'followers', 'Instagram'],
        [1, 'Instagram Likes', 'Instagram post likes', 0.30, 50, 5000, 'likes', 'Instagram'],
        [1, 'Instagram Views', 'Instagram video views', 0.20, 100, 10000, 'views', 'Instagram'],
        [2, 'Facebook Likes', 'Facebook page likes', 0.40, 100, 5000, 'likes', 'Facebook'],
        [3, 'YouTube Views', 'YouTube video views', 0.10, 1000, 100000, 'views', 'YouTube'],
        [4, 'TikTok Followers', 'TikTok followers', 0.60, 100, 10000, 'followers', 'TikTok']
    ];

    foreach ($services as $service) {
        $stmt = $conn->prepare("INSERT IGNORE INTO services (category_id, name, description, price, min_quantity, max_quantity, service_type, platform) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute($service);
    }
}
?>