<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = '../assets/hero/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $oldPath = '';
    $stmt = $pdo->query("SELECT background_path FROM hero_section WHERE id = 1");
    $row = $stmt->fetch();
    $oldPath = $row ? $row['background_path'] : 'assets/bg.jpg';

    // Hapus file lama (kecuali default)
    if ($oldPath !== 'assets/bg.jpg' && file_exists('../' . $oldPath)) {
        unlink('../' . $oldPath);
    }

    if (!empty($_FILES['background_image']['name'])) {
        $file = $_FILES['background_image'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024;

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['hero_alert'] = 'Gagal: Error saat upload.';
        } elseif (!in_array($file['type'], $allowed)) {
            $_SESSION['hero_alert'] = 'Gagal: Format tidak didukung. Gunakan JPG/PNG/WebP.';
        } elseif ($file['size'] > $maxSize) {
            $_SESSION['hero_alert'] = 'Gagal: Ukuran maksimal 2 MB.';
        } else {
            $ext = match($file['type']) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => 'jpg'
            };
            $newName = 'hero_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $ext;
            $target = $uploadDir . $newName;

            if (move_uploaded_file($file['tmp_name'], $target)) {
                $newPath = 'assets/hero/' . $newName;
                $stmt = $pdo->prepare("UPDATE hero_section SET background_path = ? WHERE id = 1");
                $stmt->execute([$newPath]);
                $_SESSION['hero_alert'] = 'Berhasil: Gambar latar diperbarui!';
            } else {
                $_SESSION['hero_alert'] = 'Gagal: Tidak bisa simpan ke server.';
            }
        }
    } else {
        $_SESSION['hero_alert'] = 'Gagal: Harap pilih gambar.';
    }
}

header('Location: hero.php');
exit;