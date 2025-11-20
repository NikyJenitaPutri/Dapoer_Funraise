<?php
require 'config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id || !ctype_digit($id)) {
    http_response_code(400);
    die("<!DOCTYPE html><html><head><meta charset='utf-8'><title>Error</title></head><body style='font-family:sans-serif;text-align:center;padding:2rem;background:#FFF5EE;height:100vh;display:flex;flex-direction:column;justify-content:center;align-items:center;'><div><h2><i class='fas fa-exclamation-triangle' style='color:#B64B62'></i> Produk tidak ditemukan</h2><a href='./admin/index.php' style='display:inline-block;margin-top:1.2rem;padding:0.6rem 1.6rem;background:#5A46A2;color:white;text-decoration:none;border-radius:8px;font-weight:600;'>Kembali ke Dashboard</a></div></body></html>");
}
$id = (int)$id;

$stmt = $pdo->prepare("SELECT * FROM produk WHERE ID = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    http_response_code(404);
    die("<!DOCTYPE html><html><head><meta charset='utf-8'><title>Error</title></head><body style='font-family:sans-serif;text-align:center;padding:2rem;background:#FFF5EE;height:100vh;display:flex;flex-direction:column;justify-content:center;align-items:center;'><div><h2><i class='fas fa-search' style='color:#5A46A2'></i> Produk tidak ditemukan</h2><a href='./admin/index.php' style='display:inline-block;margin-top:1.2rem;padding:0.6rem 1.6rem;background:#5A46A2;color:white;text-decoration:none;border-radius:8px;font-weight:600;'>Kembali ke Dashboard</a></div></body></html>");
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $harga = trim($_POST['harga'] ?? '');
    $varian = trim($_POST['varian'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    if ($nama === '' || $harga === '') {
        $msg = "Nama dan harga wajib diisi!";
    } elseif (!is_numeric($harga) || $harga <= 0) {
        $msg = "Harga harus berupa angka positif!";
    } else {
        $foto_name = $p['Foto_Produk'];

        if (!empty($_FILES['foto']['name']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
            $original = basename($_FILES['foto']['name']);
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed_ext)) {
                $msg = "Format gambar tidak diizinkan. Gunakan JPG, PNG, atau WebP.";
            } else {
                $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($original, PATHINFO_FILENAME));
                $foto_name_new = $safe_name . '_' . time() . '.' . $ext;
                $upload_path = __DIR__ . '/uploads/' . $foto_name_new;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                    if (!empty($p['Foto_Produk']) && $p['Foto_Produk'] !== $foto_name_new) {
                        $old_path = __DIR__ . '/uploads/' . $p['Foto_Produk'];
                        if (file_exists($old_path)) {
                            unlink($old_path);
                        }
                    }
                    $foto_name = $foto_name_new;
                } else {
                    $msg = "Gagal mengunggah gambar. Pastikan folder uploads/ dapat ditulis.";
                }
            }
        }

        if ($msg === '') {
            try {
                $stmt = $pdo->prepare("UPDATE produk SET Nama = ?, Harga = ?, Varian = ?, Deskripsi_Produk = ?, Foto_Produk = ? WHERE ID = ?");
                $stmt->execute([
                    $nama,
                    (float)$harga,
                    $varian === '' ? null : $varian,
                    $deskripsi,
                    $foto_name,
                    $id
                ]);
                header('Location: ./admin/daftar_produk.php?msg=' . urlencode('Produk berhasil diperbarui'));
                exit;
            } catch (PDOException $e) {
                $msg = 'Gagal memperbarui produk: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Edit Produk – Dapoer Funraise</title>
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
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, var(--bg-light) 0%, #f9f5ff 100%);
            color: #333;
            display: flex;
            flex-direction: column;
            font-size: 15px;
        }

        .app-content {
            flex: 1;
            display: flex;
            padding: 1.5rem;
            overflow: auto;
        }

        .form-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(90, 70, 162, 0.18);
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0,0,0,0.03);
            min-height: calc(100vh - 3rem);
        }

        .card-header {
            background: linear-gradient(120deg, #f5f3ff, #faf5ff);
            color: var(--primary);
            padding: 1.2rem 1.8rem;
            font-size: 1.3rem;
            font-weight: 700;
            border-bottom: 1px solid #f0eaff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            flex: 1;
            padding: 2rem;
        }

        .alert {
            background: #fff8f8;
            color: #c0392b;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--secondary);
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(182, 75, 98, 0.1);
        }

        .row-1 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            align-items: start;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary);
            font-size: 1.05rem;
        }
        .required { color: var(--secondary); }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e8e6f2;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.25s;
            background: #faf9ff;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(90, 70, 162, 0.15);
            background: white;
        }

        .form-group textarea {
            min-height: 180px;
            resize: vertical;
        }

        .help {
            display: block;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 5px;
            font-style: italic;
        }

        .file-control { 
            position: relative; 
        }
        .file-input { 
            position: absolute; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            opacity: 0; 
            cursor: pointer; 
        }
        .file-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fbf9ff;
            border: 2px dashed var(--soft);
            border-radius: 12px;
            padding: 14px;
            cursor: pointer;
            transition: all 0.2s;
            min-height: 56px;
        }
        .file-meta:hover {
            background: #f5f0ff;
            border-color: var(--primary);
        }
        .file-name {
            flex: 1;
            font-size: 0.95rem;
            color: #444;
            font-weight: 500;
            word-break: break-word;
        }

        .preview-box {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            justify-content: flex-start;
        }
        .preview-label {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.05rem;
            margin-bottom: 8px;
        }
        .preview-img-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 220px;
            background: #fcfbff;
            border: 2px solid #f0eaff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .preview-img {
            max-width: 100%;
            max-height: 200px;
            height: auto;
            object-fit: contain;
            display: block;
        }
        .no-preview {
            color: var(--text-muted);
            font-style: italic;
            text-align: center;
            padding: 1.5rem;
            font-size: 0.95rem;
        }

        .form-footer {
            margin-top: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid #eee8ff;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.3s;
            font-family: inherit;
            min-height: 48px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary), #9e3e52);
            color: white;
            flex: 1;
            box-shadow: 0 4px 12px rgba(182, 75, 98, 0.25);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(182, 75, 98, 0.35);
        }
        .btn-secondary {
            background: linear-gradient(135deg, var(--soft), #c8a5d0);
            color: var(--primary);
            flex: 1;
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #d0a8d5, #c095cb);
        }

        @media (max-width: 767px) {
            body { font-size: 14px; }
            .app-content { padding: 1rem; }
            .form-card { min-height: auto; }
            .row-1,
            .row-2 {
                grid-template-columns: 1fr;
            }
            .form-group input,
            .form-group textarea {
                font-size: 0.95rem;
                padding: 12px 14px;
            }
            .card-body { padding: 1.5rem; }
            .form-footer { flex-direction: column; }
            .btn { font-size: 0.95rem; padding: 12px; }
            .preview-img-container { min-height: 180px; }
            .preview-img { max-height: 160px; }
        }
    </style>
</head>
<body>
    <main class="app-content">
        <div class="form-card">
            <div class="card-header">
                <i class="fas fa-edit" style="color: var(--secondary);"></i>
                Edit Detail Produk
            </div>
            <div class="card-body">
                <?php if ($msg): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row-1">
                        <div class="form-group">
                            <label for="nama">Nama Produk <span class="required">*</span></label>
                            <input id="nama" type="text" name="nama" value="<?= htmlspecialchars($p['Nama'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="harga">Harga (Rp) <span class="required">*</span></label>
                            <input id="harga" type="number" step="1" min="1" name="harga" value="<?= (int)($p['Harga'] ?? 0) ?>" required>
                            <small class="help">Contoh: 125000</small>
                        </div>
                        <div class="form-group">
                            <label for="varian">Varian</label>
                            <input id="varian" type="text" name="varian" value="<?= htmlspecialchars($p['Varian'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                   placeholder="S,M,L atau Nude,Mocha">
                            <small class="help">Pisahkan dengan koma</small>
                        </div>
                    </div>

                    <div class="row-2">
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" rows="8"><?= htmlspecialchars($p['Deskripsi_Produk'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="foto">Unggah Gambar</label>
                            <div class="file-control">
                                <input id="foto" class="file-input" type="file" name="foto" accept="image/*">
                                <div class="file-meta">
                                    <span class="file-name"><?= htmlspecialchars($p['Foto_Produk'] ?? 'Pilih file...', ENT_QUOTES, 'UTF-8') ?></span>
                                    <button type="button" class="btn btn-secondary" style="padding:8px 14px;font-size:0.85rem;flex:none;min-height:auto;" onclick="resetFile()">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="help">Maks. 5MB (JPG/PNG/WebP)</small>
                        </div>

                        <div class="form-group preview-box">
                            <label class="preview-label">Gambar</label>
                            <div class="preview-img-container">
                                <?php 
                                $foto = $p['Foto_Produk'] ?? '';
                                if (!empty($foto) && file_exists(__DIR__ . '/uploads/' . $foto)): 
                                ?>
                                    <img id="previewImg" class="preview-img" src="uploads/<?= htmlspecialchars($foto, ENT_QUOTES, 'UTF-8') ?>" alt="Produk">
                                <?php else: ?>
                                    <div class="no-preview">
                                        <i class="fas fa-image fa-3x" style="color:#ddd;margin-bottom:0.5rem;"></i><br>
                                        Belum ada gambar
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="./admin/daftar_produk.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        function resetFile() {
            const input = document.getElementById('foto');
            const fileName = document.querySelector('.file-name');
            const preview = document.getElementById('previewImg');
            input.value = '';
            
            const defaultName = '<?= htmlspecialchars($p['Foto_Produk'] ?? "Pilih file...", ENT_QUOTES, "UTF-8") ?>';
            fileName.textContent = defaultName;

            <?php if (!empty($p['Foto_Produk']) && file_exists(__DIR__ . '/uploads/' . $p['Foto_Produk'])): ?>
                if (preview) {
                    preview.src = 'uploads/<?= htmlspecialchars($p['Foto_Produk'], ENT_QUOTES, 'UTF-8') ?>';
                    preview.parentNode.querySelector('.no-preview')?.remove();
                    preview.style.display = 'block';
                }
            <?php else: ?>
                if (preview) {
                    preview.style.display = 'none';
                    let container = preview.closest('.preview-img-container');
                    if (!container.querySelector('.no-preview')) {
                        const fallback = document.createElement('div');
                        fallback.className = 'no-preview';
                        fallback.innerHTML = `<i class="fas fa-image fa-3x" style="color:#ddd;margin-bottom:0.5rem;"></i><br>Belum ada gambar`;
                        container.appendChild(fallback);
                    }
                }
            <?php endif; ?>
        }

        document.getElementById('foto')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('previewImg');
            const fileName = document.querySelector('.file-name');
            const container = document.querySelector('.preview-img-container');
            const fallback = container.querySelector('.no-preview');

            if (!file) {
                fileName.textContent = '<?= htmlspecialchars($p['Foto_Produk'] ?? "Pilih file...", ENT_QUOTES, "UTF-8") ?>';
                resetFile();
                return;
            }

            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                fileName.textContent = '❌ Format tidak didukung';
                setTimeout(resetFile, 2000);
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                fileName.textContent = '❌ File >5MB';
                setTimeout(resetFile, 2000);
                return;
            }

            fileName.textContent = file.name;

            if (fallback) fallback.remove();

            const reader = new FileReader();
            reader.onload = () => {
                if (preview) {
                    preview.src = reader.result;
                    preview.style.display = 'block';
                } else {
                    const img = document.createElement('img');
                    img.id = 'previewImg';
                    img.className = 'preview-img';
                    img.src = reader.result;
                    container.innerHTML = '';
                    container.appendChild(img);
                }
            };
            reader.onerror = () => {
                fileName.textContent = 'Gagal membaca file';
                setTimeout(resetFile, 2000);
            };
            reader.readAsDataURL(file);
        });
    </script>
</body>
</html>