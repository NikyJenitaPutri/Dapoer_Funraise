<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
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
                INSERT INTO contact_cards (icon_class, title, label, href, sort_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?)
            ")->execute([$icon, $title, $label, $href, $sort, $is_active]);
            $_SESSION['kontak_alert'] = 'Kontak berhasil ditambahkan.';
            header('Location: kontak.php');
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kontak — Dapoer Funraise</title>
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

        .input-pair {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 1.2rem;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .input-group label {
            font-weight: 600;
            font-size: 0.92rem;
            color: var(--primary);
            line-height: 1.3;
            margin: 0;
        }

        .input-group input {
            width: 100%;
            padding: 11px 15px;
            border: 2px solid #e8e6f2;
            border-radius: 10px;
            font-size: 0.93rem;
            background: #faf9ff;
            font-family: inherit;
            height: 46px;
            line-height: 1.4;
            margin: 0;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.1);
        }

        /* Checkbox rapi & lurus */
        .checkbox-group {
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: center;
            gap: 6px;
            height: 46px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--secondary);
            margin: 0;
            cursor: pointer;
            transform: translateY(-0.5px);
        }

        .checkbox-group label {
            font-weight: 500;
            font-size: 0.95rem;
            color: var(--primary);
            cursor: pointer;
            margin: 0;
            padding-top: 1px;
        }

        .help-text {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 4px;
            line-height: 1.4;
        }

        .help-text a {
            color: var(--secondary);
            text-decoration: underline;
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
            .input-pair { grid-template-columns: 1fr; }
            .action-bar { flex-direction: column; }
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

        /* Preview tanpa card dalam card */
        .preview-body {
            padding: 1.5rem 1.2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
        }

        .preview-icon {
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--soft);
            color: var(--primary);
            border-radius: 50%;
            font-size: 1.8rem;
        }

        .preview-title {
            font-weight: 600;
            color: var(--primary);
            font-size: 1.1rem;
            margin: 4px 0 2px;
        }

        .preview-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .preview-href {
            font-family: monospace;
            font-size: 0.85rem;
            color: #555;
            background: #faf9ff;
            padding: 6px 12px;
            border-radius: 6px;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .preview-meta {
            display: flex;
            justify-content: space-between;
            width: 100%;
            font-size: 0.85rem;
            margin-top: 8px;
        }

        .preview-order { font-weight: 600; color: var(--primary); }
        .preview-status {
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
        }
        .status-active { background: rgba(102, 187, 106, 0.15); color: #2e7d32; }
        .status-inactive { background: rgba(244, 67, 54, 0.1); color: #c62828; }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="form-box">
            <div class="form-header">
                <i class="fas fa-plus" style="color: var(--secondary);"></i>
                Tambah Kontak Baru
            </div>

            <div class="form-body">
                <?php if ($alert): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($alert) ?>
                    </div>
                <?php endif; ?>

                <form id="addForm" method="POST">

                    <!-- Baris 1: Icon Class | Judul -->
                    <div class="input-pair">
                        <div class="input-group">
                            <label for="icon_class">Ikon</label>
                            <input 
                                type="text" 
                                id="icon_class" 
                                name="icon_class"
                                value="fab fa-whatsapp"
                                placeholder="fab fa-whatsapp"
                                required
                            >
                            <div class="help-text">
                                Cari ikon di <a href="https://fontawesome.com/search?o=r&m=free" target="_blank" rel="noopener">Font Awesome Search</a>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="title">Judul</label>
                            <input 
                                type="text" 
                                id="title" 
                                name="title"
                                placeholder="WhatsApp"
                                required
                            >
                        </div>
                    </div>

                    <!-- Baris 2: Label | URL -->
                    <div class="input-pair">
                        <div class="input-group">
                            <label for="label">Label</label>
                            <input 
                                type="text" 
                                id="label" 
                                name="label"
                                placeholder="Yunisa"
                                required
                            >
                        </div>
                        <div class="input-group">
                            <label for="href">URL</label>
                            <input 
                                type="text" 
                                id="href" 
                                name="href"
                                placeholder="https://wa.me/6281234567890"
                                required
                            >
                        </div>
                    </div>

                    <!-- Baris 3: Urutan | Checkbox (default aktif) -->
                    <div class="input-pair">
                        <div class="input-group">
                            <label for="sort_order">Urutan Tampil</label>
                            <input 
                                type="number" 
                                id="sort_order" 
                                name="sort_order"
                                value="0"
                                min="0"
                            >
                        </div>
                        <div class="input-group" style="display: flex; align-items: flex-end; justify-content: flex-end; height: 74px;">
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_active" name="is_active" checked>
                                <label for="is_active">Tampilkan</label>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <div class="action-bar">
                <a href="kontak.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" form="addForm" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Kontak
                </button>
            </div>
        </div>

        <!-- Preview Section (tanpa card dalam card) -->
        <div class="preview-box">
            <div class="preview-header">
                <i class="fas fa-eye"></i> Preview Kontak
            </div>
            <div class="preview-body">
                <div class="preview-icon" id="previewIcon">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div class="preview-title" id="previewTitle">Judul</div>
                <div class="preview-label" id="previewLabel">Label</div>
                <div class="preview-href" id="previewHref">URL</div>
                <div class="preview-meta">
                    <span class="preview-order" id="previewOrder">Urutan: 0</span>
                    <span class="preview-status status-active" id="previewStatus">Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        const iconInput = document.getElementById('icon_class');
        const titleInput = document.getElementById('title');
        const labelInput = document.getElementById('label');
        const hrefInput = document.getElementById('href');
        const sortInput = document.getElementById('sort_order');
        const activeCheckbox = document.getElementById('is_active');

        const previewIcon = document.getElementById('previewIcon');
        const previewTitle = document.getElementById('previewTitle');
        const previewLabel = document.getElementById('previewLabel');
        const previewHref = document.getElementById('previewHref');
        const previewOrder = document.getElementById('previewOrder');
        const previewStatus = document.getElementById('previewStatus');

        function renderIcon(iconClass) {
            const i = document.createElement('i');
            i.className = iconClass.trim() || 'fas fa-question';
            return i.outerHTML;
        }

        // Live preview
        iconInput.addEventListener('input', e => {
            previewIcon.innerHTML = renderIcon(e.target.value);
        });

        titleInput.addEventListener('input', e => {
            previewTitle.textContent = e.target.value || '—';
        });

        labelInput.addEventListener('input', e => {
            previewLabel.textContent = e.target.value || '—';
        });

        hrefInput.addEventListener('input', e => {
            previewHref.textContent = e.target.value || '—';
        });

        sortInput.addEventListener('input', e => {
            previewOrder.textContent = `Urutan: ${e.target.value || '0'}`;
        });

        activeCheckbox.addEventListener('change', e => {
            previewStatus.textContent = e.target.checked ? 'Aktif' : 'Nonaktif';
            previewStatus.className = 'preview-status ' + (e.target.checked ? 'status-active' : 'status-inactive');
        });

        // Inisialisasi preview dari nilai awal
        window.addEventListener('DOMContentLoaded', () => {
            previewIcon.innerHTML = renderIcon(iconInput.value);
        });
    </script>
</body>
</html>