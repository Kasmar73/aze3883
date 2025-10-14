# SMM Panel - Telegram Bot Webapp

Bu layihə Telegram bot üçün SMM (Social Media Marketing) panel webapp-ıdır. PHP, MySQL və Telegram Bot API istifadə edərək hazırlanmışdır.

## Xüsusiyyətlər

- 🤖 Telegram Bot inteqrasiyası
- 📱 Responsive webapp dizaynı
- 💰 Balans idarəetməsi
- 🛒 Sifariş sistemi
- 📊 Dashboard və statistikalar
- 🔗 SMM API inteqrasiyası
- 🔐 Təhlükəsizlik tədbirləri

## Quraşdırma

### 1. Tələblər

- PHP 7.4 və ya daha yeni
- MySQL 5.7 və ya daha yeni
- Apache/Nginx web server
- SSL sertifikatı (Telegram WebApp üçün)

### 2. Veritabanı Quraşdırması

1. MySQL-də yeni veritabanı yaradın:
```sql
CREATE DATABASE smm_panel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. `config/database.php` faylında veritabanı məlumatlarını yeniləyin:
```php
private $host = 'localhost';
private $db_name = 'smm_panel';
private $username = 'your_username';
private $password = 'your_password';
```

### 3. Telegram Bot Quraşdırması

1. [@BotFather](https://t.me/botfather) ilə yeni bot yaradın
2. Bot tokeninizi alın
3. `config/telegram.php` faylında tokeni yeniləyin:
```php
$BOT_TOKEN = "YOUR_BOT_TOKEN_HERE";
$WEBHOOK_URL = "https://yourdomain.com/webhook.php";
$WEBAPP_URL = "https://yourdomain.com/index.php";
```

### 4. SMM API Konfiqurasiyası

1. `config/smm_api.php` faylında SMM API məlumatlarını təyin edin
2. Veritabanında SMM API konfiqurasiyasını əlavə edin

### 5. Avtomatik Quraşdırma

```bash
php bot_setup.php
```

Bu skript:
- Veritabanı cədvəllərini yaradır
- Əsas məlumatları doldurur
- Webhook-u quraşdırır

## İstifadə

### Bot Əmrləri

- `/start` - Botu başlat və webapp-ı aç
- `/help` - Yardım məlumatları
- `/balance` - Balansı yoxla
- `/orders` - Sifarişləri gör
- `/panel` - SMM panelini aç

### Webapp Xüsusiyyətləri

1. **Dashboard** - Ümumi statistikalar
2. **Xidmətlər** - Mövcud SMM xidmətləri
3. **Sifarişlər** - Sifariş tarixçəsi
4. **Balans** - Balans idarəetməsi
5. **Profil** - İstifadəçi məlumatları

## API Endpoint-ləri

### Authentication
- `POST /api/auth.php` - Telegram ilə giriş

### Services
- `GET /api/services.php?action=categories` - Kateqoriyaları al
- `GET /api/services.php?action=services&category_id=X` - Xidmətləri al

### Orders
- `GET /api/orders.php` - Sifarişləri al
- `POST /api/orders.php` - Yeni sifariş yarat

### User
- `GET /api/user.php?action=profile` - Profil məlumatları
- `GET /api/user.php?action=balance` - Balans məlumatları
- `GET /api/user.php?action=stats` - Statistikalar

## Cron Jobs

Avtomatik sinxronizasiya üçün cron job əlavə edin:

```bash
# Hər 5 dəqiqədə sifariş statuslarını yenilə
*/5 * * * * php /path/to/your/project/api/smm_sync.php

# Hər gün xidmətləri sinxronizasiya et
0 2 * * * php /path/to/your/project/api/smm_sync.php
```

## Təhlükəsizlik

- Tüm API endpoint-ləri authentication tələb edir
- SQL injection qorunması (PDO prepared statements)
- XSS qorunması
- CSRF qorunması
- Rate limiting (tövsiyə olunur)

## Dəstəklənən Platformalar

- Instagram (followers, likes, views)
- Facebook (likes, followers)
- YouTube (views, subscribers)
- TikTok (followers, likes)
- Twitter (followers, retweets)

## Problemlərin Həlli

### Webhook Problemləri
```bash
# Webhook statusunu yoxla
curl "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getWebhookInfo"
```

### Veritabanı Bağlantısı
`config/database.php` faylında məlumatları yoxlayın.

### SSL Problemləri
Telegram WebApp HTTPS tələb edir. SSL sertifikatı quraşdırın.

## Dəstək

Suallarınız üçün:
- GitHub Issues
- Telegram: @your_support_username

## Lisenziya

Bu layihə MIT lisenziyası altında paylaşılır.

## Yeniləmələr

### v1.0.0
- İlkin versiya
- Telegram Bot inteqrasiyası
- Webapp interfeysi
- SMM API inteqrasiyası
- Balans sistemi