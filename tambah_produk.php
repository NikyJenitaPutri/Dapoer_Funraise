<?php
require 'config.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    exit('Internal Server Error: Database connection not established.');
}

session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$namaVal = '';
$hargaVal = '';
$varianVal = '';
$deskripsiVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $harga = trim($_POST['harga'] ?? '');
    $varian = trim($_POST['varian'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    $namaVal = $nama;
    $hargaVal = $harga;
    $varianVal = $varian;
    $deskripsiVal = $deskripsi;

    if (!$nama || $harga === '' || !is_numeric($harga) || $harga <= 0) {
        $msg = "Nama dan harga wajib diisi. Harga harus angka positif!";
    } else {
        $varianArray = array_filter(array_map('trim', explode(',', $varian)));
        if ($varian && empty($varianArray)) {
            $msg = "Varian tidak valid. Pisahkan dengan koma, contoh: S,M,L";
        } else {
            $foto_name = null;
            if (!empty($_FILES['foto']['name']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
                $original = basename($_FILES['foto']['name']);
                $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
                $safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($original, PATHINFO_FILENAME));
                $foto_name = $safe . '_' . time();
                if ($ext) $foto_name .= '.' . $ext;

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['foto']['tmp_name']);
                finfo_close($finfo);
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];

                if (!in_array($mime, $allowed)) {
                    $msg = 'File tidak valid. Gunakan: JPG, PNG, atau WEBP.';
                } elseif ($_FILES['foto']['size'] > 3 * 1024 * 1024) {
                    $msg = 'Ukuran file terlalu besar. Maksimal 3MB.';
                } else {
                    $upload_dir = __DIR__ . "/uploads/";
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $foto_name)) {
                        $msg = 'Gagal menyimpan file. Periksa izin folder uploads.';
                    }
                }
            }

            if (!$msg) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO produk (Nama, Harga, Varian, Deskripsi_Produk, Foto_Produk) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        substr($nama, 0, 100),
                        (float)$harga,
                        !empty($varianArray) ? implode(', ', $varianArray) : null,
                        substr($deskripsi, 0, 2000),
                        $foto_name
                    ]);
                    header('Location: ./admin/daftar_produk.php?msg=' . urlencode('Produk berhasil ditambahkan!'));
                    exit;
                } catch (PDOException $e) {
                    error_log("Insert Produk Error: " . $e->getMessage());
                    $msg = 'Gagal menyimpan produk. Silakan coba lagi.';
                }
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
    <title>Tambah Produk — Dapoer Funraise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5A46A2;
            --secondary: #B64B62;
            --accent: #F9CC22;
            --bg-light: #FFF5EE;
            --soft: #DFBEE0;
            --text-muted: #9180BB;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-light) 0%, #f9f5ff 100%);
            color: #333;
            padding: 1.5rem;
            font-size: 15px;
            min-height: 100vh;
        }

        .main-wrapper {
            display: flex;
            gap: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
            min-height: calc(100vh - 3rem);
        }

        .form-box {
            flex: 1;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(90, 70, 162, 0.18);
            overflow: hidden;
            border: 1px solid #f0eaff;
            display: flex;
            flex-direction: column;
        }

        .form-header {
            background: linear-gradient(120deg, #f5f3ff, #faf5ff);
            color: var(--primary);
            padding: 1.2rem 1.8rem;
            font-size: 1.3rem;
            font-weight: 600;
            border-bottom: 1px solid #f0eaff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-body {
            padding: 2rem;
            flex: 1;
            overflow-y: auto;
        }

        .row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .row { grid-template-columns: 1fr; }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1.05rem;
            color: var(--primary);
        }
        .required { color: var(--secondary); }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e8e6f2;
            border-radius: 12px;
            font-size: 1rem;
            background: #faf9ff;
            font-family: inherit;
            transition: all 0.2s;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(90, 70, 162, 0.15);
        }

        textarea { min-height: 120px; resize: vertical; }

        .help {
            display: block;
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 5px;
            font-style: italic;
        }

        /* Variant */
        .variant-input-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .variant-input-group input {
            flex: 1;
            min-width: 180px;
        }
        .variant-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        .variant-tag {
            background: var(--soft);
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Action bar */
        .action-bar {
            padding: 1.2rem 1.8rem;
            background: #fbf9ff;
            border-top: 1px solid #f3f0ff;
            display: flex;
            gap: 12px;
        }

        /* Preview */
        .preview-box {
            width: 320px;
            flex-shrink: 0;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(90, 70, 162, 0.15);
            border: 1px solid #f0eaff;
            display: flex;
            flex-direction: column;
        }

        .preview-header {
            background: #faf5ff;
            color: var(--secondary);
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
            border-bottom: 1px solid #f3f0ff;
        }

        .preview-body {
            padding: 1.5rem;
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .preview-img-container {
            margin-bottom: 1.2rem;
        }

        .preview-img-placeholder,
        .preview-img {
            width: 180px;
            height: 180px;
            margin: 0 auto;
            border-radius: 12px;
            background: #fbf9ff;
        }
        .preview-img-placeholder {
            border: 2px dashed var(--soft);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 2rem;
        }
        .preview-img {
            object-fit: contain;
            display: none;
        }

        .preview-text h3 {
            color: var(--primary);
            margin: 10px 0 6px;
            font-size: 1.2rem;
            font-weight: 600;
        }
        .preview-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--secondary);
        }
        .preview-variant {
            background: var(--soft);
            color: var(--primary);
            padding: 5px 12px;
            border-radius: 20px;
            display: inline-block;
            font-size: 0.85rem;
            font-weight: 600;
            margin: 8px 0;
            min-height: 1.8rem;
        }
        .preview-text p {
            font-size: 0.95rem;
            color: #555;
            margin-top: 10px;
            line-height: 1.5;
            max-height: 75px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        /* Tombol */
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
            transition: all 0.25s;
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

        /* Alert */
        .alert {
            background: #fff8f8;
            color: #c0392b;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--secondary);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 8px rgba(182, 75, 98, 0.1);
        }

        /* Mobile */
        @media (max-width: 768px) {
            body { padding: 1rem; }
            .main-wrapper {
                flex-direction: column;
                min-height: auto;
            }
            .preview-box { width: 100%; max-width: 400px; margin: 0 auto; }
            .preview-img-placeholder,
            .preview-img { width: 150px; height: 150px; }
            .form-body { padding: 1.5rem; }
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="form-box">
            <div class="form-header">
                <i class="fas fa-plus-circle" style="color: var(--secondary);"></i>
                Tambah Produk
            </div>
            <div class="form-body">
                <?php if ($msg): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($msg) ?>
                    </div>
                <?php endif; ?>

                <form id="addForm" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="form-group">
                            <label for="nama">Nama Produk <span class="required">*</span></label>
                            <input 
                                id="nama" 
                                type="text" 
                                name="nama" 
                                value="<?= htmlspecialchars($namaVal) ?>" 
                                placeholder="Contoh: Jus Buah"
                                maxlength="100"
                            >
                        </div>

                        <div class="form-group">
                            <label for="harga">Harga (Rp) <span class="required">*</span></label>
                            <input 
                                id="harga" 
                                type="number" 
                                name="harga" 
                                value="<?= htmlspecialchars($hargaVal) ?>" 
                                placeholder="Contoh: 45000"
                            >
                            <small class="help">Tanpa titik/koma</small>
                        </div>

                        <div class="form-group">
                            <label for="varian">Varian (Opsional)</label>
                            <div class="variant-input-group">
                                <input 
                                    id="varian" 
                                    type="text" 
                                    name="varian" 
                                    value="<?= htmlspecialchars($varianVal) ?>"
                                    placeholder="S,M,L"
                                    maxlength="255"
                                >
                                <button 
                                    type="button" 
                                    class="btn btn-secondary" 
                                    id="btnParseVariant"
                                    style="padding: 8px 16px; font-size: 0.9rem; white-space: nowrap; flex: none;"
                                >
                                    <i class="fas fa-magic"></i> Pisahkan
                                </button>
                            </div>
                            <small class="help">Pisahkan dengan koma</small>
                            <div class="variant-tags" id="variantPreview">
                                <span class="variant-tag"><i class="fas fa-tag"></i> Belum ada varian</span>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="grid-template-columns: 2fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea 
                                id="deskripsi" 
                                name="deskripsi"
                                placeholder="Ceritakan keunggulan produk..."
                                maxlength="2000"
                            ><?= htmlspecialchars($deskripsiVal) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="foto">Foto Produk</label>
                            <input 
                                id="foto" 
                                type="file" 
                                name="foto" 
                                accept="image/jpeg,image/png,image/webp"
                            >
                            <small class="help">JPG/PNG/WEBP (≤3MB)</small>
                        </div>
                    </div>
                </form>
            </div>

            <div class="action-bar">
                <a href="./admin/daftar_produk.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
                <button type="submit" form="addForm" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </div>

        <div class="preview-box">
            <div class="preview-header">
                <i class="fas fa-eye"></i> Preview
            </div>
            <div class="preview-body">
                <div class="preview-img-container">
                    <div class="preview-img-placeholder">
                        <i class="fas fa-image"></i>
                    </div>
                    <img id="livePreviewImg" class="preview-img" src="" alt="Preview">
                </div>
                <div class="preview-text">
                    <h3 id="liveNama">Nama Produk</h3>
                    <div class="preview-price" id="liveHarga">Rp Harga</div>
                    <div class="preview-variant" id="liveVarian">—</div>
                    <p id="liveDeskripsi">Deskripsi produk akan muncul...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function formatRupiah(num) {
            const n = parseFloat(num) || 0;
            return 'Rp ' + n.toLocaleString('id-ID');
        }

        const updateVarianText = (variants) => {
            return variants.length ? variants.join(', ') : '—';
        };

        const namaInput = document.getElementById('nama');
        const hargaInput = document.getElementById('harga');
        const varianInput = document.getElementById('varian');
        const deskripsiInput = document.getElementById('deskripsi');
        const fotoInput = document.getElementById('foto');
        const liveNama = document.getElementById('liveNama');
        const liveHarga = document.getElementById('liveHarga');
        const liveVarian = document.getElementById('liveVarian');
        const liveDeskripsi = document.getElementById('liveDeskripsi');
        const livePreviewImg = document.getElementById('livePreviewImg');
        const placeholder = document.querySelector('.preview-img-placeholder');

        liveNama.textContent = namaInput.value || 'Nama Produk';
        liveHarga.textContent = formatRupiah(hargaInput.value);
        liveDeskripsi.textContent = deskripsiInput.value || 'Deskripsi produk akan muncul...';

        (() => {
            const vars = varianInput.value.split(',').map(v => v.trim()).filter(v => v);
            liveVarian.textContent = updateVarianText(vars);
        })();

        namaInput.addEventListener('input', () => liveNama.textContent = namaInput.value || 'Nama Produk');
        hargaInput.addEventListener('input', () => liveHarga.textContent = formatRupiah(hargaInput.value));
        deskripsiInput.addEventListener('input', () => liveDeskripsi.textContent = deskripsiInput.value || 'Deskripsi produk akan muncul...');

        varianInput.addEventListener('input', () => {
            const vars = varianInput.value.split(',').map(v => v.trim()).filter(v => v);
            liveVarian.textContent = updateVarianText(vars);
            updateVariantPreview();
        });

        fotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = ev => {
                    livePreviewImg.src = ev.target.result;
                    livePreviewImg.style.display = 'block';
                    placeholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                livePreviewImg.style.display = 'none';
                placeholder.style.display = 'flex';
            }
        });

        document.getElementById('btnParseVariant').addEventListener('click', () => {
            let val = varianInput.value.trim()
                .replace(/\s*,\s*/g, ',')
                .replace(/,+/g, ',')
                .replace(/^,|,$/g, '');
            varianInput.value = val;
            const variants = val.split(',').map(v => v.trim()).filter(v => v);
            liveVarian.textContent = updateVarianText(variants);
            updateVariantPreview();
        });

        function updateVariantPreview() {
            const container = document.getElementById('variantPreview');
            const variants = varianInput.value
                ? varianInput.value.split(',').map(v => v.trim()).filter(v => v)
                : [];
            container.innerHTML = variants.length
                ? variants.map(v => `<span class="variant-tag"><i class="fas fa-tag"></i> ${v}</span>`).join('')
                : '<span class="variant-tag"><i class="fas fa-tag"></i> Belum ada varian</span>';
        }

        updateVariantPreview();
    </script>
</body>
</html>