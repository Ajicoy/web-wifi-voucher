<?php
session_start();
require_once 'config/payment.php';
require_once 'db.php';
require_once 'auth.php';    // ← TAMBAHKAN INI

requireLogin();             // ← TAMBAHKAN INI (paksa login sebelum bayar)

$package_id = $_GET['package'] ?? null;

$packages = [
    1 => ['name'=>'Paket 1 Jam',  'price'=>2000],
    2 => ['name'=>'Paket 3 Jam',  'price'=>5000],
    3 => ['name'=>'Paket 1 Hari', 'price'=>10000],
    4 => ['name'=>'Paket 3 Hari', 'price'=>20000],
    5 => ['name'=>'Paket 7 Hari', 'price'=>50000],
];

if (!$package_id || !isset($packages[$package_id])) {
    header('Location: index.php');
    exit;
}

$package = $packages[$package_id];

// Generate reference ID unik
$reference_id = 'WIFI-' . date('YmdHis') . '-' . rand(1000, 9999);

$_SESSION['reference_id'] = $reference_id;
$_SESSION['package_id']   = $package_id;

// ============================================
// 2. REQUEST KE iPAYMU
// ============================================
$notify_url = NGROK_URL . '/callback.php';

$body = [
    'name'          => 'Pelanggan WiFi',
    'phone'         => '08123456789',
    'email'         => 'customer@example.com',
    'amount'        => $package['price'],
    'notifyUrl'     => $notify_url,
    'referenceId'   => $reference_id,
    'paymentMethod' => 'qris',
    'paymentChannel'=> 'qris',
    'comments'      => $package['name'],
];

$bodyJson  = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$signature = generateSignature('POST', IPAYMU_VA, $bodyJson, IPAYMU_API_KEY);

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL            => IPAYMU_API_URL,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $bodyJson,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        'va: ' . IPAYMU_VA,
        'signature: ' . $signature,
        'timestamp: ' . time(),
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 30,
]);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err  = curl_error($ch);
curl_close($ch);

$result = json_decode($response, true);

// ============================================
// 3. HANDLE ERROR — cek QrImage, BUKAN Url
// ============================================
if ($http_code !== 200 
    || empty($result['Success']) 
    || empty($result['Data']['QrImage'])) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Error Pembayaran</title>
        <style>
            * { margin:0; padding:0; box-sizing:border-box; font-family:Arial,sans-serif; }
            body { background:#f0f2f5; display:flex; justify-content:center; align-items:center;
                   min-height:100vh; padding:20px; }
            .card { background:#fff; padding:32px; border-radius:16px; max-width:600px;
                    width:100%; box-shadow:0 8px 30px rgba(0,0,0,0.1); }
            .icon { font-size:56px; text-align:center; margin-bottom:16px; }
            h2 { color:#721c24; text-align:center; margin-bottom:20px; }
            .error-box { background:#f8d7da; border:1px solid #f5c6cb; color:#721c24;
                         padding:16px; border-radius:8px; margin:16px 0; }
            pre { background:#1e1e1e; color:#d4d4d4; padding:16px; border-radius:8px;
                  overflow-x:auto; font-size:12px; }
            .btn { display:inline-block; padding:12px 24px; background:#3498db; color:#fff;
                   border-radius:8px; text-decoration:none; font-weight:600; margin-top:12px; }
        </style>
    </head>
    <body>
    <div class="card">
        <div class="icon">⚠️</div>
        <h2>Pembayaran Gagal Dibuat</h2>
        <div class="error-box">
            <strong>HTTP Code:</strong> <?= $http_code ?><br>
            <?php if ($curl_err): ?>
                <strong>cURL Error:</strong> <?= htmlspecialchars($curl_err) ?>
            <?php endif; ?>
        </div>
        <details>
            <summary style="cursor:pointer;"><b>Lihat detail response</b></summary>
            <pre><?= htmlspecialchars(print_r($result, true)) ?></pre>
        </details>
        <div style="text-align:center;">
            <a href="index.php" class="btn">Kembali ke Beranda</a>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================
// 4. AMBIL DATA DARI RESPONSE
// ============================================
$data       = $result['Data'];
$session_id = $data['SessionId'] ?? $reference_id;
$qr_image   = $data['QrImage'] ?? '';
$expired    = $data['Expired'] ?? '';

// Simpan transaksi pending ke database
$stmt = $pdo->prepare("
    INSERT INTO vouchers (order_id, user_id, package_id, amount, status, payment_status, created_at)
    VALUES (?, ?, ?, ?, 'pending', 'pending', NOW())
");
$stmt->execute([$reference_id, $_SESSION['user_id'], $package_id, $package['price']]);

// ============================================
// 5. TAMPILKAN HALAMAN QRIS
// ============================================
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran QRIS</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',Arial,sans-serif; }
        body { background:#f0f2f5; display:flex; justify-content:center; align-items:center;
               min-height:100vh; padding:20px; }
        .container { background:#fff; padding:28px; border-radius:16px; max-width:440px;
                     width:100%; text-align:center; box-shadow:0 8px 30px rgba(0,0,0,0.1); }
        .badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px;
                 font-weight:700; margin-bottom:12px; background:#fff3cd; color:#856404; }
        h2 { color:#2c3e50; font-size:22px; margin-bottom:6px; }
        .price { font-size:30px; font-weight:800; color:#e74c3c; margin:12px 0 18px; }
        .qr-box { background:#fff; border:2px solid #e2e8f0; border-radius:12px;
                  padding:16px; margin:16px 0; }
        .qr-box img { width:100%; max-width:280px; height:auto; margin:0 auto; display:block; }
        .info { color:#7f8c8d; font-size:13px; margin:8px 0; }
        .info strong { color:#2c3e50; }
        .status { margin-top:18px; padding:14px; border-radius:10px;
                  background:#e7f3ff; color:#004085; font-size:14px; }
        .status.success { background:#d4edda; color:#155724; }
        .status.error   { background:#f8d7da; color:#721c24; }
        .loader { display:inline-block; border:3px solid #f3f3f3; border-top:3px solid #3498db;
                  border-radius:50%; width:20px; height:20px; animation:spin 1s linear infinite;
                  vertical-align:middle; margin-right:8px; }
        @keyframes spin { 0%{transform:rotate(0)} 100%{transform:rotate(360deg)} }
        .demo-warning { background:#fff3cd; border:1px solid #ffc107;
                        padding:10px 14px; border-radius:8px; font-size:12px;
                        color:#856404; margin:12px 0; text-align:left; line-height:1.5; }
        .demo-warning code { background:rgba(0,0,0,0.1); padding:2px 6px; border-radius:4px;
                             font-size:11px; word-break:break-all; }
    </style>
</head>
<body>
<div class="container">
    <span class="badge">🟢 SANDBOX MODE</span>
    <h2><?= htmlspecialchars($package['name']) ?></h2>
    <div class="price">Rp <?= number_format($package['price'],0,',','.') ?></div>

    <?php if ($qr_image): ?>
        <div class="qr-box">
            <img src="<?= htmlspecialchars($qr_image) ?>" alt="QRIS Code">
        </div>
    <?php endif; ?>

    <div class="demo-warning">
        ⚠️ <strong>Mode Sandbox:</strong> QR ini DEMO — tidak bisa discan/dibayar.<br>
        Untuk simulasi pembayaran sukses, buka <strong>dashboard iPaymu → menu "Tes Notify"</strong>, masukkan Session ID:
        <br><code><?= htmlspecialchars($session_id) ?></code>
    </div>

    <div class="info">
        <strong>Order ID:</strong> <?= htmlspecialchars($reference_id) ?><br>
        <strong>Expired:</strong> <?= htmlspecialchars($expired) ?>
    </div>

    <div id="status" class="status">
        <span class="loader"></span>
        Menunggu pembayaran...
    </div>
</div>

<script>
    const refId = '<?= $reference_id ?>';
    let pollingCount = 0;
    const maxPolling = 150;

    function checkStatus() {
        if (pollingCount++ > maxPolling) {
            document.getElementById('status').className = 'status error';
            document.getElementById('status').innerHTML = '⌛ Waktu habis. Silakan refresh.';
            return;
        }

        fetch('cek_status.php?ref=' + encodeURIComponent(refId))
            .then(r => r.json())
            .then(data => {
                if (data.status === 'aktif' && data.voucher_code) {
                    document.getElementById('status').className = 'status success';
                    document.getElementById('status').innerHTML = '✅ Pembayaran berhasil! Mengalihkan...';
                    setTimeout(() => {
                        window.location.href = 'sukses.php?ref=' + encodeURIComponent(refId);
                    }, 1000);
                } else {
                    setTimeout(checkStatus, 4000);
                }
            })
            .catch(e => {
                console.error(e);
                setTimeout(checkStatus, 4000);
            });
    }

    setTimeout(checkStatus, 3000);
</script>
</body>
</html>