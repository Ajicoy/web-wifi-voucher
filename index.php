<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NetVoucher — Internet Cepat & Terjangkau</title>
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
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.04);
            --shadow-md: 0 8px 24px rgba(0,0,0,0.08);
            --shadow-lg: 0 16px 48px rgba(0,0,0,0.12);
        }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        img { max-width: 100%; display: block; }
        a { text-decoration: none; color: inherit; }

        /* ============ NAVBAR ============ */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 14px 0;
        }
        .nav-container {
            max-width: 1200px;
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
            font-size: 22px;
            font-weight: 800;
            color: var(--secondary);
            letter-spacing: -0.5px;
        }
        .logo-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary), #ff4d4d);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 4px 12px rgba(230,0,0,0.3);
        }
        .logo span { color: var(--primary); }
        .nav-menu {
            display: flex;
            gap: 32px;
            list-style: none;
            font-weight: 500;
            font-size: 15px;
        }
        .nav-menu a {
            color: var(--text);
            transition: color 0.2s;
        }
        .nav-menu a:hover { color: var(--primary); }
        
        /* Nav Actions (Tombol Kanan) */
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-btn {
            padding: 10px 22px;
            background: var(--primary);
            color: #fff;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .nav-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(230,0,0,0.3);
        }
        .nav-btn-outline {
            background: transparent;
            color: var(--text);
            border: 1px solid var(--border);
        }
        .nav-btn-outline:hover {
            background: var(--bg);
            color: var(--primary);
            border-color: var(--primary);
            box-shadow: none;
        }
        .nav-btn-dark {
            background: var(--secondary);
        }
        .nav-btn-dark:hover {
            background: #2d3748;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        .nav-btn-danger {
            background: #e74c3c;
        }
        .nav-btn-danger:hover {
            background: #c0392b;
            box-shadow: 0 8px 20px rgba(231,76,60,0.3);
        }

        /* ============ HERO ============ */
        .hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #2d1b3d 50%, #e60000 150%);
            color: #fff;
            padding: 80px 24px 100px;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(255,255,255,0.08) 0%, transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(230,0,0,0.2) 0%, transparent 50%);
            pointer-events: none;
        }
        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }
        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 24px;
            backdrop-filter: blur(10px);
        }
        .hero-badge .dot {
            width: 8px;
            height: 8px;
            background: #4ade80;
            border-radius: 50%;
            box-shadow: 0 0 10px #4ade80;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .hero h1 {
            font-size: clamp(32px, 5vw, 56px);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -1.5px;
            margin-bottom: 20px;
        }
        .hero h1 .highlight {
            background: linear-gradient(135deg, #ff4d4d, #ffb800);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero p {
            font-size: clamp(15px, 2vw, 18px);
            opacity: 0.85;
            max-width: 620px;
            margin: 0 auto 36px;
            line-height: 1.7;
        }
        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 48px;
            flex-wrap: wrap;
            margin-top: 40px;
            padding-top: 40px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .hero-stat { text-align: center; }
        .hero-stat .num {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
        }
        .hero-stat .label {
            font-size: 13px;
            opacity: 0.7;
            margin-top: 4px;
        }

        /* ============ BENEFITS ============ */
        .benefits {
            max-width: 1200px;
            margin: -50px auto 0;
            padding: 0 24px;
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }
        .benefit-card {
            background: var(--white);
            padding: 28px 24px;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
            transition: all 0.3s;
        }
        .benefit-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(230,0,0,0.2);
        }
        .benefit-icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: var(--primary);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }
        .benefit-card h3 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--secondary);
        }
        .benefit-card p {
            font-size: 14px;
            color: var(--text-light);
            line-height: 1.6;
        }

        /* ============ PAKET SECTION ============ */
        .packages-section {
            max-width: 1200px;
            margin: 80px auto 0;
            padding: 0 24px 80px;
        }
        .section-header {
            text-align: center;
            margin-bottom: 48px;
        }
        .section-header .label {
            display: inline-block;
            color: var(--primary);
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .section-header h2 {
            font-size: clamp(26px, 3.5vw, 38px);
            font-weight: 800;
            color: var(--secondary);
            letter-spacing: -1px;
            margin-bottom: 12px;
        }
        .section-header p {
            color: var(--text-light);
            max-width: 560px;
            margin: 0 auto;
            font-size: 15px;
        }

        .packages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px;
        }

        .package-card {
            background: var(--white);
            border-radius: 20px;
            padding: 32px 28px;
            border: 2px solid var(--border);
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }
        .package-card:hover {
            border-color: var(--primary);
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(230,0,0,0.12);
        }
        .package-card.popular {
            border-color: var(--primary);
            background: linear-gradient(180deg, #fff 0%, #fff5f5 100%);
            box-shadow: 0 12px 32px rgba(230,0,0,0.15);
        }
        .package-card.popular::before {
            content: 'TERPOPULER';
            position: absolute;
            top: -1px;
            right: 24px;
            background: linear-gradient(135deg, var(--primary), #ff4d4d);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 6px 14px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 12px rgba(230,0,0,0.3);
        }
        .package-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
        }
        .package-card.popular .package-icon {
            background: linear-gradient(135deg, var(--primary), #ff4d4d);
            color: #fff;
            box-shadow: 0 6px 16px rgba(230,0,0,0.3);
        }
        .package-name {
            font-size: 20px;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 4px;
        }
        .package-desc {
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 20px;
        }
        .package-price {
            display: flex;
            align-items: baseline;
            gap: 4px;
            margin-bottom: 22px;
        }
        .package-price .currency {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-light);
        }
        .package-price .amount {
            font-size: 36px;
            font-weight: 800;
            color: var(--secondary);
            letter-spacing: -1.5px;
            line-height: 1;
        }
        .package-price .period {
            font-size: 14px;
            color: var(--text-light);
            font-weight: 500;
        }
        .package-features {
            list-style: none;
            margin-bottom: 24px;
            flex-grow: 1;
        }
        .package-features li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 0;
            font-size: 14px;
            color: var(--text);
        }
        .package-features li svg {
            flex-shrink: 0;
            color: var(--primary);
        }
        .package-btn {
            display: block;
            text-align: center;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            background: var(--secondary);
            color: #fff;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .package-btn:hover {
            background: var(--primary);
            transform: scale(1.02);
            box-shadow: 0 8px 20px rgba(230,0,0,0.3);
        }
        .package-card.popular .package-btn {
            background: var(--primary);
        }
        .package-card.popular .package-btn:hover {
            background: var(--primary-dark);
        }

        /* ============ FOOTER ============ */
        .footer {
            background: var(--secondary);
            color: #a0aec0;
            padding: 56px 24px 28px;
            margin-top: 40px;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        .footer-brand .logo {
            color: #fff;
            margin-bottom: 16px;
        }
        .footer-brand p {
            font-size: 14px;
            line-height: 1.7;
            max-width: 320px;
        }
        .footer h4 {
            color: #fff;
            font-size: 15px;
            margin-bottom: 16px;
            font-weight: 700;
        }
        .footer ul { list-style: none; }
        .footer ul li { margin-bottom: 10px; }
        .footer ul a {
            font-size: 14px;
            transition: color 0.2s;
        }
        .footer ul a:hover { color: #fff; }
        .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .nav-menu { display: none; }
            .hero { padding: 60px 20px 80px; }
            .hero-stats { gap: 24px; }
            .hero-stat .num { font-size: 22px; }
            .benefits { grid-template-columns: 1fr; }
            .footer-container { grid-template-columns: 1fr 1fr; gap: 28px; }
        }
        @media (max-width: 480px) {
            .footer-container { grid-template-columns: 1fr; }
        }

        /* ============ ANIMATIONS ============ */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up {
            animation: fadeUp 0.6s ease-out backwards;
        }
        .fade-up:nth-child(1) { animation-delay: 0.05s; }
        .fade-up:nth-child(2) { animation-delay: 0.1s; }
        .fade-up:nth-child(3) { animation-delay: 0.15s; }
        .fade-up:nth-child(4) { animation-delay: 0.2s; }
        .fade-up:nth-child(5) { animation-delay: 0.25s; }
    </style>
</head>
<body>

<!-- ============ NAVBAR ============ -->
<nav class="navbar">
    <div class="nav-container">
        <div class="logo">
            <div class="logo-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
                    <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
                    <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
                    <line x1="12" y1="20" x2="12.01" y2="20"/>
                </svg>
            </div>
            Net<span>Voucher</span>
        </div>
        <ul class="nav-menu">
            <li><a href="#paket">Paket</a></li>
            <li><a href="#keunggulan">Keunggulan</a></li>
            <li><a href="#bantuan">Bantuan</a></li>
        </ul>
        
        <!-- ============ LOGIKA NAVBAR DINAMIS ============ -->
        <div class="nav-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Jika Sudah Login -->
                <a href="<?= $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php' ?>" class="nav-btn nav-btn-dark">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?>
                </a>
                <a href="logout.php" class="nav-btn nav-btn-danger">Logout</a>
            <?php else: ?>
                <!-- Jika Belum Login -->
                <a href="login.php" class="nav-btn nav-btn-outline">Masuk</a>
                <a href="register.php" class="nav-btn">Daftar</a>
            <?php endif; ?>
            
            <!-- Tombol Beli Selalu Ada -->
            <a href="#paket" class="nav-btn">Beli Voucher</a>
        </div>

    </div>
</nav>

<!-- ============ HERO ============ -->
<section class="hero">
    <div class="hero-container">
        <div class="hero-badge">
            <span class="dot"></span>
            Layanan Aktif 24/7
        </div>
        <h1>Internet Cepat & Stabil<br><span class="highlight">Tanpa Batas</span></h1>
        <p>Nikmati koneksi internet berkecepatan tinggi dengan harga terjangkau. Bayar pakai QRIS — GoPay, OVO, DANA, atau m-Banking. Voucher langsung aktif otomatis.</p>

        <div class="hero-stats">
            <div class="hero-stat">
                <div class="num">99.9%</div>
                <div class="label">Uptime</div>
            </div>
            <div class="hero-stat">
                <div class="num">100 Mbps</div>
                <div class="label">Kecepatan</div>
            </div>
            <div class="hero-stat">
                <div class="num">24/7</div>
                <div class="label">Support</div>
            </div>
            <div class="hero-stat">
                <div class="num">&lt; 30s</div>
                <div class="label">Aktivasi</div>
            </div>
        </div>
    </div>
</section>

<!-- ============ BENEFITS ============ -->
<section class="benefits" id="keunggulan">
    <div class="benefit-card fade-up">
        <div class="benefit-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
        </div>
        <h3>Kecepatan Tinggi</h3>
        <p>Streaming, gaming, dan video call tanpa buffering dengan bandwidth hingga 100 Mbps.</p>
    </div>
    <div class="benefit-card fade-up">
        <div class="benefit-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>
        <h3>Pembayaran Aman</h3>
        <p>Transaksi QRIS terenkripsi melalui payment gateway tersertifikasi Bank Indonesia.</p>
    </div>
    <div class="benefit-card fade-up">
        <div class="benefit-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <h3>Aktivasi Instan</h3>
        <p>Voucher langsung diterima setelah pembayaran sukses. Tanpa menunggu lama.</p>
    </div>
    <div class="benefit-card fade-up">
        <div class="benefit-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
        </div>
        <h3>Support 24/7</h3>
        <p>Tim teknis siap membantu kapan saja melalui WhatsApp dan live chat.</p>
    </div>
</section>

<!-- ============ PAKET ============ -->
<section class="packages-section" id="paket">
    <div class="section-header">
        <span class="label">Pilih Paket Anda</span>
        <h2>Harga Jujur, Koneksi Nyata</h2>
        <p>Sesuaikan durasi dengan kebutuhan Anda. Semua paket sudah termasuk pajak.</p>
    </div>

    <div class="packages-grid">
        <?php
        $packages = [
            1 => [
                'name'     => 'Paket Kilat',
                'desc'     => 'Cocok untuk browsing & chat',
                'price'    => 2000,
                'period'   => '/ 1 jam',
                'features' => ['Unlimited data', 'Kecepatan 20 Mbps', '1 perangkat'],
                'icon'     => 'zap'
            ],
            2 => [
                'name'     => 'Paket Santai',
                'desc'     => 'Streaming & kerja santai',
                'price'    => 5000,
                'period'   => '/ 3 jam',
                'features' => ['Unlimited data', 'Kecepatan 50 Mbps', '2 perangkat'],
                'icon'     => 'coffee',
                'popular'  => true
            ],
            3 => [
                'name'     => 'Paket Harian',
                'desc'     => 'Internet seharian penuh',
                'price'    => 10000,
                'period'   => '/ 1 hari',
                'features' => ['Unlimited data', 'Kecepatan 75 Mbps', '3 perangkat'],
                'icon'     => 'sun'
            ],
            4 => [
                'name'     => 'Paket Weekend',
                'desc'     => 'Hemat untuk 3 hari',
                'price'    => 20000,
                'period'   => '/ 3 hari',
                'features' => ['Unlimited data', 'Kecepatan 100 Mbps', '5 perangkat'],
                'icon'     => 'calendar'
            ],
            5 => [
                'name'     => 'Paket Mingguan',
                'desc'     => 'Paling hemat untuk 7 hari',
                'price'    => 50000,
                'period'   => '/ 7 hari',
                'features' => ['Unlimited data', 'Kecepatan 100 Mbps', 'Unlimited device'],
                'icon'     => 'award'
            ],
        ];

        $icons = [
            'zap'      => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
            'coffee'   => '<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>',
            'sun'      => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>',
            'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
            'award'    => '<circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>'
        ];

        foreach ($packages as $id => $pkg):
            $popular = !empty($pkg['popular']);
        ?>
            <div class="package-card fade-up <?= $popular ? 'popular' : '' ?>">
                <div class="package-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <?= $icons[$pkg['icon']] ?>
                    </svg>
                </div>
                <div class="package-name"><?= htmlspecialchars($pkg['name']) ?></div>
                <div class="package-desc"><?= htmlspecialchars($pkg['desc']) ?></div>
                <div class="package-price">
                    <span class="currency">Rp</span>
                    <span class="amount"><?= number_format($pkg['price'], 0, ',', '.') ?></span>
                    <span class="period"><?= $pkg['period'] ?></span>
                </div>
                <ul class="package-features">
                    <?php foreach ($pkg['features'] as $feature): ?>
                        <li>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <?= htmlspecialchars($feature) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="checkout.php?package=<?= $id ?>" class="package-btn">
                    Beli Sekarang
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ FOOTER ============ -->
<footer class="footer" id="bantuan">
    <div class="footer-container">
        <div class="footer-brand">
            <div class="logo">
                <div class="logo-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
                        <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
                        <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
                        <line x1="12" y1="20" x2="12.01" y2="20"/>
                    </svg>
                </div>
                Net<span>Voucher</span>
            </div>
            <p>Layanan internet voucher untuk kebutuhan harian, mingguan, dan event. Cepat, stabil, dan terjangkau.</p>
        </div>
        <div>
            <h4>Produk</h4>
            <ul>
                <li><a href="#paket">Paket Harian</a></li>
                <li><a href="#paket">Paket Mingguan</a></li>
                <li><a href="#paket">Paket Event</a></li>
            </ul>
        </div>
        <div>
            <h4>Bantuan</h4>
            <ul>
                <li><a href="#">Cara Beli</a></li>
                <li><a href="#">FAQ</a></li>
                <li><a href="#">Kontak</a></li>
            </ul>
        </div>
        <div>
            <h4>Legal</h4>
            <ul>
                <li><a href="#">Syarat & Ketentuan</a></li>
                <li><a href="#">Kebijakan Privasi</a></li>
                <li><a href="#">Refund Policy</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <div>© <?= date('Y') ?> NetVoucher. All rights reserved.</div>
        <div>Powered by Midtrans QRIS</div>
    </div>
</footer>

</body>
</html>