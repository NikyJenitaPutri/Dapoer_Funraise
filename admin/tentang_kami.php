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
$alertType = 'success';
if (isset($_SESSION['tk_alert'])) {
    $alert = $_SESSION['tk_alert'];
    $alertType = strpos($_SESSION['tk_alert'], 'Gagal') !== false ? 'error' : 'success';
    unset($_SESSION['tk_alert']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami — Dapoer Funraise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5A46A2;
            --secondary: #B64B62;
            --accent: #F9CC22;
            --soft: #DFBEE0;
            --text-muted: #9180BB;
            --cream: #FFF5EE;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f1e8fdff;
            color: #333;
            font-size: 15px;
        }

        .main-wrapper {
            display: flex;
            gap: 0;
            width: 100vw;
            margin: 0;
            padding: 0;
        }

        @media (max-width: 768px) {
            .main-wrapper {
                flex-direction: column;
            }
        }

        .form-box {
            flex: 1;
            background: white;
            box-shadow: 0 5px 20px rgba(90, 70, 162, 0.1);
            overflow: hidden;
            border: 1px solid #f0eaff;
            margin: 0;
            border-radius: 0;
        }

        .photos-box {
            width: 380px;
            flex-shrink: 0;
            background: white;
            box-shadow: 0 5px 20px rgba(90, 70, 162, 0.1);
            overflow: hidden;
            border: 1px solid #f0eaff;
            margin: 0;
            border-radius: 0;
        }

        @media (max-width: 768px) {
            .photos-box {
                width: 100%;
                max-width: 100%;
            }
        }

        .form-header, .photos-header {
            background: #faf5ff;
            padding: 0.9rem 1.4rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-bottom: 1px solid #f0eaff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-header { color: var(--primary); }
        .photos-header { color: var(--secondary); }

        .form-body, .photos-body {
            padding: 1.5rem 1.4rem;
        }

        .row {
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
        }

        @media (min-width: 768px) {
            .row {
                flex-direction: row;
                gap: 1.1rem;
            }
            .form-group {
                flex: 1;
            }
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.95rem;
            color: var(--primary);
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 11px 15px;
            border: 2px solid #e8e6f2;
            border-radius: 10px;
            font-size: 0.93rem;
            background: #faf9ff;
            font-family: inherit;
            transition: all 0.2s;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.1);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .alert {
            background: #fff8f8;
            color: #c0392b;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 1rem;
            border-left: 3px solid var(--secondary);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 3px solid #66bb6a;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.92rem;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s;
            font-family: inherit;
            min-height: 40px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary), #9e3e52);
            color: white;
            flex: 1;
            box-shadow: 0 2px 8px rgba(182, 75, 98, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(182, 75, 98, 0.25);
        }

        .btn-secondary, .btn-gray {
            background: linear-gradient(135deg, var(--soft), #c8a5d0);
            color: var(--primary);
            flex: 1;
        }

        .btn-secondary:hover, .btn-gray:hover {
            background: linear-gradient(135deg, #d0a8d5, #c095cb);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid #e8e6f2;
        }

        .btn-outline:hover {
            background: #faf9ff;
            border-color: var(--primary);
        }

        .btn-danger {
            background: #f9d9d9;
            color: #d32f2f;
            border: 1px solid #f1b7b7;
        }

        .btn-danger:hover {
            background: #f5baba;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
            min-height: auto;
        }

        .action-bar {
            padding: 0.8rem 1.4rem 0.9rem;
            background: #fbf9ff;
            border-top: 1px solid #f3f0ff;
            display: flex;
            gap: 10px;
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .action-bar {
                flex-direction: column;
            }
        }

        /* Photo Card — adapted from step-card */
        .photo-card {
            background: #faf9ff;
            border: 1px solid #f0eaff;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 14px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .photo-thumb {
            width: 56px;
            height: 56px;
            flex-shrink: 0;
            border-radius: 8px;
            overflow: hidden;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .photo-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-thumb .placeholder {
            color: #ccc;
            font-size: 1.2rem;
        }

        .photo-info {
            flex: 1;
            min-width: 0;
        }

        .photo-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 0.83rem;
        }

        .photo-sort { font-weight: 600; color: var(--primary); }
        .photo-status { font-weight: 500; }
        .status-active { color: #2e7d32; }
        .status-inactive { color: var(--text-muted); }

        .photo-alt {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 4px;
            font-size: 0.92rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .photo-desc {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 10px;
            line-height: 1.4;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .photo-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .no-photos {
            text-align: center;
            padding: 30px 10px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .no-photos i {
            font-size: 2rem;
            margin-bottom: 12px;
            color: #dcd6f7;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <!-- Kiri: Form Judul, Subjudul & Konten -->
        <div class="form-box">
            <div class="form-header">
                <i class="fas fa-info-circle" style="color: var(--secondary);"></i>
                Konten "Tentang Kami"
            </div>

            <div class="form-body">
                <?php if ($alert): ?>
                    <div class="alert <?= $alertType === 'success' ? 'alert-success' : '' ?>">
                        <i class="fas fa-<?= $alertType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                        <?= htmlspecialchars($alert) ?>
                    </div>
                <?php endif; ?>

                <form action="update_tentang_kami.php" method="POST">
                    <div class="row">
                        <div class="form-group">
                            <label for="title">Judul</label>
                            <input 
                                type="text" 
                                id="title" 
                                name="title"
                                value="<?= htmlspecialchars($tentang['title']) ?>"
                                required
                                placeholder="Contoh: Tentang Kami"
                            >
                        </div>

                        <div class="form-group">
                            <label for="subtitle">Subjudul</label>
                            <input 
                                type="text" 
                                id="subtitle" 
                                name="subtitle"
                                value="<?= htmlspecialchars($tentang['subtitle']) ?>"
                                required
                                placeholder="Contoh: Dapur kecil, dampak besar..."
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="content">Konten (HTML diperbolehkan)</label>
                        <textarea 
                            id="content" 
                            name="content"
                            required
                            placeholder="Masukkan konten HTML..."><?= htmlspecialchars($tentang['content']) ?></textarea>
                    </div>

                    <button type="submit" name="update_tentang" class="btn btn-primary" style="width:100%; margin-top:0.5rem;">
                        <i class="fas fa-save"></i> Simpan Konten
                    </button>
                </form>
            </div>

            <!-- Action bar: Kembali + Tambah Foto -->
            <div class="action-bar">
                <a href="../pengaturan.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="add-photo.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Foto
                </a>
            </div>
        </div>

        <!-- Kanan: Daftar Foto Carousel -->
        <div class="photos-box">
            <div class="photos-header">
                <i class="fas fa-images"></i>
                Foto Carousel (<?= count($photos) ?> item)
            </div>

            <div class="photos-body">
                <?php if ($photos): ?>
                    <?php foreach ($photos as $p): ?>
                        <div class="photo-card">
                            <div class="photo-thumb">
                                <?php if (file_exists('../' . $p['image_path'])): ?>
                                    <img src="../<?= htmlspecialchars($p['image_path']) ?>" alt="<?= htmlspecialchars($p['alt_text']) ?>">
                                <?php else: ?>
                                    <i class="fas fa-ban placeholder"></i>
                                <?php endif; ?>
                            </div>
                            <div class="photo-info">
                                <div class="photo-meta">
                                    <span class="photo-sort">Urutan: <?= (int)$p['sort_order'] ?></span>
                                    <span class="photo-status <?= $p['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                        <?= $p['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </div>
                                <div class="photo-actions">
                                    <a href="edit-photo.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($p['is_active']): ?>
                                        <form method="POST" action="toggle-photo.php" style="display:inline;"
                                              onsubmit="return confirm('Nonaktifkan foto ini? Tidak tampil di halaman utama.')">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="action" value="deactivate">
                                            <button type="submit" class="btn btn-gray btn-sm">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="toggle-photo.php" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="action" value="activate">
                                            <button type="submit" class="btn btn-gray btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" action="delete-photo.php" style="display:inline;"
                                          onsubmit="return confirm('Hapus foto ini? File akan dihapus dari server.')">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-photos">
                        <i class="fas fa-images"></i>
                        <p>Belum ada foto carousel.</p>
                        <a href="add-photo.php" class="btn btn-primary" style="margin-top:12px; width:100%;">
                            <i class="fas fa-plus"></i> Tambah Foto
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>