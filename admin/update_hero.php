<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['hero_alert'] = 'Metode tidak diizinkan.';
    header('Location: hero.php');
    exit;
}

$cta_button_text = trim($_POST['cta_button_text'] ?? '');
$background_path = null;

// Validasi teks CTA
if (empty($cta_button_text)) {
    $_SESSION['hero_alert'] = 'Gagal: Teks tombol CTA wajib diisi.';
    header('Location: hero.php');
    exit;
}

// Proses upload gambar (jika ada)
if (!empty($_FILES['background_image']['name'])) {
    $file = $_FILES['background_image'];
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        $_SESSION['hero_alert'] = 'Gagal: Format gambar tidak diizinkan. Gunakan JPG, PNG, atau WebP.';
        header('Location: hero.php');
        exit;
    }
    
    if ($file['size'] > 2 * 1024 * 1024) { // 2 MB
        $_SESSION['hero_alert'] = 'Gagal: Ukuran gambar maksimal 2 MB.';
        header('Location: hero.php');
        exit;
    }
    
    // Generate nama unik
    $newName = 'hero_bg_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $uploadDir = '../assets/';
    $uploadPath = $uploadDir . $newName;
    
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $background_path = 'assets/' . $newName;
        
        // Hapus gambar lama (kecuali default 'bg.jpg' atau URL eksternal)
        $stmtOld = $pdo->query("SELECT background_path FROM hero_section WHERE id = 1");
        $old = $stmtOld->fetchColumn();
        if ($old && !str_starts_with($old, 'http') && $old !== 'assets/bg.jpg') {
            $oldFullPath = '../' . $old;
            if (file_exists($oldFullPath)) {
                unlink($oldFullPath);
            }
        }
    } else {
        $_SESSION['hero_alert'] = 'Gagal: Gagal mengunggah gambar. Pastikan folder assets dapat ditulis.';
        header('Location: hero.php');
        exit;
    }
}

// Siapkan data update
$updateData = [
    'cta_button_text' => $cta_button_text,
    'background_path' => $background_path ?? null // null = tidak ganti
];

// Update ke DB
try {
    $sql = "UPDATE hero_section SET 
                cta_button_text = ?,
                updated_at = NOW()";
    
    $params = [$updateData['cta_button_text']];
    
    if ($updateData['background_path'] !== null) {
        $sql .= ", background_path = ?";
        $params[] = $updateData['background_path'];
    }
    
    $sql .= " WHERE id = 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $_SESSION['hero_alert'] = 'Berhasil: Data hero section telah diperbarui.';
} catch (Exception $e) {
    error_log('Hero update error: ' . $e->getMessage());
    $_SESSION['hero_alert'] = 'Gagal: Terjadi kesalahan sistem. Silakan coba lagi.';
}

header('Location: hero.php');
exit;