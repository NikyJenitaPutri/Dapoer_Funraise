<?php
session_start();
require_once '../config.php';
$id = $_GET['id'] ?? 0;
$pdo->prepare("DELETE FROM cara_pesan_steps WHERE id = ?")->execute([$id]);
$_SESSION['cp_alert'] = 'Berhasil: Langkah telah dihapus.';
header('Location: cara-pesan.php');
exit;