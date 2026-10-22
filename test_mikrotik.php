<?php
require_once 'vendor/autoload.php';
require_once 'config/payment.php';

use RouterOS\Client;
use RouterOS\Query;

header('Content-Type: text/html; charset=utf-8');
echo "<pre style='font-family:monospace;background:#1e1e1e;color:#d4d4d4;padding:20px;line-height:1.7;border-radius:10px'>";

echo "═══════════════════════════════════════════════\n";
echo "  TEST KONEKSI MIKROTIK\n";
echo "═══════════════════════════════════════════════\n\n";

echo "📋 Konfigurasi:\n";
echo "   Host : " . MIKROTIK_HOST . "\n";
echo "   User : " . MIKROTIK_USER . "\n";
echo "   Port : " . MIKROTIK_PORT . "\n\n";

echo "🔌 Mencoba connect...\n\n";

try {
    $client = new Client([
        'host' => MIKROTIK_HOST,
        'user' => MIKROTIK_USER,
        'pass' => MIKROTIK_PASS,
        'port' => MIKROTIK_PORT,
    ]);
    echo "✅ Koneksi BERHASIL!\n\n";

    // Ambil info router
    $query = new Query('/system/resource/print');
    $response = $client->query($query)->read();
    if (!empty($response[0])) {
        echo "📊 Info Router:\n";
        echo "   Versi OS  : " . ($response[0]['version'] ?? '-') . "\n";
        echo "   Board     : " . ($response[0]['board-name'] ?? '-') . "\n";
        echo "   Uptime    : " . ($response[0]['uptime'] ?? '-') . "\n";
    }

    // Cek profile yang tersedia
    echo "\n📁 Daftar Hotspot Profile:\n";
    $profileQuery = new Query('/ip/hotspot/user/profile/print');
    $profiles = $client->query($profileQuery)->read();
    if (empty($profiles)) {
        echo "   (Belum ada profile — buat dulu di WinBox)\n";
    } else {
        foreach ($profiles as $p) {
            echo "   - " . ($p['name'] ?? '?') . "\n";
        }
    }

    echo "\n✅ Semua OK! Lanjut test buat user.\n";

} catch (Exception $e) {
    echo "❌ GAGAL: " . $e->getMessage() . "\n\n";
    echo "💡 Cek hal-hal ini:\n";
    echo "   1. Apakah service API aktif? (IP → Services → api)\n";
    echo "   2. Apakah IP router benar? (" . MIKROTIK_HOST . ")\n";
    echo "   3. Apakah user & password benar?\n";
    echo "   4. Komputer bisa ping router? coba: ping " . MIKROTIK_HOST . "\n";
    echo "   5. Firewall router blokir port 8728?\n";
}
echo "</pre>";