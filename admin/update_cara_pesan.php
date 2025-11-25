<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');

    if (empty($title) || empty($subtitle)) {
        $_SESSION['cp_alert'] = 'Gagal: Judul dan subjudul wajib diisi.';
        header('Location: cara-pesan.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE cara_pesan_section 
            SET title = ?, subtitle = ?, updated_at = NOW() 
            WHERE id = 1
        ");
        $stmt->execute([$title, $subtitle]);
        $_SESSION['cp_alert'] = 'Berhasil: Judul & subjudul telah diperbarui.';
    } catch (Exception $e) {
        error_log('Cara Pesan update error: ' . $e->getMessage());
        $_SESSION['cp_alert'] = 'Gagal: Gagal memperbarui data. Coba lagi.';
    }
}

header('Location: cara-pesan.php');
exit;