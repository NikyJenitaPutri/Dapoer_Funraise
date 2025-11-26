<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) exit;

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM contact_cards WHERE id = ?");
$stmt->execute([$id]);
$card = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$card) {
    $_SESSION['kontak_alert'] = 'Kontak tidak ditemukan.';
    header('Location: kontak.php');
    exit;
}

$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $icon = trim($_POST['icon_class'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $label = trim($_POST['label'] ?? '');
    $href = trim($_POST['href'] ?? '');
    $sort = (int)($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!$icon || !$title || !$label || !$href) {
        $alert = 'Semua field wajib diisi.';
    } else {
        try {
            $pdo->prepare("
                UPDATE contact_cards 
                SET icon_class = ?, title = ?, label = ?, href = ?, sort_order = ?, is_active = ?
                WHERE id = ?
            ")->execute([$icon, $title, $label, $href, $sort, $is_active, $id]);
            $_SESSION['kontak_alert'] = 'Kontak berhasil diperbarui.';
            header('Location: kontak.php');
            exit;
        } catch (Exception $e) {
            $alert = 'Gagal memperbarui: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kontak — Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #B64B62; --secondary: #5A46A2; --gray: #6c757d; }
        * { box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f9f7fc; padding: 20px; }
        .card { max-width: 600px; margin: 40px auto; background: white; border-radius: 16px; box-shadow: 0 6px 20px rgba(90,70,162,0.1); padding: 30px; }
        h2 { color: var(--secondary); font-weight: 700; margin-bottom: 24px; text-align: center; }
        .preview { text-align: center; margin-bottom: 20px; font-size: 2.5rem; color: var(--primary); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #2a1f3d; }
        .form-control { width: 100%; padding: 12px 16px; border-radius: 10px; border: 2px solid #e0d6eb; font-size: 1rem; }
        .form-control:focus { outline: none; border-color: var(--secondary); box-shadow: 0 0 0 3px rgba(90,70,162,0.2); }
        .checkbox-group { display: flex; align-items: center; gap: 10px; margin-top: 8px; }
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
        <h2><i class="fas fa-edit"></i> Edit Kontak</h2>

        <div class="preview">
            <i class="fa <?= htmlspecialchars($card['icon_class']) ?>"></i>
        </div>

        <?php if ($alert): ?>
            <div class="alert alert-error"><?= htmlspecialchars($alert) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="icon_class">Icon Class (Font Awesome)</label>
                <input type="text" id="icon_class" name="icon_class" class="form-control"
                       value="<?= htmlspecialchars($card['icon_class']) ?>" required>
            </div>
            <div class="form-group">
                <label for="title">Judul</label>
                <input type="text" id="title" name="title" class="form-control"
                       value="<?= htmlspecialchars($card['title']) ?>" required>
            </div>
            <div class="form-group">
                <label for="label">Label</label>
                <input type="text" id="label" name="label" class="form-control"
                       value="<?= htmlspecialchars($card['label']) ?>" required>
            </div>
            <div class="form-group">
                <label for="href">URL Lengkap</label>
                <input type="url" id="href" name="href" class="form-control"
                       value="<?= htmlspecialchars($card['href']) ?>" required>
            </div>
            <div class="form-group">
                <label for="sort_order">Urutan Tampil</label>
                <input type="number" id="sort_order" name="sort_order" class="form-control"
                       value="<?= $card['sort_order'] ?>" min="0">
            </div>
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="is_active" name="is_active" <?= $card['is_active'] ? 'checked' : '' ?>>
                    <label for="is_active" style="font-weight:500; margin:0;">Tampilkan di halaman utama</label>
                </div>
            </div>
            <div style="display: flex; gap: 12px; justify-content: center; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <a href="kontak.php" class="btn btn-gray">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
</body>
</html>