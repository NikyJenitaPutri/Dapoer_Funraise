<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) exit;

$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $icon = trim($_POST['icon_class'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $label = trim($_POST['label'] ?? '');
    $href = trim($_POST['href'] ?? '');
    $sort = (int)($_POST['sort_order'] ?? 0);
    
    if (!$icon || !$title || !$label || !$href) {
        $alert = 'Semua field wajib diisi.';
    } else {
        try {
            $pdo->prepare("
                INSERT INTO contact_cards (icon_class, title, label, href, sort_order, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
            ")->execute([$icon, $title, $label, $href, $sort]);
            $_SESSION['kontak_alert'] = 'Kontak berhasil ditambahkan.';
            header('Location: pengaturan/kontak.php');
            exit;
        } catch (Exception $e) {
            $alert = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kontak — Admin</title>
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
        .icon-preview {
            display: inline-block;
            width: 36px;
            height: 36px;
            text-align: center;
            line-height: 36px;
            font-size: 1.2rem;
            margin-right: 8px;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="card">
        <h2><i class="fas fa-plus"></i> Tambah Kontak Baru</h2>

        <?php if ($alert): ?>
            <div class="alert alert-error"><?= htmlspecialchars($alert) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="icon_class">Icon Class (Font Awesome)</label>
                <input type="text" id="icon_class" name="icon_class" class="form-control"
                       placeholder="Contoh: fa-brands fa-whatsapp"
                       value="fa-brands fa-whatsapp" required>
                <small style="display:block; margin-top:6px; color:#666">
                    Gunakan class lengkap, misal: <code>fa-brands fa-instagram</code>, <code>fa-solid fa-envelope</code>
                </small>
            </div>
            <div class="form-group">
                <label for="title">Judul (Nama Platform)</label>
                <input type="text" id="title" name="title" class="form-control"
                       placeholder="Contoh: WhatsApp" required>
            </div>
            <div class="form-group">
                <label for="label">Label (Teks Tautan)</label>
                <input type="text" id="label" name="label" class="form-control"
                       placeholder="Contoh: Yunisa" required>
            </div>
            <div class="form-group">
                <label for="href">URL Lengkap</label>
                <input type="url" id="href" name="href" class="form-control"
                       placeholder="https://..." required>
            </div>
            <div class="form-group">
                <label for="sort_order">Urutan Tampil (semakin kecil, semakin awal)</label>
                <input type="number" id="sort_order" name="sort_order" class="form-control" value="0" min="0">
            </div>
            <div style="display: flex; gap: 12px; justify-content: center; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Kontak
                </button>
                <a href="kontak.php" class="btn btn-gray">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>

    <script>
    document.getElementById('icon_class').addEventListener('input', function() {
        const val = this.value.trim();
        if (val) {
            const preview = document.querySelector('.icon-preview') || document.createElement('span');
            preview.className = 'icon-preview';
            preview.innerHTML = `<i class="fa ${val}"></i>`;
            if (!this.parentNode.querySelector('.icon-preview')) {
                this.parentNode.insertBefore(preview, this.nextSibling);
            }
        }
    });
    </script>
</body>
</html>