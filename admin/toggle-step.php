<?php
session_start();
require_once '../config.php';
$id = $_GET['id'] ?? 0;
$action = $_GET['action'] ?? '';

if ($action === 'activate') {
    $pdo->prepare("UPDATE cara_pesan_steps SET is_active = 1 WHERE id = ?")->execute([$id]);
} elseif ($action === 'deactivate') {
    $pdo->prepare("UPDATE cara_pesan_steps SET is_active = 0 WHERE id = ?")->execute([$id]);
}

$_SESSION['cp_alert'] = 'Berhasil: Status langkah diperbarui.';
header('Location: cara-pesan.php');
exit;