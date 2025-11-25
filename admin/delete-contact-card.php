<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['username'])) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $pdo->prepare("DELETE FROM contact_cards WHERE id = ?")->execute([$id]);
    }
}
header('Location: kontak.php');
exit;