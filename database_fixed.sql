-- SMM Panel Veritabanı Strukturu
-- Mövcud veritabanına import edin

-- Veritabanı yaratmaq əvəzinə mövcud veritabanını istifadə edin
-- USE your_existing_database_name;

-- İstifadəçilər cədvəli
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

-- Kateqoriyalar cədvəli
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Xidmətlər cədvəli
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
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Sifarişlər cədvəli
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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Tranzaksiyalar cədvəli
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('deposit', 'withdrawal', 'order_payment') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description TEXT,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- SMM API konfiqurasiyası cədvəli
CREATE TABLE IF NOT EXISTS smm_api_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_name VARCHAR(100) NOT NULL,
    api_url VARCHAR(500) NOT NULL,
    api_key VARCHAR(500) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admin logları cədvəli
CREATE TABLE IF NOT EXISTS admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Arxiv sifarişlər cədvəli
CREATE TABLE IF NOT EXISTS orders_archive (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    link VARCHAR(500) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,4) NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    smm_order_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Əsas kateqoriyaları əlavə et
INSERT INTO categories (name, description, icon) VALUES
('Instagram', 'Instagram xidmətləri', 'fab fa-instagram'),
('Facebook', 'Facebook xidmətləri', 'fab fa-facebook'),
('YouTube', 'YouTube xidmətləri', 'fab fa-youtube'),
('TikTok', 'TikTok xidmətləri', 'fab fa-tiktok'),
('Twitter', 'Twitter xidmətləri', 'fab fa-twitter');

-- Nümunə xidmətləri əlavə et
INSERT INTO services (category_id, name, description, price, min_quantity, max_quantity, service_type, platform) VALUES
-- Instagram xidmətləri
(1, 'Instagram Followers', 'Real Instagram followers - High Quality', 0.50, 100, 10000, 'followers', 'Instagram'),
(1, 'Instagram Likes', 'Instagram post likes - Fast Delivery', 0.30, 50, 5000, 'likes', 'Instagram'),
(1, 'Instagram Views', 'Instagram video views - Real Users', 0.20, 100, 10000, 'views', 'Instagram'),
(1, 'Instagram Comments', 'Instagram post comments - Custom Text', 0.40, 10, 1000, 'comments', 'Instagram'),
(1, 'Instagram Shares', 'Instagram post shares - High Quality', 0.35, 20, 2000, 'shares', 'Instagram'),

-- Facebook xidmətləri
(2, 'Facebook Page Likes', 'Facebook page likes - Real Users', 0.40, 100, 5000, 'likes', 'Facebook'),
(2, 'Facebook Post Likes', 'Facebook post likes - Fast Delivery', 0.25, 50, 3000, 'likes', 'Facebook'),
(2, 'Facebook Followers', 'Facebook page followers - High Quality', 0.60, 100, 10000, 'followers', 'Facebook'),
(2, 'Facebook Shares', 'Facebook post shares - Real Users', 0.30, 20, 1500, 'shares', 'Facebook'),

-- YouTube xidmətləri
(3, 'YouTube Views', 'YouTube video views - High Retention', 0.10, 1000, 100000, 'views', 'YouTube'),
(3, 'YouTube Subscribers', 'YouTube channel subscribers - Real Users', 0.80, 100, 50000, 'followers', 'YouTube'),
(3, 'YouTube Likes', 'YouTube video likes - Fast Delivery', 0.15, 100, 10000, 'likes', 'YouTube'),
(3, 'YouTube Comments', 'YouTube video comments - Custom Text', 0.50, 10, 1000, 'comments', 'YouTube'),

-- TikTok xidmətləri
(4, 'TikTok Followers', 'TikTok followers - High Quality', 0.60, 100, 10000, 'followers', 'TikTok'),
(4, 'TikTok Likes', 'TikTok video likes - Real Users', 0.40, 100, 50000, 'likes', 'TikTok'),
(4, 'TikTok Views', 'TikTok video views - Fast Delivery', 0.20, 1000, 100000, 'views', 'TikTok'),
(4, 'TikTok Shares', 'TikTok video shares - High Quality', 0.30, 50, 10000, 'shares', 'TikTok'),

-- Twitter xidmətləri
(5, 'Twitter Followers', 'Twitter followers - Real Users', 0.70, 100, 50000, 'followers', 'Twitter'),
(5, 'Twitter Likes', 'Twitter post likes - Fast Delivery', 0.35, 50, 10000, 'likes', 'Twitter'),
(5, 'Twitter Retweets', 'Twitter retweets - High Quality', 0.45, 20, 5000, 'shares', 'Twitter'),
(5, 'Twitter Views', 'Twitter video views - Real Users', 0.25, 100, 50000, 'views', 'Twitter');

-- Nümunə SMM API konfiqurasiyası
INSERT INTO smm_api_config (api_name, api_url, api_key, status) VALUES
('smm_api_1', 'https://example-smm-api.com/api/v2', 'YOUR_API_KEY_1', 'inactive'),
('smm_api_2', 'https://another-smm-api.com/api', 'YOUR_API_KEY_2', 'inactive');

-- İndekslər əlavə et (performans üçün)
CREATE INDEX idx_users_telegram_id ON users(telegram_id);
CREATE INDEX idx_orders_user_id ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_orders_created_at ON orders(created_at);
CREATE INDEX idx_services_category_id ON services(category_id);
CREATE INDEX idx_services_status ON services(status);
CREATE INDEX idx_transactions_user_id ON transactions(user_id);
CREATE INDEX idx_transactions_type ON transactions(type);

-- Nümunə istifadəçi əlavə et (test üçün)
INSERT INTO users (telegram_id, username, first_name, last_name, balance) VALUES
(123456789, 'test_user', 'Test', 'User', 100.00);

-- Veritabanı yaradıldı mesajı
SELECT 'SMM Panel cədvəlləri uğurla yaradıldı!' as message;