<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

// === DATA TENTANG KAMI ===
$stmtTentang = $pdo->query("SELECT title, subtitle, content FROM tentang_kami_section WHERE id = 1");
$tentang = $stmtTentang->fetch(PDO::FETCH_ASSOC);
if (!$tentang) {
    $pdo->exec("
        INSERT INTO tentang_kami_section (title, subtitle, content) VALUES (
            'Tentang Kami',
            'Dapur kecil, dampak besar untuk pendidikan',
            'Dapoer Funraise adalah wujud kepedulian alumni MAN 2 Samarinda dalam mendukung <strong>Expo Campus MAN 2 Samarinda</strong> — acara tahunan untuk memperkenalkan perguruan tinggi kepada siswa. Seluruh keuntungan penjualan cemilan digunakan untuk kebutuhan acara: konsumsi, dekorasi, dan logistik. Kami percaya: bisnis kecil bisa berdampak besar!'
        )
    ");
    $tentang = [
        'title'    => 'Tentang Kami',
        'subtitle' => 'Dapur kecil, dampak besar untuk pendidikan',
        'content'  => 'Dapoer Funraise adalah wujud kepedulian alumni MAN 2 Samarinda dalam mendukung <strong>Expo Campus MAN 2 Samarinda</strong> — acara tahunan untuk memperkenalkan perguruan tinggi kepada siswa. Seluruh keuntungan penjualan cemilan digunakan untuk kebutuhan acara: konsumsi, dekorasi, dan logistik. Kami percaya: bisnis kecil bisa berdampak besar!'
    ];
}

// === DAFTAR FOTO (AKTIF/NON) ===
$stmtPhotos = $pdo->query("
    SELECT * FROM carousel_photos 
    ORDER BY sort_order ASC, id ASC
");
$photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

$alert = '';
if (isset($_SESSION['tk_alert'])) {
    $alert = $_SESSION['tk_alert'];
    unset($_SESSION['tk_alert']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami — Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #B64B62;
            --secondary: #5A46A2;
            --accent: #F9CC22;
            --cream: #FFF5EE;
            --dark: #2a1f3d;
            --gray-btn: #6c757d;
            --font-main: 'Poppins', sans-serif;
            --transition: all 0.3s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-main);
            background-color: #f9f7fc;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(90, 70, 162, 0.1);
            overflow: hidden;
        }
        header {
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-title { font-size: 1.5rem; font-weight: 600; }
        .header-title i { margin-right: 8px; }
        .nav-actions a {
            color: white;
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
        }
        .nav-actions a:hover { background: rgba(255,255,255,0.3); }
        .content { padding: 30px; }
        .section-heading {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--dark);
        }
        .alert {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: left;
        }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .form-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }
        .form-col {
            flex: 1;
            min-width: 300px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 1.05rem;
        }
        .form-control, .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 2px solid #e0d6eb;
            font-size: 1rem;
            transition: var(--transition);
        }
        .form-control:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.2);
        }
        .form-textarea {
            min-height: 180px;
            resize: vertical;
        }
        .photos-heading {
            font-size: 1.5rem;
            margin: 30px 0 16px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .photos-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 16px;
        }
        .photo-card {
            background: white;
            border: 2px solid #e6e1f0;
            border-radius: 14px;
            overflow: hidden;
            transition: var(--transition);
        }
        .photo-card:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            transform: translateY(-4px);
        }
        .photo-preview {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-info {
            padding: 16px;
        }
        .photo-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .photo-sort {
            font-weight: 700;
            color: var(--primary);
            font-size: 0.95rem;
        }
        .photo-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #b91c1c; }
        .photo-alt {
            font-size: 0.9rem;
            color: #555;
            margin: 4px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .photo-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.92rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
            text-align: center;
        }
        .btn-sm { padding: 6px 12px; font-size: 0.875rem; }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #d05876);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(182, 75, 98, 0.3);
        }
        .btn-gray {
            background: var(--gray-btn);
            color: white;
        }
        .btn-gray:hover { background: #5a6268; }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .no-photos {
            text-align: center;
            padding: 40px 20px;
            color: #777;
            background: var(--cream);
            border-radius: 14px;
        }
        .no-photos i { font-size: 2.5rem; margin-bottom: 16px; color: #ccc; }
        @media (max-width: 768px) {
            .form-row { flex-direction: column; }
            .photos-list { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-title">
                <i class="fas fa-info-circle"></i> Tentang Kami
            </div>
            <div class="nav-actions">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </div>
        </header>

        <div class="content">
            <h2 class="section-heading">Kelola Konten "Tentang Kami"</h2>

            <?php if ($alert): ?>
                <div class="alert <?= strpos($alert, 'Gagal') !== false ? 'alert-error' : 'alert-success' ?>">
                    <?= htmlspecialchars($alert) ?>
                </div>
            <?php endif; ?>

            <form action="update_tentang_kami.php" method="POST">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="title">Judul</label>
                            <input type="text" id="title" name="title" class="form-control"
                                   value="<?= htmlspecialchars($tentang['title']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="subtitle">Subjudul</label>
                            <input type="text" id="subtitle" name="subtitle" class="form-control"
                                   value="<?= htmlspecialchars($tentang['subtitle']) ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="content">Konten (HTML diperbolehkan)</label>
                            <textarea id="content" name="content" class="form-textarea" required><?= htmlspecialchars($tentang['content']) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="update_tentang" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Konten
                    </button>
                </div>
            </form>

            <h3 class="photos-heading">
                <i class="fas fa-images"></i> Foto Carousel (<?= count($photos) ?> foto)
            </h3>

            <?php if ($photos): ?>
                <div class="photos-list">
                    <?php foreach ($photos as $p): ?>
                        <div class="photo-card">
                            <div class="photo-preview">
                                <?php if (file_exists('../' . $p['image_path'])): ?>
                                    <img src="../<?= htmlspecialchars($p['image_path']) ?>" 
                                         alt="<?= htmlspecialchars($p['alt_text']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-ban" style="font-size: 2rem; color: #ccc;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="photo-info">
                                <div class="photo-meta">
                                    <span class="photo-sort">Urutan: <?= $p['sort_order'] ?></span>
                                    <span class="photo-status <?= $p['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                        <?= $p['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </div>
                                <div class="photo-alt">
                                    <strong>Alt:</strong> <?= htmlspecialchars($p['alt_text'] ?: '-') ?>
                                </div>
                                <div class="photo-actions">
                                    <a href="edit-photo.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if ($p['is_active']): ?>
                                        <form method="POST" action="toggle-photo.php" style="display: inline;" 
                                              onsubmit="return confirm('Nonaktifkan foto ini? Tidak tampil di halaman utama.')">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="action" value="deactivate">
                                            <button type="submit" class="btn btn-warning btn-sm">
                                                <i class="fas fa-eye-slash"></i> Nonaktifkan
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="toggle-photo.php" style="display: inline;">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="action" value="activate">
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-eye"></i> Aktifkan
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" action="delete-photo.php" style="display: inline;"
                                          onsubmit="return confirm('Hapus foto ini? File akan dihapus dari server.')">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-photos">
                    <i class="fas fa-images"></i>
                    <p>Belum ada foto. Tambahkan foto pertama untuk carousel.</p>
                </div>
            <?php endif; ?>

            <div style="margin-top: 30px; text-align: center;">
                <a href="add-photo.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Foto Baru
                </a>
                <a href="index.php" class="btn btn-gray">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>