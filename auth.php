<?php
// auth.php - Helper untuk autentikasi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Cek apakah user sudah login. Jika belum, redirect ke login.php
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Cek apakah user adalah admin. Jika bukan, redirect ke dashboard user
 */
function requireAdmin() {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: user_dashboard.php');
        exit;
    }
}

/**
 * Mendapatkan info user yang sedang login
 */
function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? '',
        'role' => $_SESSION['role'] ?? 'user'
    ];
}