<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $alert = '❌ File gambar wajib diunggah.';
    } else {
        $file = $_FILES['foto'];
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $alert = '❌ Format tidak didukung. Gunakan JPG, PNG, atau WebP.';
        } else {
            $newName = 'carousel_' . uniqid() . '.' . $ext;
            $uploadDir = '../assets/';
            $uploadPath = $uploadDir . $newName;

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                $alt = trim($_POST['alt_text'] ?? '');
                $sort = (int)($_POST['sort_order'] ?? 0);
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO carousel_photos (image_path, alt_text, sort_order, is_active)
                        VALUES (?, ?, ?, 1)
                    ");
                    $stmt->execute(['assets/' . $newName, $alt, $sort]);
                    $_SESSION['tk_alert'] = '✅ Foto berhasil ditambahkan ke carousel.';
                    header('Location: tentang_kami.php');
                    exit;
                } catch (Exception $e) {
                    $alert = '❌ Gagal simpan ke database: ' . $e->getMessage();
                    @unlink($uploadPath);
                }
            } else {
                $alert = '❌ Gagal upload. Pastikan folder "assets/" dapat ditulis.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Foto — Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #B64B62; --secondary: #5A46A2; --gray: #6c757d; }
        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f9f7fc; padding: 20px; }
        .card { max-width: 600px; margin: 40px auto; background: white; border-radius: 16px; box-shadow: 0 6px 20px rgba(90,70,162,0.1); padding: 30px; }
        h2 { color: var(--secondary); font-weight: 700; margin-bottom: 24px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #2a1f3d; }
        .form-control { width: 100%; padding: 12px 16px; border-radius: 10px; border: 2px solid #e0d6eb; font-size: 1rem; }
        .form-control:focus { outline: none; border-color: var(--secondary); box-shadow: 0 0 0 3px rgba(90,70,162,0.2); }
        .btn { padding: 12px 24px; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-primary { background: linear-gradient(135deg, var(--primary), #d05876); color: white; }
        .btn-gray { background: var(--gray); color: white; }
        .btn:hover { transform: translateY(-2px); }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 16px; font-weight: 600; }
        .alert-error { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>
    <div class="card">
        <h2><i class="fas fa-plus"></i> Tambah Foto Baru</h2>

        <?php if ($alert): ?>
            <div class="alert alert-error"><?= htmlspecialchars($alert) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="foto">Pilih Gambar (JPG/PNG/WebP)</label>
                <input type="file" id="foto" name="foto" accept="image/*" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="alt_text">Teks Alternatif (Alt Text)</label>
                <input type="text" id="alt_text" name="alt_text" class="form-control"
                       placeholder="Contoh: Kegiatan Expo Campus 2024" required>
            </div>
            <div class="form-group">
                <label for="sort_order">Urutan Tampil (semakin kecil, semakin awal)</label>
                <input type="number" id="sort_order" name="sort_order" class="form-control" value="0" min="0">
            </div>
            <div style="display: flex; gap: 12px; justify-content: center; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Unggah & Simpan
                </button>
                <a href="tentang_kami.php" class="btn btn-gray">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</body>
</html>