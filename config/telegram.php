<?php
// Telegram Bot konfiqurasiyası
class TelegramBot {
    private $bot_token;
    private $webhook_url;
    
    public function __construct($bot_token, $webhook_url) {
        $this->bot_token = $bot_token;
        $this->webhook_url = $webhook_url;
    }
    
    public function sendMessage($chat_id, $text, $reply_markup = null) {
        $url = "https://api.telegram.org/bot{$this->bot_token}/sendMessage";
        
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'HTML'
        ];
        
        if ($reply_markup) {
            $data['reply_markup'] = json_encode($reply_markup);
        }
        
        return $this->makeRequest($url, $data);
    }
    
    public function sendWebApp($chat_id, $text, $web_app_url) {
        $url = "https://api.telegram.org/bot{$this->bot_token}/sendMessage";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => 'SMM Panel Aç',
                        'web_app' => ['url' => $web_app_url]
                    ]
                ]
            ]
        ];
        
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => json_encode($keyboard)
        ];
        
        return $this->makeRequest($url, $data);
    }
    
    public function setWebhook() {
        $url = "https://api.telegram.org/bot{$this->bot_token}/setWebhook";
        $data = ['url' => $this->webhook_url];
        
        return $this->makeRequest($url, $data);
    }
    
    public function getWebhookInfo() {
        $url = "https://api.telegram.org/bot{$this->bot_token}/getWebhookInfo";
        return $this->makeRequest($url, []);
    }
    
    private function makeRequest($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $result = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($result, true);
    }
}

// Bot konfiqurasiyası
$BOT_TOKEN = "YOUR_BOT_TOKEN_HERE"; // Buraya bot tokeninizi yazın
$WEBHOOK_URL = "https://yourdomain.com/webhook.php"; // Buraya webhook URL-inizi yazın
$WEBAPP_URL = "https://yourdomain.com/index.php"; // Buraya webapp URL-inizi yazın

$telegram = new TelegramBot($BOT_TOKEN, $WEBHOOK_URL);
?>