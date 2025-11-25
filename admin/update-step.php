<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) exit;

$id = $_POST['id'] ?? null;
if (!$id) { header('Location: cara-pesan.php'); exit; }

$icon = trim($_POST['icon_class'] ?? 'fa-cookie-bite');
$step_number = intval($_POST['step_number']);
$title = trim($_POST['title'] ?? '');
$desc = trim($_POST['description'] ?? '');

if (empty($title) || empty($desc)) {
    $_SESSION['cp_alert'] = 'Gagal: Judul dan deskripsi wajib diisi.';
} else {
    try {
        $stmt = $pdo->prepare("
            UPDATE cara_pesan_steps 
            SET icon_class = ?, step_number = ?, title = ?, description = ?
            WHERE id = ?
        ");
        $stmt->execute([$icon, $step_number, $title, $desc, $id]);
        $_SESSION['cp_alert'] = 'Berhasil: Langkah telah diperbarui.';
    } catch (Exception $e) {
        $_SESSION['cp_alert'] = 'Gagal: Gagal memperbarui langkah.';
    }
}
header('Location: cara-pesan.php');
exit;