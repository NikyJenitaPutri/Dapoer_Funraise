<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM carousel_photos WHERE id = ?");
$stmt->execute([$id]);
$photo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$photo) {
    $_SESSION['tk_alert'] = '❌ Foto tidak ditemukan.';
    header('Location: tentang_kami.php');
    exit;
}

$alert = '';
$originalImagePath = $photo['image_path']; // Simpan path lama untuk hapus jika diganti

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alt = trim($_POST['alt_text'] ?? '');
    $sort = (int)($_POST['sort_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Cek apakah ada upload foto baru
    $newImagePath = $photo['image_path']; // default: tetap pakai yang lama

    if (!empty($_FILES['new_photo']['name'])) {
        $file = $_FILES['new_photo'];
        $allowedTypes = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 5 * 1024 * 1024; // 5 MB
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $alert = 'Terjadi kesalahan saat upload foto.';
        } elseif (!in_array($ext, $allowedTypes)) {
            $alert = 'Format tidak didukung. Gunakan JPG, PNG, atau WebP.';
        } elseif ($file['size'] > $maxSize) {
            $alert = 'Ukuran gambar terlalu besar (maks. 5 MB).';
        } else {
            $newName = 'carousel_' . uniqid() . '.' . $ext;
            $uploadDir = '../assets/';
            $uploadPath = $uploadDir . $newName;

            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Hapus file lama (kecuali default/placeholder)
                $oldPath = '../' . $photo['image_path'];
                if (file_exists($oldPath) && $photo['image_path'] !== 'assets/placeholder.jpg') {
                    @unlink($oldPath);
                }
                $newImagePath = 'assets/' . $newName;
            } else {
                $alert = 'Gagal menyimpan file ke server.';
            }
        }
    }

    // Jika tidak ada error, simpan
    if (!$alert) {
        try {
            $stmt = $pdo->prepare("
                UPDATE carousel_photos 
                SET image_path = ?, alt_text = ?, sort_order = ?, is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([$newImagePath, $alt, $sort, $is_active, $id]);
            $_SESSION['tk_alert'] = 'Foto berhasil diperbarui.';
            header('Location: tentang_kami.php');
            exit;
        } catch (Exception $e) {
            $alert = 'Gagal memperbarui: ' . $e->getMessage();
            // Kembalikan file baru jika gagal simpan (opsional, bisa dilewati)
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Foto — Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5A46A2;
            --secondary: #B64B62;
            --accent: #F9CC22;
            --bg-light: #FFF5EE;
            --soft: #DFBEE0;
            --text-muted: #9180BB;
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

        .preview-box {
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
            .preview-box {
                width: 100%;
                max-width: 100%;
            }
        }

        .form-header, .preview-header {
            background: linear-gradient(120deg, #f5f3ff, #faf5ff);
            padding: 0.9rem 1.4rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-bottom: 1px solid #f0eaff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-header { color: var(--primary); }
        .preview-header { color: var(--secondary); justify-content: center; }

        .form-body {
            padding: 1.5rem 1.4rem 1rem;
        }

        .row-alt-sort {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 576px) {
            .row-alt-sort {
                flex-direction: column;
            }
        }

        .form-group {
            margin-bottom: 0.8rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 4px;
            font-size: 0.95rem;
            color: var(--primary);
        }

        input[type="text"],
        input[type="number"] {
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
        input[type="number"]:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--secondary);
            cursor: pointer;
        }

        .checkbox-group label {
            font-weight: 500;
            font-size: 0.95rem;
            color: var(--primary);
            cursor: pointer;
        }

        .upload-area {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 16px;
            border: 2px dashed var(--soft);
            border-radius: 10px;
            background: #faf9ff;
            cursor: pointer;
        }

        .upload-area i {
            font-size: 1.5rem;
            color: var(--text-muted);
        }

        .upload-text {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--primary);
        }

        .upload-hint {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .upload-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
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

        .action-bar {
            padding: 0.8rem 1.4rem 0.9rem;
            background: #fbf9ff;
            border-top: 1px solid #f3f0ff;
            display: flex;
            gap: 10px;
            margin-top: 0;
        }

        @media (max-width: 768px) {
            .action-bar {
                flex-direction: column;
            }
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

        /* Preview box */
        .preview-body {
            padding: 1.5rem 1.2rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.2rem;
        }


        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
        }

        .preview-alt {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--primary);
            margin-top: 4px;
        }

        .preview-status {
            font-size: 0.85rem;
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .preview-status.active {
            background: rgba(102, 187, 106, 0.15);
            color: #2e7d32;
        }

        .preview-status.inactive {
            background: rgba(244, 67, 54, 0.1);
            color: #c62828;
        }

        .preview-sort {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <!-- Form Box -->
        <div class="form-box">
            <div class="form-header">
                <i class="fas fa-edit" style="color: var(--secondary);"></i>
                Edit & Ganti Foto
            </div>

            <div class="form-body">
                <?php if ($alert): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($alert) ?>
                    </div>
                <?php endif; ?>

                <form id="editForm" method="POST" enctype="multipart/form-data">
                    <!-- Alt Text & Sort in one row -->
                    <div class="row-alt-sort">
                        <div class="form-group" style="flex: 2;">
                            <label for="alt_text">Teks Alternatif</label>
                            <input 
                                type="text" 
                                id="alt_text" 
                                name="alt_text"
                                value="<?= htmlspecialchars($photo['alt_text']) ?>"
                                placeholder="Contoh: Produk brownies coklat"
                                required
                            >
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 120px;">
                            <label for="sort_order">Urutan</label>
                            <input 
                                type="number" 
                                id="sort_order" 
                                name="sort_order"
                                value="<?= (int)$photo['sort_order'] ?>"
                                min="0"
                            >
                        </div>
                    </div>

                    <!-- Ganti Foto -->
                    <div class="form-group">
                        <label>Ganti Foto (Opsional)</label>
                        <div class="upload-area" id="uploadArea">
                            <i class="fas fa-sync-alt"></i>
                            <div class="upload-text" id="uploadFileName">Klik untuk ganti foto</div>
                            <div class="upload-hint">JPG, PNG, WebP • ≤5 MB</div>
                            <input 
                                type="file" 
                                id="new_photo" 
                                name="new_photo"
                                class="upload-input"
                                accept=".jpg,.jpeg,.png,.webp"
                            >
                        </div>
                    </div>

                    <!-- Aktif/Nonaktif — DIPINDAH KE BAWAH GANTI FOTO -->
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_active" name="is_active" <?= $photo['is_active'] ? 'checked' : '' ?>>
                            <label for="is_active">Tampilkan di halaman utama</label>
                        </div>
                    </div>
                </form>
            </div>

            <div class="action-bar">
                <a href="../pengaturan.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" form="editForm" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>

        <!-- Preview Box -->
        <div class="preview-box">
            <div class="preview-header">
                <i class="fas fa-eye"></i> Preview Foto
            </div>
            <div class="preview-body">
                <div class="preview-image-container">
                    <img 
                        src="../<?= htmlspecialchars($photo['image_path']) ?>"
                        alt="<?= htmlspecialchars($photo['alt_text']) ?>"
                        class="preview-image"
                        id="previewImage"
                        onerror="this.src='image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22250%22 height=%22250%22 viewBox=%220 0 250 250%22%3E%3Crect width=%22250%22 height=%22250%22 fill=%22%23faf9ff%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dominant-baseline=%22middle%22 font-family=%22Poppins%22 font-size=%2214%22 fill=%22%239180BB%22%3EError memuat gambar%3C/text%3E%3C/svg%3E';"
                    >
                </div>
                <div class="preview-alt" id="previewAlt"><?= htmlspecialchars($photo['alt_text']) ?: '—' ?></div>
                <div class="preview-status <?= $photo['is_active'] ? 'active' : 'inactive' ?>" id="previewStatus">
                    <?= $photo['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                </div>
                <div class="preview-sort" id="previewSort">Urutan: <?= (int)$photo['sort_order'] ?></div>
            </div>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('new_photo');
        const uploadFileName = document.getElementById('uploadFileName');
        const previewImage = document.getElementById('previewImage');
        const previewAlt = document.getElementById('previewAlt');
        const previewStatus = document.getElementById('previewStatus');
        const previewSort = document.getElementById('previewSort');
        const altInput = document.getElementById('alt_text');
        const sortInput = document.getElementById('sort_order');
        const activeCheckbox = document.getElementById('is_active');

        // Live preview untuk file upload
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                let name = file.name;
                if (name.length > 22) name = name.substring(0, 19) + '...';
                uploadFileName.textContent = name;

                const reader = new FileReader();
                reader.onload = ev => {
                    previewImage.src = ev.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                uploadFileName.textContent = 'Klik untuk ganti foto';
            }
        });

        // Live preview metadata
        altInput.addEventListener('input', e => {
            previewAlt.textContent = e.target.value || '—';
        });

        sortInput.addEventListener('input', e => {
            previewSort.textContent = `Urutan: ${e.target.value || '—'}`;
        });

        activeCheckbox.addEventListener('change', e => {
            if (e.target.checked) {
                previewStatus.textContent = 'Aktif';
                previewStatus.className = 'preview-status active';
            } else {
                previewStatus.textContent = '⏸Nonaktif';
                previewStatus.className = 'preview-status inactive';
            }
        });
    </script>
</body>
</html>