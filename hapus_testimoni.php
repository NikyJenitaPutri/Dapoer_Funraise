<?php
session_start();
require 'config.php';

// Cek apakah user login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Cek apakah ID testimoni diberikan
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: lihat_testimoni.php?msg=ID%20testimoni%20tidak%20valid.');
    exit;
}

$id = (int)$_GET['id'];

// Ambil nama testimoni sebelum dihapus (untuk konfirmasi pesan)
$stmt = $pdo->prepare("SELECT nama FROM testimoni WHERE id = ?");
$stmt->execute([$id]);
$testimoni = $stmt->fetch();

if (!$testimoni) {
    header('Location: lihat_testimoni.php?msg=Testimoni%20tidak%20ditemukan.');
    exit;
}

// Hapus testimoni
$stmt = $pdo->prepare("DELETE FROM testimoni WHERE id = ?");
$stmt->execute([$id]);

// Redirect kembali ke halaman daftar testimoni dengan pesan sukses
header('Location: lihat_testimoni.php?msg=Testimoni%20berhasil%20dihapus.');
exit;