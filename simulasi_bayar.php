<?php
/**
 * simulasi_bayar.php
 * Buka: http://localhost/wifi-voucher/simulasi_bayar.php
 * 
 * File ini mensimulasikan callback iPaymu + integrasi MikroTik
 * untuk testing tanpa perlu ngrok/iPaymu.
 * 
 * ⚠️ HAPUS di production!
 */

require_once 'config/payment.php';
require_once 'db.php';

$ref = $_GET['ref'] ?? '';

// ============================================
// TAMPILKAN DAFTAR ORDER PENDING
// ============================================
if (!$ref) {
    $pending = $pdo->query("SELECT order_id, amount, created_at FROM vouchers WHERE status='pending' ORDER BY created_at DESC LIMIT 10")->fetchAll();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Simulasi Bayar</title>
        <style>
            body { font-family:Arial; padding:30px; background:#f0f2f5; }
            h1 { color:#2c3e50; }
            .card { background:#fff; padding:20px; border-radius:12px; margin:10px 0; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
            .btn { display:inline-block; padding:10px 20px; background:#27ae60; color:#fff; border-radius:8px; text-decoration:none; font-weight:bold; margin-top:10px; }
            .btn:hover { background:#219150; }
            .empty { background:#fff3cd; color:#856404; padding:20px; border-radius:12px; }
            .order-id { font-family:monospace; font-weight:bold; color:#3498db; }
        </style>
    </head>
    <body>
        <h1>🎯 Simulasi Pembayaran</h1>
        <p>Klik salah satu order di bawah untuk simulasi pembayaran sukses.<br>
        Order ini akan otomatis generate voucher <b>dan</b> buat user di MikroTik.</p>
        <?php if (empty($pending)): ?>
            <div class="empty">
                <b>⚠️ Tidak ada order pending.</b><br>
                Silakan buat order baru di <a href="index.php">halaman utama</a> dulu.
            </div>
        <?php else: ?>
            <?php foreach ($pending as $p): ?>
                <div class="card">
                    <b>Order ID:</b> <span class="order-id"><?= htmlspecialchars($p['order_id']) ?></span><br>
                    <b>Amount:</b> Rp <?= number_format($p['amount'],0,',','.') ?><br>
                    <b>Dibuat:</b> <?= $p['created_at'] ?><br>
                    <a href="?ref=<?= urlencode($p['order_id']) ?>" class="btn">✅ Simulasi Bayar Sukses</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </body>
    </html>
    <?php
    exit;
}

// ============================================
// PROSES SIMULASI BAYAR
// ============================================
$stmt = $pdo->prepare("SELECT * FROM vouchers WHERE order_id = ?");
$stmt->execute([$ref]);
$row = $stmt->fetch();

if (!$row) {
    die("❌ Order tidak ditemukan: $ref");
}

if ($row['voucher_code']) {
    ?>
    <!DOCTYPE html>
    <html>
    <body style="font-family:Arial;padding:30px;background:#f0f2f5">
        <div style="background:#fff;padding:20px;border-radius:12px;max-width:500px;margin:0 auto">
            <h2>⚠️ Voucher sudah ada</h2>
            <p>Order <b><?= htmlspecialchars($ref) ?></b> sudah diproses.</p>
            <p>Kode: <b style="font-size:24px;letter-spacing:4px"><?= $row['voucher_code'] ?></b></p>
            <p>Status: <b><?= $row['status'] ?></b></p>
            <a href="sukses.php?ref=<?= urlencode($ref) ?>" style="color:#3498db">→ Lihat halaman sukses</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================
// GENERATE VOUCHER
// ============================================
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$voucher_code = '';
for ($i = 0; $i < 6; $i++) {
    $voucher_code .= $chars[random_int(0, strlen($chars) - 1)];
}

$duration_minutes = getVoucherDuration((int)$row['amount']);
$expires_at = date('Y-m-d H:i:s', strtotime("+$duration_minutes minutes"));

// Update database
$stmt = $pdo->prepare("
    UPDATE vouchers
    SET voucher_code = ?, status = 'aktif', payment_status = 'paid',
        duration_minutes = ?, expires_at = ?, paid_at = NOW()
    WHERE order_id = ? AND voucher_code IS NULL
");
$stmt->execute([$voucher_code, $duration_minutes, $expires_at, $ref]);

file_put_contents('callback_log.txt',
    date('[Y-m-d H:i:s] ') . "✅ SIMULASI VOUCHER CREATED: $voucher_code (Ref: $ref)\n",
    FILE_APPEND);

// ============================================
// INTEGRASI KE MIKROTIK
// ============================================
$mikrotik_status = '';
$mikrotik_success = false;

try {
    require_once 'mikrotik_helper.php';

    $mikrotik_success = createMikrotikUser(
        $voucher_code,
        $voucher_code,
        $duration_minutes,
        "Simulasi Order: $ref"
    );

    if ($mikrotik_success) {
        $mikrotik_status = "✅ User MikroTik berhasil dibuat!";
        file_put_contents('callback_log.txt',
            date('[Y-m-d H:i:s] ') . "✅ MIKROTIK USER CREATED: $voucher_code (Profile: " . getMikrotikProfile($duration_minutes) . ")\n\n",
            FILE_APPEND);
    } else {
        $mikrotik_status = "⚠️ Voucher dibuat, tapi GAGAL sinkron ke MikroTik. Cek callback_log.txt";
        file_put_contents('callback_log.txt',
            date('[Y-m-d H:i:s] ') . "⚠️ MIKROTIK SYNC FAILED untuk $voucher_code\n\n",
            FILE_APPEND);
    }
} catch (Exception $e) {
    $mikrotik_status = "❌ Error MikroTik: " . $e->getMessage();
    file_put_contents('callback_log.txt',
        date('[Y-m-d H:i:s] ') . "❌ MIKROTIK INTEGRATION ERROR: " . $e->getMessage() . "\n\n",
        FILE_APPEND);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simulasi Sukses</title>
    <style>
        body { font-family:Arial; padding:30px; background:#f0f2f5; }
        .card { background:#fff; padding:30px; border-radius:16px; max-width:520px; margin:0 auto; text-align:center; box-shadow:0 8px 30px rgba(0,0,0,0.1); }
        .code { font-size:42px; font-weight:bold; letter-spacing:8px; color:#2c3e50; background:#ecf0f1; padding:20px; border-radius:12px; margin:20px 0; font-family:monospace; }
        .btn { display:inline-block; padding:12px 24px; background:#3498db; color:#fff; border-radius:8px; text-decoration:none; font-weight:bold; }
        .status-box { margin-top:16px; padding:12px; border-radius:8px; font-size:14px; text-align:left; }
        .success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
        .warning { background:#fff3cd; color:#856404; border:1px solid #ffeeba; }
        .error   { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="card">
        <h1>✅ Pembayaran Sukses!</h1>
        <p>Voucher berhasil dibuat:</p>
        <div class="code"><?= $voucher_code ?></div>
        <p>Durasi: <b><?= $duration_minutes ?> menit</b><br>Expired: <?= $expires_at ?></p>

        <div class="status-box <?= $mikrotik_success ? 'success' : 'warning' ?>">
            <b>Status MikroTik:</b><br>
            <?= htmlspecialchars($mikrotik_status) ?>
        </div>

        <p style="color:#7f8c8d;font-size:13px;margin-top:16px">
            Buka tab checkout Anda — akan otomatis redirect dalam 4 detik.
        </p>
        <a href="sukses.php?ref=<?= urlencode($ref) ?>" class="btn">Lihat Halaman Sukses</a>
    </div>
</body>
</html>