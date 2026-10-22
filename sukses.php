<?php
require_once 'db.php';

$reference_id = $_GET['ref'] ?? $_GET['order_id'] ?? '';
if (!$reference_id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM vouchers WHERE order_id = ? LIMIT 1");
$stmt->execute([$reference_id]);
$voucher = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher WiFi Anda</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:Arial,sans-serif; }
        body { background:#f0f2f5; display:flex; justify-content:center; align-items:center;
               min-height:100vh; padding:20px; }
        .card { background:#fff; padding:40px 30px; border-radius:16px; max-width:420px;
                width:100%; text-align:center; box-shadow:0 8px 30px rgba(0,0,0,0.12); }
        .icon { font-size:64px; margin-bottom:15px; }
        .code { font-size:42px; font-weight:bold; letter-spacing:8px; color:#2c3e50;
                background:#ecf0f1; padding:20px; border-radius:12px; margin:20px 0;
                font-family:'Courier New',monospace; }
        .info { color:#7f8c8d; font-size:14px; margin:8px 0; }
        .btn { display:inline-block; margin-top:20px; padding:12px 30px;
               background:#3498db; color:#fff; text-decoration:none;
               border-radius:8px; font-weight:bold; }
    </style>
</head>
<body>
<div class="card">
<?php if ($voucher && $voucher['voucher_code']): ?>
    <div class="icon">✅</div>
    <h2>Pembayaran Berhasil!</h2>
    <p class="info">Gunakan kode ini untuk login WiFi:</p>
    <div class="code"><?= htmlspecialchars($voucher['voucher_code']) ?></div>
    <p class="info">Durasi: <b><?= $voucher['duration_minutes'] ?> menit</b></p>
    <p class="info">Berlaku hingga: <?= date('d/m/Y H:i', strtotime($voucher['expires_at'])) ?></p>
    <a href="index.php" class="btn">Kembali ke Beranda</a>
<?php else: ?>
    <div class="icon">⏳</div>
    <h2>Menunggu Konfirmasi</h2>
    <p class="info">Pembayaran sedang diproses. Halaman ini akan otomatis refresh.</p>
    <a href="sukses.php?ref=<?= htmlspecialchars($reference_id) ?>" class="btn">Refresh Manual</a>
    <script>setTimeout(()=>location.reload(), 5000);</script>
<?php endif; ?>
</div>
</body>
</html>