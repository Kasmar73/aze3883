<?php
// SMM API konfiqurasiyası və inteqrasiyası
class SMMAPI {
    private $api_url;
    private $api_key;
    private $api_name;
    
    public function __construct($api_name, $api_url, $api_key) {
        $this->api_name = $api_name;
        $this->api_url = $api_url;
        $this->api_key = $api_key;
    }
    
    // Sifariş yaratmaq
    public function createOrder($service_id, $link, $quantity) {
        $data = [
            'key' => $this->api_key,
            'action' => 'add',
            'service' => $service_id,
            'link' => $link,
            'quantity' => $quantity
        ];
        
        $response = $this->makeRequest('POST', $data);
        
        if ($response && isset($response['order'])) {
            return [
                'success' => true,
                'order_id' => $response['order'],
                'charge' => $response['charge'] ?? 0,
                'start_count' => $response['start_count'] ?? 0,
                'status' => $response['status'] ?? 'pending'
            ];
        } else {
            return [
                'success' => false,
                'message' => $response['error'] ?? 'SMM API xətası'
            ];
        }
    }
    
    // Sifariş statusunu yoxlamaq
    public function getOrderStatus($order_id) {
        $data = [
            'key' => $this->api_key,
            'action' => 'status',
            'order' => $order_id
        ];
        
        $response = $this->makeRequest('POST', $data);
        
        if ($response && isset($response['status'])) {
            return [
                'success' => true,
                'status' => $response['status'],
                'charge' => $response['charge'] ?? 0,
                'start_count' => $response['start_count'] ?? 0,
                'remains' => $response['remains'] ?? 0
            ];
        } else {
            return [
                'success' => false,
                'message' => $response['error'] ?? 'Status yoxlanıla bilmədi'
            ];
        }
    }
    
    // Balansı yoxlamaq
    public function getBalance() {
        $data = [
            'key' => $this->api_key,
            'action' => 'balance'
        ];
        
        $response = $this->makeRequest('POST', $data);
        
        if ($response && isset($response['balance'])) {
            return [
                'success' => true,
                'balance' => $response['balance']
            ];
        } else {
            return [
                'success' => false,
                'message' => $response['error'] ?? 'Balans yoxlanıla bilmədi'
            ];
        }
    }
    
    // Xidmətləri almaq
    public function getServices() {
        $data = [
            'key' => $this->api_key,
            'action' => 'services'
        ];
        
        $response = $this->makeRequest('POST', $data);
        
        if ($response && is_array($response)) {
            return [
                'success' => true,
                'services' => $response
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Xidmətlər alına bilmədi'
            ];
        }
    }
    
    // Sifarişi ləğv etmək
    public function cancelOrder($order_id) {
        $data = [
            'key' => $this->api_key,
            'action' => 'cancel',
            'order' => $order_id
        ];
        
        $response = $this->makeRequest('POST', $data);
        
        if ($response && isset($response['status'])) {
            return [
                'success' => true,
                'message' => 'Sifariş ləğv edildi'
            ];
        } else {
            return [
                'success' => false,
                'message' => $response['error'] ?? 'Sifariş ləğv edilə bilmədi'
            ];
        }
    }
    
    private function makeRequest($method, $data) {
        $ch = curl_init();
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_URL, $this->api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            curl_setopt($ch, CURLOPT_URL, $this->api_url . '?' . http_build_query($data));
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SMM Panel Bot/1.0');
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'cURL xətası: ' . $error
            ];
        }
        
        if ($http_code !== 200) {
            return [
                'success' => false,
                'message' => 'HTTP xətası: ' . $http_code
            ];
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'JSON parse xətası: ' . json_last_error_msg()
            ];
        }
        
        return $decoded;
    }
}

// SMM API konfiqurasiyası
class SMMAPIManager {
    private $apis = [];
    private $database;
    
    public function __construct($database) {
        $this->database = $database;
        $this->loadAPIs();
    }
    
    private function loadAPIs() {
        $conn = $this->database->getConnection();
        $stmt = $conn->prepare("SELECT * FROM smm_api_config WHERE status = 'active'");
        $stmt->execute();
        $apis = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($apis as $api) {
            $this->apis[$api['api_name']] = new SMMAPI(
                $api['api_name'],
                $api['api_url'],
                $api['api_key']
            );
        }
    }
    
    public function getAPI($api_name) {
        return $this->apis[$api_name] ?? null;
    }
    
    public function getAllAPIs() {
        return $this->apis;
    }
    
    // Ən yaxşı API-ni seçmək (balans əsasında)
    public function getBestAPI() {
        $best_api = null;
        $highest_balance = 0;
        
        foreach ($this->apis as $api_name => $api) {
            $balance_result = $api->getBalance();
            if ($balance_result['success'] && $balance_result['balance'] > $highest_balance) {
                $highest_balance = $balance_result['balance'];
                $best_api = $api;
            }
        }
        
        return $best_api;
    }
    
    // Sifarişi bütün API-lərə göndərmək
    public function createOrder($service_id, $link, $quantity) {
        $best_api = $this->getBestAPI();
        
        if (!$best_api) {
            return [
                'success' => false,
                'message' => 'Aktiv SMM API tapılmadı'
            ];
        }
        
        return $best_api->createOrder($service_id, $link, $quantity);
    }
    
    // Sifariş statusunu yoxlamaq
    public function getOrderStatus($smm_order_id) {
        foreach ($this->apis as $api) {
            $result = $api->getOrderStatus($smm_order_id);
            if ($result['success']) {
                return $result;
            }
        }
        
        return [
            'success' => false,
            'message' => 'Sifariş tapılmadı'
        ];
    }
}

// Nümunə SMM API konfiqurasiyası
function setupDefaultSMMAPIs($conn) {
    $default_apis = [
        [
            'api_name' => 'smm_api_1',
            'api_url' => 'https://example-smm-api.com/api/v2',
            'api_key' => 'YOUR_API_KEY_1',
            'status' => 'active'
        ],
        [
            'api_name' => 'smm_api_2', 
            'api_url' => 'https://another-smm-api.com/api',
            'api_key' => 'YOUR_API_KEY_2',
            'status' => 'active'
        ]
    ];
    
    foreach ($default_apis as $api) {
        $stmt = $conn->prepare("
            INSERT IGNORE INTO smm_api_config (api_name, api_url, api_key, status) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $api['api_name'],
            $api['api_url'], 
            $api['api_key'],
            $api['status']
        ]);
    }
}
?>