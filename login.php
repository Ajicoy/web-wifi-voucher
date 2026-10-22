<?php
require_once 'db.php';
require_once 'auth.php';

// Jika sudah login, langsung lempar ke dashboard yang sesuai
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: user_dashboard.php');
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login sukses: Simpan sesi
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            // Cek Role: Admin atau User?
            if ($user['role'] === 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                // Jika ada request halaman tertentu (misal mau checkout), arahkan ke situ
                $redirect = $_GET['redirect'] ?? 'user_dashboard.php';
                header("Location: " . $redirect);
            }
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    } else {
        $error = 'Mohon isi email dan password.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NetVoucher</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
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
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            background: var(--bg);
            display: flex;
            min-height: 100vh;
            margin: 0;
        }

        /* Kiri: Area Gambar/Info */
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #1a1a2e 0%, #2d1b3d 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at top right, rgba(230,0,0,0.3) 0%, transparent 60%);
        }
        .login-left-content { position: relative; z-index: 1; text-align: center; max-width: 400px; }
        .login-left h1 { font-size: 36px; font-weight: 800; margin-bottom: 15px; line-height: 1.2; }
        .login-left p { font-size: 16px; opacity: 0.8; line-height: 1.6; }

        /* Kanan: Form Login */
        .login-right {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            background: var(--white);
        }
        .login-form-container {
            width: 100%;
            max-width: 380px;
        }
        .login-header { margin-bottom: 30px; }
        .login-header h2 { font-size: 28px; font-weight: 800; color: var(--secondary); margin-bottom: 8px; }
        .login-header p { color: var(--text-light); font-size: 14px; }
        
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text); font-size: 14px; }
        input {
            width: 100%;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            outline: none;
            font-size: 14px;
            transition: all 0.2s;
            background: var(--bg);
        }
        input:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(230,0,0,0.1);
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(230,0,0,0.3);
        }
        
        .error {
            background: #f8d7da; color: #721c24;
            padding: 12px; border-radius: 8px;
            margin-bottom: 20px; font-size: 14px;
            text-align: center; border: 1px solid #f5c6cb;
        }
        .login-footer { text-align: center; margin-top: 25px; font-size: 14px; color: var(--text-light); }
        .login-footer a { color: var(--primary); font-weight: 600; text-decoration: none; }
        .login-footer a:hover { text-decoration: underline; }

        /* Tombol Back to Home */
        .back-home { display: inline-flex; align-items: center; gap: 5px; color: var(--text-light); text-decoration: none; font-size: 14px; margin-bottom: 20px; font-weight: 600; }
        .back-home:hover { color: var(--primary); }

        /* Responsive */
        @media (max-width: 768px) {
            .login-left { display: none; }
            .login-right { background: var(--bg); }
            .login-form-container { background: var(--white); padding: 40px 30px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        }
    </style>
</head>
<body>

    <!-- Bagian Kiri (Visual) -->
    <div class="login-left">
        <div class="login-left-content">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 20px; color: var(--primary);"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
            <h1>Selamat Datang Kembali!</h1>
            <p>Masuk ke akun NetVoucher Anda untuk mengelola paket internet dan melihat riwayat voucher WiFi Anda.</p>
        </div>
    </div>

    <!-- Bagian Kanan (Form) -->
    <div class="login-right">
        <div class="login-form-container">
            <a href="index.php" class="back-home">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Kembali ke Beranda
            </a>

            <div class="login-header">
                <h2>Masuk Akun</h2>
                <p>Gunakan email dan password Anda. (Admin & User)</p>
            </div>

            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Alamat Email</label>
                    <input type="email" name="email" required placeholder="nama@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Masukkan password Anda">
                </div>
                
                <button type="submit" class="btn-submit">Masuk Sekarang</button>
            </form>

            <div class="login-footer">
                Belum punya akun? <a href="register.php">Daftar Gratis</a>
            </div>
        </div>
    </div>

</body>
</html>