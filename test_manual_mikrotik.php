<?php
require_once 'mikrotik_helper.php';

header('Content-Type: text/plain');

echo "=== TEST MANUAL CREATE USER MIKROTIK ===\n\n";

$test_code = 'TEST' . rand(1000, 9999);

echo "Username : $test_code\n";
echo "Password : $test_code\n";
echo "Profile  : " . getMikrotikProfile(60) . "\n";
echo "Duration : 60 menit\n\n";

echo "Mengirim request ke MikroTik...\n\n";

$result = createMikrotikUser(
    $test_code,
    $test_code,
    60,
    'Manual Test'
);

if ($result) {
    echo "✅ BERHASIL!\n\n";
    echo "=== LANGKAH SELANJUTNYA ===\n";
    echo "1. Buka WinBox\n";
    echo "2. IP → Hotspot → Users\n";
    echo "3. Cari user: $test_code\n";
    echo "4. Kalau ada = integrasi JALAN!\n";
} else {
    echo "❌ GAGAL!\n\n";
    echo "Buka callback_log.txt baris terakhir untuk detail error.\n";
}