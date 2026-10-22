<?php
require_once 'db.php';
require_once 'auth.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    header('Location: admin_dashboard.php');
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
            // Cek apakah role-nya admin
            if ($user['role'] === 'admin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error = 'Akses ditolak. Akun ini bukan Administrator.';
            }
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
    <title>Admin Login - NetVoucher</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',Arial,sans-serif; }
        body { background:#1a1a2e; display:flex; justify-content:center; align-items:center; min-height:100vh; }
        .card { background:#fff; padding:40px 30px; border-radius:16px; width:100%; max-width:400px; box-shadow:0 10px 40px rgba(0,0,0,0.3); border-top:5px solid #e60000; }
        h2 { text-align:center; color:#2c3e50; margin-bottom:5px; }
        .subtitle { text-align:center; color:#718096; font-size:13px; margin-bottom:25px; }
        .form-group { margin-bottom:15px; }
        label { display:block; margin-bottom:5px; font-weight:600; color:#4a5568; font-size:14px; }
        input { width:100%; padding:12px; border:1px solid #e2e8f0; border-radius:8px; outline:none; font-size:14px; }
        input:focus { border-color:#e60000; }
        button { width:100%; padding:14px; background:#e60000; color:#fff; border:none; border-radius:8px; font-weight:bold; font-size:16px; cursor:pointer; margin-top:10px; }
        button:hover { background:#b80000; }
        .error { background:#f8d7da; color:#721c24; padding:10px; border-radius:8px; margin-bottom:15px; font-size:14px; text-align:center; }
    </style>
</head>
<body>
    <div class="card">
        <h2>👑 Admin Panel</h2>
        <div class="subtitle">Silakan login dengan akun Administrator</div>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email Admin</label>
                <input type="email" name="email" required placeholder="admin@netvoucher.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="********">
            </div>
            <button type="submit">Masuk ke Dashboard</button>
        </form>
    </div>
</body>
</html>