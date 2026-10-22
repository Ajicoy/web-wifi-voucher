<?php
require_once 'auth.php';
require_once 'db.php';
require_once 'mikrotik_helper.php';

requireAdmin();

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: admin_dashboard.php');
    exit;
}

// 1. Ambil data voucher
$stmt = $pdo->prepare("SELECT voucher_code FROM vouchers WHERE id = ?");
$stmt->execute([$id]);
$voucher = $stmt->fetch();

if ($voucher) {
    // 2. Hapus user di MikroTik (jika ada kode vouchernya)
    if (!empty($voucher['voucher_code'])) {
        deleteMikrotikUser($voucher['voucher_code']);
    }

    // 3. Hapus data dari database
    $stmt = $pdo->prepare("DELETE FROM vouchers WHERE id = ?");
    $stmt->execute([$id]);
}

// Kembali ke dashboard
header('Location: admin_dashboard.php?msg=deleted');
exit;