<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

// Default next number
$stmtMax = $pdo->query("SELECT MAX(step_number) as max_num FROM cara_pesan_steps");
$max = $stmtMax->fetchColumn();
$nextNum = $max ? $max + 1 : 1;

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $icon = trim($_POST['icon_class'] ?? 'fa-cookie-bite');
    $step_number = intval($_POST['step_number']);
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if (empty($title) || empty($desc)) {
        $error = 'Judul dan deskripsi wajib diisi.';
    } elseif (!preg_match('/^fa-[a-z0-9-]+$/i', $icon)) {
        $error = 'Kelas ikon tidak valid. Contoh: fa-whatsapp, fa-truck';
    } else {
        try {
            $stmtOrd = $pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM cara_pesan_steps");
            $nextOrder = $stmtOrd->fetchColumn();

            $stmt = $pdo->prepare("
                INSERT INTO cara_pesan_steps (icon_class, step_number, title, description, sort_order, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$icon, $step_number, $title, $desc, $nextOrder]);
            $success = 'Berhasil: Langkah baru telah ditambahkan.';
        } catch (Exception $e) {
            $error = 'Gagal menyimpan langkah. Coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Langkah — Dapoer Funraise</title>
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
        .preview-header { 
            color: var(--secondary); 
            justify-content: center; 
        }

        .form-body, .preview-body {
            padding: 1.5rem 1.4rem;
        }

        .inline-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.1rem;
        }

        .inline-item {
            flex: 1;
            min-width: 140px;
        }

        .inline-item label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.93rem;
            color: var(--primary);
        }

        .input-with-icon {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-with-icon input {
            padding-right: 36px;
        }

        .icon-preview {
            position: absolute;
            right: 10px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--soft);
            color: var(--primary);
            font-size: 0.8rem;
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

        input[type="text"], input[type="number"], textarea {
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
        input[type="number"]:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .help-text {
            display: block;
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 3px;
            font-style: italic;
        }

        code {
            background: #f0effc;
            padding: 2px 5px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.85em;
        }

        /* Alert */
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

        /* Buttons */
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

        .btn-secondary {
            background: linear-gradient(135deg, var(--soft), #c8a5d0);
            color: var(--primary);
            flex: 1;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #d0a8d5, #c095cb);
        }

        /* Action bar */
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

        /* Preview Card — konsisten */
        .preview-card {
            background: #faf9ff;
            border: 1px solid #f0eaff;
            border-radius: 12px;
            padding: 20px;
            width: 100%;
            max-width: 320px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .preview-step-badge {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.4rem;
            color: var(--primary);
            margin-bottom: 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        }

        .preview-icon {
            font-size: 2.2rem;
            color: var(--primary);
            margin-bottom: 16px;
        }

        .preview-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .preview-desc {
            font-size: 0.92rem;
            color: #555;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <!-- Form Section -->
        <div class="form-box">
            <div class="form-header">
                <i class="fas fa-plus" style="color: var(--secondary);"></i>
                Tambah Langkah Baru
            </div>

            <div class="form-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" id="stepForm">
                    <div class="inline-group">
                        <div class="inline-item">
                            <label for="icon_class">Kelas Ikon</label>
                            <div class="input-with-icon">
                                <input 
                                    type="text" 
                                    id="icon_class" 
                                    name="icon_class"
                                    value="fa-cookie-bite"
                                    placeholder="fa-cookie-bite"
                                    required
                                >
                                <span class="icon-preview">
                                    <i class="fas fa-cookie-bite"></i>
                                </span>
                            </div>
                            <span class="help-text">
                                Contoh: <code>fa-truck</code>, <code>fa-whatsapp</code>
                            </span>
                        </div>

                        <div class="inline-item">
                            <label for="step_number">No.</label>
                            <input 
                                type="number" 
                                id="step_number" 
                                name="step_number"
                                value="<?= (int)$nextNum ?>"
                                min="1"
                                required
                            >
                        </div>

                        <div class="inline-item">
                            <label for="title">Judul Langkah</label>
                            <input 
                                type="text" 
                                id="title" 
                                name="title"
                                placeholder="Pilih produk favorit"
                                maxlength="80"
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea 
                            id="description" 
                            name="description"
                            placeholder="Jelaskan langkah secara singkat dan jelas..."
                            rows="4"
                            maxlength="200"
                            required
                        ></textarea>
                    </div>

                    <div class="action-bar">
                        <a href="cara-pesan.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Langkah
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Section -->
        <div class="preview-box">
            <div class="preview-header">
                <i class="fas fa-eye"></i> Preview Langkah
            </div>
            <div class="preview-body">
                <div class="preview-card">
                    <div class="preview-step-badge" id="previewStepBadge"><?= (int)$nextNum ?></div>
                    <i class="fas fa-cookie-bite preview-icon" id="previewIcon"></i>
                    <div class="preview-title" id="previewTitle">Pilih produk favorit</div>
                    <div class="preview-desc" id="previewDesc">
                        Pilih camilan kesukaanmu dari daftar menu yang tersedia.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Live preview
        document.getElementById('step_number').addEventListener('input', e => {
            const num = e.target.value.trim() || '1';
            document.getElementById('previewStepBadge').textContent = num;
        });

        document.getElementById('icon_class').addEventListener('input', e => {
            let val = e.target.value.trim();
            if (!/^fa-[a-z0-9-]+$/.test(val)) val = 'fa-cookie-bite';
            const fullClass = 'fas ' + val;
            document.getElementById('previewIcon').className = fullClass + ' preview-icon';
            // Update inline icon preview
            const inlineIcon = document.querySelector('.icon-preview i');
            if (inlineIcon) inlineIcon.className = fullClass;
        });

        document.getElementById('title').addEventListener('input', e => {
            document.getElementById('previewTitle').textContent = escapeHtml(e.target.value || 'Judul Langkah');
        });

        document.getElementById('description').addEventListener('input', e => {
            document.getElementById('previewDesc').textContent = escapeHtml(
                e.target.value || 'Deskripsi'
            );
        });

        // Init
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('step_number').dispatchEvent(new Event('input'));
            document.getElementById('icon_class').dispatchEvent(new Event('input'));
            document.getElementById('title').dispatchEvent(new Event('input'));
            document.getElementById('description').dispatchEvent(new Event('input'));
        });
    </script>
</body>
</html>