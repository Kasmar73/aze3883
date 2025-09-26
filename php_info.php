<?php
// PHP məlumatları
echo "<h2>PHP Məlumatları</h2>";

echo "<p><strong>PHP Versiyası:</strong> " . phpversion() . "</p>";

if (version_compare(phpversion(), '7.4.0', '>=')) {
    echo "<p style='color: green;'>✅ PHP versiyası uyğundur (7.4+)</p>";
} else {
    echo "<p style='color: red;'>❌ PHP versiyası uyğun deyil (7.4+ tələb olunur)</p>";
}

echo "<h3>PHP Extension-ları</h3>";

$required_extensions = ['pdo', 'pdo_mysql', 'curl', 'json', 'mbstring'];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✅ {$ext} extension yüklüdür</p>";
    } else {
        echo "<p style='color: red;'>❌ {$ext} extension yüklü deyil</p>";
    }
}

echo "<h3>Server Məlumatları</h3>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Naməlum') . "</p>";
echo "<p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Naməlum') . "</p>";
echo "<p><strong>HTTP Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'Naməlum') . "</p>";

echo "<h3>PHP Konfiqurasiyası</h3>";
echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . "</p>";
echo "<p><strong>Upload Max Filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>Post Max Size:</strong> " . ini_get('post_max_size') . "</p>";

echo "<h3>Error Reporting</h3>";
echo "<p><strong>Error Reporting:</strong> " . error_reporting() . "</p>";
echo "<p><strong>Display Errors:</strong> " . (ini_get('display_errors') ? 'Aktiv' : 'Deaktiv') . "</p>";

echo "<h3>cURL Məlumatları</h3>";
if (extension_loaded('curl')) {
    $curl_version = curl_version();
    echo "<p><strong>cURL Versiyası:</strong> " . $curl_version['version'] . "</p>";
    echo "<p><strong>SSL Versiyası:</strong> " . $curl_version['ssl_version'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ cURL extension yüklü deyil</p>";
}

echo "<h3>MySQL Məlumatları</h3>";
if (extension_loaded('pdo_mysql')) {
    echo "<p style='color: green;'>✅ PDO MySQL extension yüklüdür</p>";
} else {
    echo "<p style='color: red;'>❌ PDO MySQL extension yüklü deyil</p>";
}

echo "<h3>JSON Məlumatları</h3>";
echo "<p><strong>JSON Support:</strong> " . (function_exists('json_encode') ? 'Aktiv' : 'Deaktiv') . "</p>";
echo "<p><strong>JSON Last Error:</strong> " . json_last_error_msg() . "</p>";

echo "<h3>File Permissions</h3>";
$writable_dirs = ['logs', 'config', 'api', 'assets'];

foreach ($writable_dirs as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "<p style='color: green;'>✅ {$dir}/ qovluğu yazılabilir</p>";
        } else {
            echo "<p style='color: red;'>❌ {$dir}/ qovluğu yazıla bilmir</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ {$dir}/ qovluğu mövcud deyil</p>";
    }
}

echo "<h3>Test Nəticəsi</h3>";

$php_ok = version_compare(phpversion(), '7.4.0', '>=');
$extensions_ok = true;

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $extensions_ok = false;
        break;
    }
}

if ($php_ok && $extensions_ok) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅ Bütün tələblər ödənilir!</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ Bəzi tələblər ödənilmir!</p>";
}
?>