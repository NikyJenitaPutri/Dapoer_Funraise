<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tentang'])) {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (!$title || !$subtitle || !$content) {
        $_SESSION['tk_alert'] = '❌ Gagal: Semua field wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO tentang_kami_section (id, title, subtitle, content) VALUES (1, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    subtitle = VALUES(subtitle),
                    content = VALUES(content),
                    updated_at = NOW()
            ");
            $stmt->execute([$title, $subtitle, $content]);
            $_SESSION['tk_alert'] = '✅ Konten "Tentang Kami" berhasil diperbarui.';
        } catch (Exception $e) {
            $_SESSION['tk_alert'] = '❌ Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

header('Location: tentang_kami.php');
exit;