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
    --border: #f0eaff;
    --shadow: 0 4px 12px rgba(90, 70, 162, 0.1);
    
    --fs-xs: 0.8125rem;
    --fs-sm: 0.875rem;
    --fs-md: 0.9375rem;
    --fs-lg: 1rem;
    --gap-xs: 0.4rem;
    --gap-sm: 0.6rem;
    --gap-md: 0.8rem;
    --gap-lg: 1rem;
    --radius: 8px;
    --btn-h: 38px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
html, body {
    width: 100%;
    min-width: 100vw;
    overflow-x: hidden; /* Hanya jika benar-benar diperlukan, karena bisa batasi zoom */
}

body {
    font-family: 'Poppins', sans-serif;
    background: #faf9ff;
    color: #333;
    margin: 0; /* ← pastikan tidak ada padding/margin di body */
    font-size: var(--fs-md);
    line-height: 1.5;
    min-width: 100vw; /* ← sangat penting untuk zoom */
}

.main-wrapper {
    display: flex;
    gap: var(--gap-md);
    width: 100%;
    min-width: 100vw; /* ← agar tidak menyusut saat zoom out */
    margin: 0 auto;
    padding: 0; /* opsional: untuk jarak halus di samping */
}

.form-box,
.preview-box {
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
}

.form-header {
    background: #f9f7ff;
    color: var(--primary);
    padding: 0.6rem 1rem;
    font-size: 1rem;
    font-weight: 600;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-body {
    padding: var(--gap-sm);
}

.form-group {
    margin-bottom: var(--gap-md);
}

.form-row {
    display: grid;
    gap: var(--gap-md);
    margin-bottom: var(--gap-md);
}

.form-row-2 {
    grid-template-columns: 1fr 1fr;
}

.form-row-desc-foto {
    grid-template-columns: 2fr 1fr;
}

.form-row .form-group {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 4px;
    font-size: var(--fs-md);
    color: var(--primary);
}
.required { color: var(--secondary); }

input[type="text"],
input[type="number"],
input[type="file"],
textarea {
    width: 100%;
    padding: 7px 10px;
    border: 2px solid #e8e6f2;
    border-radius: var(--radius);
    font-size: var(--fs-md);
    background: #fcfbff;
    font-family: inherit;
    transition: border-color 0.2s, background 0.2s;
}
input:focus,
textarea:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.1);
}

textarea {
    min-height: 76px;
    resize: vertical;
}

.help {
    display: block;
    font-size: var(--fs-xs);
    color: var(--text-muted);
    margin-top: 3px;
    font-style: italic;
}

.variant-input-group {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.variant-input-group input {
    flex: 1;
    min-width: 140px;
}
.variant-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 4px;
    min-height: 18px;
}
.variant-tag {
    background: var(--soft);
    color: var(--primary);
    padding: 2px 7px;
    border-radius: 14px;
    font-size: var(--fs-xs);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 3px;
}

.action-bar {
    padding: 0.6rem 1rem;
    background: #fbf9ff;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 8px;
}

.preview-box {
    width: 260px;
    flex-shrink: 0;
}

.preview-header {
    background: #f9f7ff;
    color: var(--secondary);
    padding: 0.5rem;
    font-size: 0.95rem;
    font-weight: 600;
    text-align: center;
    border-bottom: 1px solid var(--border);
}

.preview-body {
    padding: var(--gap-sm);
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: var(--gap-xs);
}

.preview-img-container {
    position: relative;
}
.preview-img-placeholder,
.preview-img {
    width: 140px;
    height: 140px;
    border-radius: var(--radius);
    background: #fcfbff;
}
.preview-img-placeholder {
    border: 1.5px dashed var(--soft);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 1.4rem;
}
.preview-img {
    object-fit: contain;
    display: none;
}

.preview-text h3 {
    color: var(--primary);
    margin: 4px 0 2px;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.3;
}

.preview-meta {
    font-size: var(--fs-sm);
    color: var(--primary);
    margin-bottom: 2px;
}
.preview-meta span {
    color: var(--secondary);
    font-weight: 600;
}

.preview-text p {
    font-size: var(--fs-xs);
    color: #555;
    margin-top: 0;
    line-height: 1.4;
    max-height: 40px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: var(--radius);
    font-weight: 600;
    font-size: var(--fs-md);
    cursor: pointer;
    text-decoration: none;
    border: none;
    transition: all 0.2s ease;
    font-family: inherit;
    min-height: var(--btn-h);
    white-space: nowrap;
}
.btn-primary {
    background: var(--secondary);
    color: white;
    flex: 1;
}
.btn-primary:hover {
    background: #a34056;
    transform: translateY(-1px);
}
.btn-secondary {
    background: var(--soft);
    color: var(--primary);
    flex: 1;
}
.btn-secondary:hover {
    background: #d5b4d9;
}

.alert {
    background: #fff8f8;
    color: #c0392b;
    padding: 7px 12px;
    border-radius: var(--radius);
    margin-bottom: var(--gap-sm);
    border-left: 3px solid var(--secondary);
    font-size: var(--fs-xs);
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 1px 5px rgba(182, 75, 98, 0.08);
}

@media (max-width: 768px) {
    body { padding: var(--gap-xs); }
    .main-wrapper {
        flex-direction: column;
    }
    .preview-box {
        width: 100%;
        max-width: 280px;
        margin: 0 auto;
    }
    .preview-img-placeholder,
    .preview-img {
        width: 120px;
        height: 120px;
    }
    .form-body,
    .action-bar {
        padding: var(--gap-xs);
    }
    .form-header {
        padding: 0.5rem 0.8rem;
        font-size: 0.95rem;
    }
}
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="form-box">
            <div class="form-header">
                <i class="fas fa-plus-circle"></i>
                Tambah Produk
            </div>
            <div class="form-body">
                <?php if ($msg): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($msg); ?>
                    </div>
                <?php endif; ?>

                <form id="addForm" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nama">Nama Produk <span class="required">*</span></label>
                        <input 
                            id="nama" 
                            type="text" 
                            name="nama" 
                            value="<?php echo htmlspecialchars($namaVal); ?>" 
                            placeholder="Contoh: Jus Buah"
                            maxlength="100"
                        >
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label for="harga">Harga (Rp) <span class="required">*</span></label>
                            <input 
                                id="harga" 
                                type="number" 
                                name="harga" 
                                value="<?php echo htmlspecialchars($hargaVal); ?>" 
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
                                    value="<?php echo htmlspecialchars($varianVal); ?>"
                                    placeholder="S,M,L"
                                    maxlength="255"
                                >
                            </div>
                            <small class="help">Pisahkan dengan koma</small>
                            <div class="variant-tags" id="variantPreview"></div>
                        </div>
                    </div>

                    <div class="form-row form-row-desc-foto">
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi</label>
                            <textarea 
                                id="deskripsi" 
                                name="deskripsi"
                                placeholder="Ceritakan keunggulan produk..."
                                maxlength="2000"
                            ><?php echo htmlspecialchars($deskripsiVal); ?></textarea>
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
                    <div class="preview-meta">
                        <span id="liveHarga">Rp 0</span>
                        <span id="liveVarianDisplay"></span>
                    </div>
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
            return variants.length ? variants.join(', ') : '';
        };

        const namaInput = document.getElementById('nama');
        const hargaInput = document.getElementById('harga');
        const varianInput = document.getElementById('varian');
        const deskripsiInput = document.getElementById('deskripsi');
        const fotoInput = document.getElementById('foto');
        const liveNama = document.getElementById('liveNama');
        const liveHarga = document.getElementById('liveHarga');
        const liveVarianDisplay = document.getElementById('liveVarianDisplay');
        const liveDeskripsi = document.getElementById('liveDeskripsi');
        const livePreviewImg = document.getElementById('livePreviewImg');
        const placeholder = document.querySelector('.preview-img-placeholder');
        const variantPreview = document.getElementById('variantPreview');

        liveNama.textContent = namaInput.value || 'Nama Produk';
        liveHarga.textContent = formatRupiah(hargaInput.value);
        liveDeskripsi.textContent = deskripsiInput.value || 'Deskripsi produk akan muncul...';

        (() => {
            const vars = varianInput.value.split(',').map(v => v.trim()).filter(v => v);
            liveVarianDisplay.textContent = vars.length ? ' • ' + updateVarianText(vars) : '';
            updateVariantPreview();
        })();

        namaInput.addEventListener('input', () => liveNama.textContent = namaInput.value || 'Nama Produk');
        hargaInput.addEventListener('input', () => liveHarga.textContent = formatRupiah(hargaInput.value));
        deskripsiInput.addEventListener('input', () => liveDeskripsi.textContent = deskripsiInput.value || 'Deskripsi produk akan muncul...');
        varianInput.addEventListener('input', () => {
            const vars = varianInput.value.split(',').map(v => v.trim()).filter(v => v);
            liveVarianDisplay.textContent = vars.length ? ' • ' + updateVarianText(vars) : '';
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

        function updateVariantPreview() {
            const variants = varianInput.value
                ? varianInput.value.split(',').map(v => v.trim()).filter(v => v)
                : [];
            variantPreview.innerHTML = variants.map(v =>
                `<span class="variant-tag"><i class="fas fa-tag"></i> ${v}</span>`
            ).join('');
        }

        updateVariantPreview();
    </script>
</body>
</html>