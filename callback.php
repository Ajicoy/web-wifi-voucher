<?php
require_once 'config/payment.php';
require_once 'db.php';

// Set header response
header('Content-Type: application/json');

// ============================================
// LOGGING INPUT MASUK
// ============================================
$raw_input = file_get_contents('php://input');

$headers = function_exists('getallheaders') ? getallheaders() : [];

file_put_contents('callback_log.txt',
    date('[Y-m-d H:i:s] ') . "=== CALLBACK RECEIVED ===\n" .
    "URL: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n" .
    "Headers: " . print_r($headers, true) . "\n" .
    "Body: " . $raw_input . "\n\n",
    FILE_APPEND);

// ============================================
// PARSE BODY (JSON atau form-urlencoded)
// ============================================
$data = [];
$content_type = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

if (stripos($content_type, 'application/json') !== false) {
    $data = json_decode($raw_input, true) ?: [];
} else {
    parse_str($raw_input, $data);
}

if (empty($data) && !empty($raw_input)) {
    $data = json_decode($raw_input, true) ?: [];
}

if (!$data) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid or empty body']);
    exit;
}

// ============================================
// 1. VALIDASI SIGNATURE (SOFT VALIDATION)
// ============================================
$received_signature = $_SERVER['HTTP_X_SIGNATURE'] ?? $_SERVER['HTTP_SIGNATURE'] ?? $headers['Signature'] ?? $headers['signature'] ?? '';

if (!empty($received_signature)) {
    $payload_for_sign = $data;
    unset($payload_for_sign['signature'], $payload_for_sign['Signature']);
    ksort($payload_for_sign);

    $string_to_sign = '';
    foreach ($payload_for_sign as $k => $v) {
        if (is_array($v)) $v = json_encode($v);
        $string_to_sign .= $k . '=' . $v . '&';
    }
    $string_to_sign = rtrim($string_to_sign, '&');

    $secret = defined('IPAYMU_SECRET') ? IPAYMU_SECRET : (defined('IPAYMU_VA') ? IPAYMU_VA : '');
    $expected_signature = hash_hmac('sha256', $string_to_sign, $secret);

    if (!hash_equals($expected_signature, $received_signature)) {
        file_put_contents('callback_log.txt',
            date('[Y-m-d H:i:s] ') . "⚠️ SIGNATURE MISMATCH!\n" .
            "Expected: $expected_signature\n" .
            "Received: $received_signature\n\n",
            FILE_APPEND);
    }
}

// ============================================
// 2. AMBIL DATA PENTING
// ============================================
$status       = $data['status'] ?? $data['Status'] ?? $data['status_code'] ?? '';
$reference_id = $data['referenceId'] ?? $data['ReferenceId'] ?? $data['reference_id'] ?? '';
$amount       = $data['amount'] ?? $data['Amount'] ?? 0;
$trx_id       = $data['trx_id'] ?? $data['TrxId'] ?? $data['transaction_id'] ?? '';

$is_success = (
    $status == '1' ||
    strtoupper((string)$status) === 'PAID' ||
    strtoupper((string)$status) === 'SUCCESS' ||
    strtoupper((string)$status) === 'BERHASIL'
);

file_put_contents('callback_log.txt',
    date('[Y-m-d H:i:s] ') . "Status: $status | Ref: $reference_id | Amount: $amount | TrxID: $trx_id\n",
    FILE_APPEND);

if ($is_success && !empty($reference_id)) {

    // -----------------------------------------
    // Cek duplikasi
    // -----------------------------------------
    $stmt = $pdo->prepare("SELECT voucher_code FROM vouchers WHERE order_id = ? LIMIT 1");
    $stmt->execute([$reference_id]);
    $existing_voucher = $stmt->fetchColumn();

    if ($existing_voucher) {
        file_put_contents('callback_log.txt',
            date('[Y-m-d H:i:s] ') . "⚠️ Voucher sudah diproses sebelumnya: $existing_voucher\n\n",
            FILE_APPEND);
        http_response_code(200);
        echo json_encode(['status' => 'ok', 'message' => 'Already processed']);
        exit;
    }

    // -----------------------------------------
    // Generate voucher
    // -----------------------------------------
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $voucher_code = '';
    for ($i = 0; $i < 6; $i++) {
        $voucher_code .= $chars[random_int(0, strlen($chars) - 1)];
    }

    $duration_minutes = function_exists('getVoucherDuration') ? getVoucherDuration((int)$amount) : 60;
    $expires_at = date('Y-m-d H:i:s', strtotime("+$duration_minutes minutes"));

    $stmt = $pdo->prepare("
        UPDATE vouchers
        SET voucher_code     = ?,
            status           = 'aktif',
            payment_status   = 'paid',
            duration_minutes = ?,
            expires_at       = ?,
            paid_at          = NOW()
        WHERE order_id = ? AND (voucher_code IS NULL OR voucher_code = '')
    ");
    $stmt->execute([$voucher_code, $duration_minutes, $expires_at, $reference_id]);

    file_put_contents('callback_log.txt',
        date('[Y-m-d H:i:s] ') . "✅ VOUCHER CREATED: $voucher_code (Order ID: $reference_id)\n",
        FILE_APPEND);

    // ============================================
    // 3. INTEGRASI KE MIKROTIK — AUTO CREATE USER
    // ============================================
    try {
        require_once 'mikrotik_helper.php';

        $mikrotik_success = createMikrotikUser(
            $voucher_code,                  // username = kode voucher
            $voucher_code,                  // password = kode voucher
            $duration_minutes,              // durasi sesuai paket
            "Order: $reference_id"          // comment untuk tracking
        );

        if ($mikrotik_success) {
            file_put_contents('callback_log.txt',
                date('[Y-m-d H:i:s] ') . "✅ MIKROTIK USER CREATED: $voucher_code (Profile: " . getMikrotikProfile($duration_minutes) . ")\n\n",
                FILE_APPEND);
        } else {
            file_put_contents('callback_log.txt',
                date('[Y-m-d H:i:s] ') . "⚠️ VOUCHER $voucher_code GAGAL SINKRON KE MIKROTIK (cek log di atas)\n\n",
                FILE_APPEND);
        }
    } catch (Exception $e) {
        // Kalau MikroTik error, jangan sampai bikin callback gagal total.
        // Voucher tetap ada di database, tinggal sync manual kalau perlu.
        file_put_contents('callback_log.txt',
            date('[Y-m-d H:i:s] ') . "❌ MIKROTIK INTEGRATION ERROR: " . $e->getMessage() . "\n\n",
            FILE_APPEND);
    }

} else {
    file_put_contents('callback_log.txt',
        date('[Y-m-d H:i:s] ') . "ℹ️ Status transaksi bukan sukses ($status), dilewati\n\n",
        FILE_APPEND);
}

// Response standar ke Webhook iPaymu
http_response_code(200);
echo json_encode(['status' => 'ok', 'message' => 'Callback processed']);
exit;