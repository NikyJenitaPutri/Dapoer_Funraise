<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['username'])) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($id) {
        $status = $action === 'activate' ? 1 : 0;
        $pdo->prepare("UPDATE contact_cards SET is_active = ? WHERE id = ?")->execute([$status, $id]);
    }
}
header('Location: kontak.php');
exit;