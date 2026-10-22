<?php
require_once 'auth.php';
require_once 'db.php';
requireAdmin();

// Statistik
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(amount) FROM vouchers WHERE status='aktif'")->fetchColumn() ?? 0;
$total_trx = $pdo->query("SELECT COUNT(*) FROM vouchers")->fetchColumn();

// Ambil semua transaksi (Join dengan tabel users)
$transactions = $pdo->query("
    SELECT v.*, u.name as user_name 
    FROM vouchers v 
    LEFT JOIN users u ON v.user_id = u.id 
    ORDER BY v.created_at DESC 
")->fetchAll();

// Fungsi untuk menghitung sisa waktu
function getRemainingTime($expires_at) {
    if (!$expires_at) return '-';
    
    $now = time();
    $expire = strtotime($expires_at);
    $diff = $expire - $now;

    if ($diff <= 0) {
        return '<span style="color:#e74c3c; font-weight:bold;">Kedaluwarsa</span>';
    }

    $days = floor($diff / 86400);
    $hours = floor(($diff % 86400) / 3600);
    $minutes = floor(($diff % 3600) / 60);

    $result = '';
    if ($days > 0) $result .= "$days hari ";
    if ($hours > 0) $result .= "$hours jam ";
    if ($days == 0) $result .= "$minutes mnt";

    return '<span style="color:#10b981; font-weight:bold;">' . trim($result) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NetVoucher</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        :root {
            --primary: #e60000; --secondary: #1a1a2e; --bg: #f7f8fa;
            --white: #ffffff; --border: #e2e8f0; --text: #2d3748;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); }
        a { text-decoration: none; color: inherit; }

        /* NAVBAR */
        .navbar { background: var(--secondary); padding: 15px 0; color: #fff; }
        .nav-container { max-width: 1100px; margin: 0 auto; padding: 0 24px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 20px; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .nav-actions { display: flex; gap: 10px; }
        .btn { padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: 0.2s; border: none; cursor: pointer; }
        .btn-danger { background: #e74c3c; color: #fff; }
        .btn-danger:hover { background: #c0392b; }

        /* CONTENT */
        .wrapper { max-width: 1100px; margin: 40px auto; padding: 0 24px; }
        .page-title { font-size: 24px; font-weight: 800; margin-bottom: 25px; color: var(--secondary); }

        /* ALERT */
        .alert { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #a7f3d0; display: flex; justify-content: space-between; align-items: center; }
        .alert-close { cursor: pointer; font-weight: bold; }

        /* STATS GRID */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--white); padding: 25px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid var(--border); position: relative; overflow: hidden; }
        .stat-card::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 5px; background: var(--primary); }
        .stat-card .title { font-size: 13px; color: #718096; font-weight: 600; text-transform: uppercase; margin-bottom: 10px; }
        .stat-card .value { font-size: 28px; font-weight: 800; color: var(--secondary); }

        /* TABLE */
        .table-container { background: var(--white); border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid var(--border); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { background: #f8fafc; padding: 16px 20px; text-align: left; font-size: 13px; font-weight: 700; color: #718096; text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 16px 20px; font-size: 14px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: #f8fafc; }
        
        .code { font-family: 'Courier New', monospace; font-weight: 800; color: var(--primary); letter-spacing: 1px; }
        .badge { padding: 4px 10px; border-radius: 50px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-aktif { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-other { background: #fee2e2; color: #991b1b; }
        
        .btn-delete { background: #fee2e2; color: #e74c3c; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; border: none; transition: 0.2s; }
        .btn-delete:hover { background: #e74c3c; color: #fff; }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="logo">👑 NetVoucher Admin</div>
        <div class="nav-actions">
            <a href="index.php" class="btn" style="background:#3498db; color:#fff;">Lihat Web</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="wrapper">
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert">
            <span>✅ Voucher berhasil dihapus dari sistem & MikroTik.</span>
            <span class="alert-close" onclick="this.parentElement.style.display='none'">✕</span>
        </div>
    <?php endif; ?>

    <h1 class="page-title">Ringkasan Sistem</h1>

    <!-- Statistik -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="title">Total Pendapatan</div>
            <div class="value">Rp <?= number_format($total_revenue, 0, ',', '.') ?></div>
        </div>
        <div class="stat-card">
            <div class="title">Total Transaksi</div>
            <div class="value"><?= $total_trx ?></div>
        </div>
        <div class="stat-card">
            <div class="title">Total Pelanggan</div>
            <div class="value"><?= $total_users ?></div>
        </div>
    </div>

    <h2 class="page-title" style="font-size: 18px; margin-top: 40px;">History Transaksi & Monitoring Voucher</h2>
    
    <!-- Tabel Transaksi -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Voucher</th>
                    <th>Nominal</th>
                    <th>Sisa Waktu</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr><td colspan="7" style="text-align:center;">Belum ada data transaksi.</td></tr>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td>
                            <?= date('d/m/y', strtotime($t['created_at'])) ?><br>
                            <small style="color:#718096;"><?= date('H:i', strtotime($t['created_at'])) ?> WIB</small>
                        </td>
                        <td>
                            <b><?= htmlspecialchars($t['user_name'] ?? 'Guest') ?></b><br>
                            <small style="color:#718096; font-family:monospace;"><?= htmlspecialchars($t['order_id']) ?></small>
                        </td>
                        <td class="code"><?= $t['voucher_code'] ?: '-' ?></td>
                        <td>Rp <?= number_format($t['amount'], 0, ',', '.') ?></td>
                        <td>
                            <?php if ($t['status'] === 'aktif'): ?>
                                <?= getRemainingTime($t['expires_at']) ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($t['status'] === 'aktif'): ?>
                                <span class="badge badge-aktif">Aktif</span>
                            <?php elseif ($t['status'] === 'pending'): ?>
                                <span class="badge badge-pending">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-other"><?= strtoupper($t['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn-delete" onclick="confirmDelete(<?= $t['id'] ?>, '<?= $t['voucher_code'] ?: $t['order_id'] ?>')">
                                Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- JavaScript Konfirmasi Hapus -->
<script>
    function confirmDelete(id, code) {
        if (confirm("Apakah Anda yakin ingin menghapus data ini?\n\nKode: " + code + "\n\nUser akan langsung ter-kick dari WiFi (MikroTik).")) {
            window.location.href = 'delete_voucher.php?id=' + id;
        }
    }
</script>

</body>
</html>