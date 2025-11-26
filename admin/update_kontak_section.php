<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    if (!$title || !$subtitle) {
        $_SESSION['kontak_alert'] = 'Judul dan subjudul wajib diisi.';
    } else {
        try {
            $pdo->prepare("
                INSERT INTO kontak_section (id, title, subtitle) VALUES (1, ?, ?)
                ON DUPLICATE KEY UPDATE title = VALUES(title), subtitle = VALUES(subtitle)
            ")->execute([$title, $subtitle]);
            $_SESSION['kontak_alert'] = 'Judul & subjudul berhasil diperbarui.';
        } catch (Exception $e) {
            $_SESSION['kontak_alert'] = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}
header('Location: kontak.php');
exit;