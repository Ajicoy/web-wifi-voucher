<?php
require_once 'db.php';
require_once 'auth.php';

if (isset($_SESSION['user_id'])) {
    header('Location: user_dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name && $email && $password) {
        // Cek email sudah terdaftar
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $email, $hash])) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $error = 'Gagal mendaftar. Coba lagi.';
            }
        }
    } else {
        $error = 'Mohon isi semua kolom.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - NetVoucher</title>
    <style>
        /* Gunakan CSS yang sama dengan login.php */
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Plus Jakarta Sans',Arial,sans-serif; }
        body { background:#f0f2f5; display:flex; justify-content:center; align-items:center; min-height:100vh; }
        .card { background:#fff; padding:40px 30px; border-radius:16px; width:100%; max-width:400px; box-shadow:0 8px 30px rgba(0,0,0,0.1); }
        h2 { text-align:center; color:#2c3e50; margin-bottom:20px; }
        .form-group { margin-bottom:15px; }
        label { display:block; margin-bottom:5px; font-weight:600; color:#4a5568; font-size:14px; }
        input { width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px; outline:none; font-size:14px; }
        input:focus { border-color:#e60000; }
        button { width:100%; padding:14px; background:#e60000; color:#fff; border:none; border-radius:8px; font-weight:bold; font-size:16px; cursor:pointer; margin-top:10px; }
        button:hover { background:#b80000; }
        .error { background:#f8d7da; color:#721c24; padding:10px; border-radius:8px; margin-bottom:15px; font-size:14px; text-align:center; }
        .link { text-align:center; margin-top:15px; font-size:14px; }
        .link a { color:#3498db; text-decoration:none; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Daftar Akun</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Daftar</button>
        </form>
        <div class="link">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
</body>
</html>