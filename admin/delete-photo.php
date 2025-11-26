<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    exit('Akses ditolak.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        // Ambil path dulu
        $stmt = $pdo->prepare("SELECT image_path FROM carousel_photos WHERE id = ?");
        $stmt->execute([$id]);
        $photo = $stmt->fetch();
        
        if ($photo) {
            $path = '../' . $photo['image_path'];
            if (file_exists($path)) {
                unlink($path);
            }
            $pdo->prepare("DELETE FROM carousel_photos WHERE id = ?")->execute([$id]);
            $_SESSION['tk_alert'] = 'Foto berhasil dihapus.';
        } else {
            $_SESSION['tk_alert'] = 'Foto tidak ditemukan di database.';
        }
    } else {
        $_SESSION['tk_alert'] = 'ID tidak valid.';
    }
}

header('Location: tentang_kami.php');
exit;