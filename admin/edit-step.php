<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    $_SESSION['cp_alert'] = 'Gagal: ID langkah tidak ditemukan.';
    header('Location: cara-pesan.php');
    exit;
}

// Ambil data langkah
$stmt = $pdo->prepare("SELECT * FROM cara_pesan_steps WHERE id = ?");
$stmt->execute([$id]);
$step = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$step) {
    $_SESSION['cp_alert'] = 'Gagal: Langkah tidak ditemukan di database.';
    header('Location: cara-pesan.php');
    exit;
}

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $icon_class = trim($_POST['icon_class'] ?? 'fa-cookie-bite');
    $step_number = intval($_POST['step_number'] ?? 1);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($description)) {
        $error = 'Judul dan deskripsi wajib diisi.';
    } elseif (!preg_match('/^fa-[a-z0-9-]+$/i', $icon_class)) {
        $error = 'Kelas ikon tidak valid. Contoh: fa-whatsapp, fa-truck';
    } else {
        try {
            $stmtUpd = $pdo->prepare("
                UPDATE cara_pesan_steps 
                SET icon_class = ?, step_number = ?, title = ?, description = ?
                WHERE id = ?
            ");
            $stmtUpd->execute([$icon_class, $step_number, $title, $description, $id]);

            $stmt = $pdo->prepare("SELECT * FROM cara_pesan_steps WHERE id = ?");
            $stmt->execute([$id]);
            $step = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $success = 'Berhasil: Langkah telah diperbarui.';
        } catch (Exception $e) {
            $error = 'Gagal memperbarui langkah: ' . $e->getMessage();
            error_log('Edit step error: ' . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Langkah — Dapoer Funraise</title>
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
        .preview-header { color: var(--secondary); justify-content: center; }

        .form-body, .preview-body {
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

        .btn-secondary {
            background: linear-gradient(135deg, var(--soft), #c8a5d0);
            color: var(--primary);
            flex: 1;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #d0a8d5, #c095cb);
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

        .preview-icon-container {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            margin-bottom: 16px;
        }

        .preview-icon {
            font-size: 2.2rem;
            color: var(--primary);
        }

        .preview-meta {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .preview-number {
            font-weight: 600;
            color: var(--primary);
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
                <i class="fas fa-edit" style="color: var(--secondary);"></i>
                Edit Langkah #<?= (int)$step['id'] ?>
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

                <form method="POST" id="editForm">
                    <div class="inline-group">
                        <div class="inline-item">
                            <label for="icon_class">Kelas Ikon</label>
                            <div class="input-with-icon">
                                <input 
                                    type="text" 
                                    id="icon_class" 
                                    name="icon_class"
                                    value="<?= htmlspecialchars($step['icon_class']) ?>"
                                    placeholder="fa-cookie-bite"
                                    required
                                >
                                <span class="icon-preview">
                                    <i class="fas <?= htmlspecialchars($step['icon_class']) ?>"></i>
                                </span>
                            </div>
                        </div>

                        <div class="inline-item">
                            <label for="step_number">No.</label>
                            <input 
                                type="number" 
                                id="step_number" 
                                name="step_number"
                                value="<?= (int)$step['step_number'] ?>"
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
                                value="<?= htmlspecialchars($step['title']) ?>"
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
                            rows="4"
                            maxlength="200"
                            required><?= htmlspecialchars($step['description']) ?></textarea>
                    </div>

                    <div class="action-bar">
                        <a href="cara-pesan.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
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
                    <div class="preview-icon-container">
                        <i class="fas <?= htmlspecialchars($step['icon_class']) ?>" 
                           id="previewIcon" 
                           style="font-size: 2.2rem; color: var(--primary);"></i>
                    </div>
                    <div class="preview-meta">
                        <span class="preview-number">Langkah <span id="previewNumber"><?= (int)$step['step_number'] ?></span></span>
                        <span>• Preview</span>
                    </div>
                    <div class="preview-title" id="previewTitle"><?= htmlspecialchars($step['title']) ?></div>
                    <div class="preview-desc" id="previewDesc"><?= htmlspecialchars($step['description']) ?></div>
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

        document.getElementById('step_number').addEventListener('input', e => {
            const num = e.target.value.trim() || '1';
            document.getElementById('previewNumber').textContent = num;
        });

        document.getElementById('icon_class').addEventListener('input', e => {
            let val = e.target.value.trim();
            if (!/^fa-[a-z0-9-]+$/i.test(val)) {
                val = 'fa-cookie-bite';
            }
            const fullClass = 'fas ' + val;
            document.getElementById('previewIcon').className = fullClass;
            const inlineIcon = document.querySelector('.icon-preview i');
            if (inlineIcon) inlineIcon.className = fullClass;
        });

        document.getElementById('title').addEventListener('input', e => {
            document.getElementById('previewTitle').textContent = e.target.value || 'Judul langkah';
        });

        document.getElementById('description').addEventListener('input', e => {
            document.getElementById('previewDesc').textContent = e.target.value || 'Deskripsi langkah';
        });
    </script>
</body>
</html>