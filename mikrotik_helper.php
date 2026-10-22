<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/payment.php';

use RouterOS\Client;
use RouterOS\Query;

/**
 * Membuat user hotspot di MikroTik
 */
function createMikrotikUser($username, $password, $duration_minutes, $comment = '') {
    try {
        $client = new Client([
            'host' => MIKROTIK_HOST,
            'user' => MIKROTIK_USER,
            'pass' => MIKROTIK_PASS,
            'port' => MIKROTIK_PORT,
        ]);

        $profile = getMikrotikProfile($duration_minutes);

        $query = new Query('/ip/hotspot/user/add');
        $query->equal('name', $username);
        $query->equal('password', $password);
        $query->equal('profile', $profile);
        $query->equal('limit-uptime', "{$duration_minutes}m");
        $query->equal('comment', $comment);

        $client->query($query)->read();

        file_put_contents('callback_log.txt',
            date('[Y-m-d H:i:s] ') . "✅ MIKROTIK USER CREATED: $username (Profile: $profile, Durasi: {$duration_minutes}m)\n",
            FILE_APPEND);
        return true;

    } catch (Exception $e) {
        file_put_contents('callback_log.txt',
            date('[Y-m-d H:i:s] ') . "❌ MIKROTIK ERROR: " . $e->getMessage() . "\n",
            FILE_APPEND);
        return false;
    }
}

/**
 * Hapus user hotspot dari MikroTik (opsional)
 */
function deleteMikrotikUser($username) {
    try {
        $client = new Client([
            'host' => MIKROTIK_HOST,
            'user' => MIKROTIK_USER,
            'pass' => MIKROTIK_PASS,
            'port' => MIKROTIK_PORT,
        ]);

        $query = new Query('/ip/hotspot/user/print');
        $query->where('name', $username);
        $response = $client->query($query)->read();

        foreach ($response as $user) {
            if (isset($user['name']) && $user['name'] === $username) {
                $removeQuery = new Query('/ip/hotspot/user/remove');
                $removeQuery->equal('.id', $user['.id']);
                $client->query($removeQuery)->read();
            }
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}