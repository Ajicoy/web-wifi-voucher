<?php
require_once 'db.php';
header('Content-Type: application/json');

$reference_id = $_GET['ref'] ?? $_GET['order_id'] ?? '';

if (!$reference_id) {
    echo json_encode(['status'=>'error', 'message'=>'no ref']);
    exit;
}

$stmt = $pdo->prepare("SELECT status, voucher_code FROM vouchers WHERE order_id = ? LIMIT 1");
$stmt->execute([$reference_id]);
$row = $stmt->fetch();

if (!$row) {
    echo json_encode(['status'=>'not_found']);
    exit;
}

echo json_encode([
    'status'       => $row['status'],
    'voucher_code' => $row['voucher_code'],
]);