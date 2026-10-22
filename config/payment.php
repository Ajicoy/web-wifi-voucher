<?php
// config/payment.php
// ============================================
// KONFIGURASI iPAYMU SANDBOX
// ============================================

// VA Number & API Key dari dashboard sandbox iPaymu
define('IPAYMU_VA',       '0000005722155073');
define('IPAYMU_API_KEY',  'SANDBOX679B35C3-2F2A-4B6C-8AB6-5D107E47D07E');

// Endpoint sandbox
define('IPAYMU_BASE_URL', 'https://sandbox.ipaymu.com');
define('IPAYMU_API_URL',  IPAYMU_BASE_URL . '/api/v2/payment/direct');

// ============================================
// URL NGROK - WAJIB ADA /wifi-voucher DI BELAKANG
// ============================================
define('NGROK_URL', 'https://28a5-103-175-224-158.ngrok-free.app/wifi-voucher');

// ============================================
// FUNGSI DURASI VOUCHER
// ============================================
function getVoucherDuration($amount) {
    $packages = [
        2000  => 60,
        5000  => 180,
        10000 => 1440,
        20000 => 4320,
        50000 => 10080,
    ];
    return $packages[$amount] ?? 60;
}

// ============================================
// FUNGSI GENERATE SIGNATURE iPAYMU
// ============================================
function generateSignature($method, $va, $bodyJson, $apiKey) {
    $bodyHash = hash('sha256', $bodyJson);
    $stringToSign = strtoupper($method) . ':' . $va . ':' . $bodyHash . ':' . $apiKey;
    return hash_hmac('sha256', $stringToSign, $apiKey);
}
// ============================================
// KONFIGURASI MIKROTIK
// ============================================
// ⚠️ GANTI dengan data router Anda
define('MIKROTIK_HOST', '192.168.56.0');      // IP router MikroTik
define('MIKROTIK_USER', 'admin-voucher');      // User API yang dibuat
define('MIKROTIK_PASS', 'anjay123');   // Password user API
define('MIKROTIK_PORT', 8728);

// Mapping durasi ke nama profile MikroTik
function getMikrotikProfile($duration_minutes) {
    $map = [
        60    => '1jam',
        180   => '3jam',
        1440  => '1hari',
        4320  => '3hari',
        10080 => '7hari',
    ];
    return $map[$duration_minutes] ?? 'default';
}