<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    exit('Akses ditolak.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if (!$id) {
        $_SESSION['tk_alert'] = '❌ ID foto tidak valid.';
    } else {
        try {
            if ($action === 'activate') {
                $pdo->prepare("UPDATE carousel_photos SET is_active = 1 WHERE id = ?")->execute([$id]);
                $_SESSION['tk_alert'] = '✅ Foto diaktifkan.';
            } elseif ($action === 'deactivate') {
                $pdo->prepare("UPDATE carousel_photos SET is_active = 0 WHERE id = ?")->execute([$id]);
                $_SESSION['tk_alert'] = '✅ Foto dinonaktifkan.';
            } else {
                $_SESSION['tk_alert'] = '❌ Aksi tidak dikenali.';
            }
        } catch (Exception $e) {
            $_SESSION['tk_alert'] = '❌ Gagal memperbarui status.';
        }
    }
}

header('Location: tentang_kami.php');
exit;