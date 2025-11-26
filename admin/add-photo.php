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
        $alert = 'File gambar wajib diunggah.';
    } else {
        $file = $_FILES['foto'];
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $alert = 'Format tidak didukung. Gunakan JPG, PNG, atau WebP.';
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
                    $_SESSION['tk_alert'] = 'Foto berhasil ditambahkan ke carousel.';
                    header('Location: tentang_kami.php');
                    exit;
                } catch (Exception $e) {
                    $alert = 'Gagal simpan ke database: ' . $e->getMessage();
                    @unlink($uploadPath);
                }
            } else {
                $alert = 'Gagal upload. Pastikan folder "assets/" dapat ditulis.';
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
    <title>Tambah Foto — Dapoer Funraise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5A46A2;
            --secondary: #B64B62;
            --accent: #F9CC22;
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
            border-radius: 0;
        }

        .preview-box {
            width: 380px;
            flex-shrink: 0;
            background: white;
            box-shadow: 0 5px 20px rgba(90, 70, 162, 0.1);
            overflow: hidden;
            border: 1px solid #f0eaff;
            border-radius: 0;
        }

        @media (max-width: 768px) {
            .preview-box {
                width: 100%;
                max-width: 100%;
            }
        }

        .form-header, .preview-header {
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
        .preview-header { color: var(--secondary); justify-content: center; }

        .form-body {
            padding: 1.5rem 1.4rem 1rem;
        }

        .row-alt-sort {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.2rem;
        }

        @media (max-width: 576px) {
            .row-alt-sort {
                flex-direction: column;
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

        .upload-area {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 18px 16px;
            border: 2px dashed var(--soft);
            border-radius: 10px;
            background: #faf9ff;
            cursor: pointer;
        }

        .upload-area i {
            font-size: 1.6rem;
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
            margin-top: auto;
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

        /* ✅ PREVIEW TANPA CARD DALAM CARD */
        .preview-body {
            padding: 1.5rem 1.2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .preview-image {
            max-width: 250px;
            max-height: 250px;
            object-fit: contain;
        }

        .preview-alt {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--primary);
        }

        .preview-sort {
            font-size: 0.85rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="form-box">
            <div class="form-header">
                <i class="fas fa-plus" style="color: var(--secondary);"></i>
                Tambah Foto ke Carousel
            </div>

            <div class="form-body">
                <?php if ($alert): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($alert) ?>
                    </div>
                <?php endif; ?>

                <form id="uploadForm" method="POST" enctype="multipart/form-data">
                    <!-- Baris: Alt Text + Sort Order -->
                    <div class="row-alt-sort">
                        <div class="form-group" style="flex: 2;">
                            <label for="alt_text">Teks Alternatif</label>
                            <input 
                                type="text" 
                                id="alt_text" 
                                name="alt_text"
                                value="<?= htmlspecialchars($_POST['alt_text'] ?? '') ?>"
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
                                value="<?= (int)($_POST['sort_order'] ?? 0) ?>"
                                min="0"
                            >
                        </div>
                    </div>

                    <!-- Upload area -->
                    <div class="form-group">
                        <label for="foto">Pilih Gambar (JPG/PNG/WebP)</label>
                        <div class="upload-area" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <div class="upload-text" id="uploadFileName">Klik atau seret file</div>
                            <div class="upload-hint">JPG, PNG, WebP • ≤5 MB</div>
                            <input 
                                type="file" 
                                id="foto" 
                                name="foto" 
                                class="upload-input"
                                accept="image/*"
                                required
                            >
                        </div>
                    </div>
                </form>
            </div>

            <div class="action-bar">
                <a href="tentang_kami.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" form="uploadForm" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Unggah & Simpan
                </button>
            </div>
        </div>

        <!-- ✅ Preview tanpa card dalam card -->
        <div class="preview-box">
            <div class="preview-header">
                <i class="fas fa-eye"></i> Preview
            </div>
            <div class="preview-body">
                <!-- Langsung tampilkan elemen preview -->
                <div class="preview-image-container">
                    <img 
                        src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='250' height='250' viewBox='0 0 250 250'%3E%3Crect width='250' height='250' fill='%23faf9ff'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dominant-baseline='middle' font-family='Poppins' font-size='14' fill='%239180BB'%3ETidak ada gambar%3C/text%3E%3C/svg%3E"
                        alt="Preview"
                        class="preview-image"
                        id="previewImage"
                    >
                </div>
                <div class="preview-alt" id="previewAlt">Nama Foto</div>
                <div class="preview-sort" id="previewSort">Urutan: </div>
            </div>
        </div>
    </div>

    <script>
        const fileInput = document.getElementById('foto');
        const uploadFileName = document.getElementById('uploadFileName');
        const previewImage = document.getElementById('previewImage');
        const previewAlt = document.getElementById('previewAlt');
        const previewSort = document.getElementById('previewSort');
        const altInput = document.getElementById('alt_text');
        const sortInput = document.getElementById('sort_order');

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
                uploadFileName.textContent = 'Klik atau seret file';
                previewImage.src = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='250' height='250' viewBox='0 0 250 250'%3E%3Crect width='250' height='250' fill='%23faf9ff'/%3E%3Ctext x='50%25' y='50%25' text-anchor='middle' dominant-baseline='middle' font-family='Poppins' font-size='14' fill='%239180BB'%3ETidak ada gambar%3C/text%3E%3C/svg%3E";
            }
        });

        altInput.addEventListener('input', e => {
            previewAlt.textContent = e.target.value || '—';
        });

        sortInput.addEventListener('input', e => {
            previewSort.textContent = `Urutan: ${e.target.value || '—'}`;
        });
    </script>
</body>
</html>