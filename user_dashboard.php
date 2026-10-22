<?php
require_once 'auth.php';
require_once 'db.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Ambil data voucher user
$stmt = $pdo->prepare("SELECT * FROM vouchers WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$vouchers = $stmt->fetchAll();

// Hitung statistik
$total_trx = count($vouchers);
$total_active = 0;
foreach ($vouchers as $v) {
    if ($v['status'] === 'aktif') $total_active++;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - NetVoucher</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ============ RESET & BASE ============ */
        * { margin:0; padding:0; box-sizing:border-box; }
        :root {
            --primary: #e60000;
            --primary-dark: #b80000;
            --secondary: #1a1a2e;
            --text: #2d3748;
            --text-light: #718096;
            --bg: #f7f8fa;
            --white: #ffffff;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 8px 24px rgba(0,0,0,0.08);
            --shadow-lg: 0 16px 48px rgba(0,0,0,0.12);
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        a { text-decoration: none; color: inherit; }

        /* ============ NAVBAR ============ */
        .navbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 14px 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 20px;
            font-weight: 800;
            color: var(--secondary);
        }
        .logo-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--primary), #ff4d4d);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #fff;
        }
        .logo span { color: var(--primary); }
        .nav-actions { display: flex; gap: 10px; align-items: center; }
        .nav-btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .nav-btn-primary { background: var(--primary); color: #fff; }
        .nav-btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .nav-btn-outline { border: 1px solid var(--border); color: var(--text); }
        .nav-btn-outline:hover { background: var(--bg); border-color: var(--text-light); }
        .nav-btn-danger { background: #fee2e2; color: #e74c3c; }
        .nav-btn-danger:hover { background: #fecaca; }

        /* ============ MAIN CONTENT ============ */
        .dashboard-wrapper {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 24px;
            flex-grow: 1;
            width: 100%;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--secondary) 0%, #2d1b3d 100%);
            border-radius: 20px;
            padding: 40px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        .welcome-banner::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at top right, rgba(230,0,0,0.4) 0%, transparent 60%);
        }
        .welcome-text { position: relative; z-index: 1; }
        .welcome-text h1 { font-size: 28px; font-weight: 800; margin-bottom: 8px; }
        .welcome-text p { font-size: 15px; opacity: 0.8; }
        .welcome-stats {
            position: relative; z-index: 1;
            display: flex; gap: 20px;
        }
        .stat-box {
            background: rgba(255,255,255,0.1);
            padding: 15px 25px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            text-align: center;
            border: 1px solid rgba(255,255,255,0.15);
        }
        .stat-box .num { font-size: 24px; font-weight: 800; color: #fff; }
        .stat-box .lbl { font-size: 12px; opacity: 0.8; }

        /* Voucher Section */
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .section-title h2 { font-size: 20px; color: var(--secondary); font-weight: 700; }

        /* Voucher Cards */
        .voucher-grid {
            display: grid;
            gap: 16px;
        }
        .voucher-card {
            background: var(--white);
            border-radius: 16px;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            transition: all 0.2s;
        }
        .voucher-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: rgba(230,0,0,0.2);
        }
        
        .v-info { display: flex; flex-direction: column; gap: 5px; }
        .v-date { font-size: 13px; color: var(--text-light); }
        .v-order { font-size: 14px; font-weight: 600; color: var(--text); }
        
        .v-code-box {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .v-code {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 4px;
            color: var(--secondary);
            background: var(--bg);
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px dashed var(--border);
        }
        .v-code.empty { color: var(--text-light); font-size: 18px; letter-spacing: normal; border: none; background: transparent; padding: 0;}
        
        .v-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
        .v-price { font-weight: 700; color: var(--text); }
        .badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-aktif { background: #d1fae5; color: #065f46; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-expired { background: #fee2e2; color: #991b1b; }

        .btn-copy {
            background: var(--bg);
            border: 1px solid var(--border);
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            color: var(--text);
            font-weight: 600;
            font-size: 13px;
            transition: all 0.2s;
            display: flex; align-items: center; gap: 5px;
        }
        .btn-copy:hover { background: #e2e8f0; color: var(--primary); }

        /* Empty State */
        .empty-state {
            background: var(--white);
            padding: 60px 20px;
            border-radius: 20px;
            text-align: center;
            border: 2px dashed var(--border);
        }
        .empty-state svg { color: #cbd5e1; margin-bottom: 15px; }
        .empty-state h3 { font-size: 20px; color: var(--secondary); margin-bottom: 10px; }
        .empty-state p { color: var(--text-light); margin-bottom: 20px; }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .welcome-banner { flex-direction: column; text-align: center; gap: 20px; padding: 30px; }
            .voucher-card { flex-direction: column; align-items: flex-start; gap: 15px; padding: 20px; }
            .v-meta { align-items: flex-start; flex-direction: row; justify-content: space-between; width: 100%; }
            .v-code-box { width: 100%; justify-content: space-between; }
            .v-code { font-size: 20px; padding: 8px 15px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-container">
        <a href="index.php" class="logo">
            <div class="logo-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
            </div>
            Net<span>Voucher</span>
        </a>
        <div class="nav-actions">
            <a href="index.php" class="nav-btn nav-btn-primary">+ Beli Voucher</a>
            <a href="logout.php" class="nav-btn nav-btn-danger">Logout</a>
        </div>
    </div>
</nav>

<!-- CONTENT -->
<div class="dashboard-wrapper">
    
    <!-- Banner Ucapan -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <h1>Halo, <?= htmlspecialchars(explode(' ', $user_name)[0]) ?>! 👋</h1>
            <p>Selamat datang di dashboard Anda. Kelola voucher internet Anda di sini.</p>
        </div>
        <div class="welcome-stats">
            <div class="stat-box">
                <div class="num"><?= $total_trx ?></div>
                <div class="lbl">Total Transaksi</div>
            </div>
            <div class="stat-box">
                <div class="num"><?= $total_active ?></div>
                <div class="lbl">Voucher Aktif</div>
            </div>
        </div>
    </div>

    <!-- Riwayat Voucher -->
    <div class="section-title">
        <h2>Riwayat Pembelian</h2>
    </div>

    <div class="voucher-grid">
        <?php if (empty($vouchers)): ?>
            <!-- Tampilan Jika Kosong -->
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <h3>Belum Ada Transaksi</h3>
                <p>Anda belum pernah membeli voucher WiFi. Silakan pilih paket terlebih dahulu.</p>
                <a href="index.php" class="nav-btn nav-btn-primary" style="display:inline-flex;">Lihat Paket Voucher</a>
            </div>
        <?php else: ?>
            <!-- List Voucher -->
            <?php foreach ($vouchers as $v): ?>
                <div class="voucher-card">
                    <div class="v-info">
                        <div class="v-date"><?= date('d M Y, H:i', strtotime($v['created_at'])) ?> WIB</div>
                        <div class="v-order">Order ID: <span style="font-family:monospace;"><?= htmlspecialchars($v['order_id']) ?></span></div>
                    </div>

                    <div class="v-code-box">
                        <?php if ($v['voucher_code']): ?>
                            <div class="v-code" id="code-<?= $v['id'] ?>"><?= htmlspecialchars($v['voucher_code']) ?></div>
                            <button class="btn-copy" onclick="copyCode('<?= $v['voucher_code'] ?>', this)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                Copy
                            </button>
                        <?php else: ?>
                            <div class="v-code empty">Menunggu Pembayaran</div>
                        <?php endif; ?>
                    </div>

                    <div class="v-meta">
                        <div class="v-price">Rp <?= number_format($v['amount'],0,',','.') ?></div>
                        <?php if ($v['status'] === 'aktif'): ?>
                            <span class="badge badge-aktif">Aktif</span>
                        <?php elseif ($v['status'] === 'pending'): ?>
                            <span class="badge badge-pending">Pending</span>
                        <?php else: ?>
                            <span class="badge badge-expired">Expired</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<!-- SCRIPT UNTUK FITUR COPY -->
<script>
    function copyCode(code, btn) {
        navigator.clipboard.writeText(code).then(() => {
            const originalText = btn.innerHTML;
            btn.innerHTML = '✅ Tersalin!';
            btn.style.color = '#10b981';
            btn.style.borderColor = '#10b981';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.color = '';
                btn.style.borderColor = '';
            }, 2000);
        });
    }
</script>

</body>
</html>