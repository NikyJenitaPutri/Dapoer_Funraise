<?php
session_start();
require_once '../config.php';
require_once 'footer.php'; // sesuaikan path

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $main_text = trim($_POST['main_text'] ?? '');
    $copyright_text = trim($_POST['copyright_text'] ?? '');

    if (empty($main_text) || empty($copyright_text)) {
        $message = ['type' => 'error', 'text' => 'Semua kolom wajib diisi.'];
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO footer_section (id, main_text, copyright_text, is_active)
            VALUES (1, ?, ?, 1)
            ON DUPLICATE KEY UPDATE
                main_text = VALUES(main_text),
                copyright_text = VALUES(copyright_text)
        ");
        $success = $stmt->execute([$main_text, $copyright_text]);

        if ($success) {
            $message = ['type' => 'success', 'text' => 'Footer berhasil diperbarui.'];
        } else {
            $message = ['type' => 'error', 'text' => 'Gagal menyimpan data.'];
        }
    }
}

// Ambil data saat ini
$stmt = $pdo->query("SELECT main_text, copyright_text FROM footer_section WHERE id = 1");
$current = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
    'main_text' => 'Mendukung Expo Campus MAN 2 Samarinda',
    'copyright_text' => '© 2025 <strong>Dapoer Funraise</strong>'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Footer — Admin Dapoer Funraise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5A46A2;
            --secondary: #B64B62;
            --accent: #F9CC22;
            --bg-light: #FFF5EE;
            --soft: #DFBEE0;
            --text-muted: #9180BB;
            --card-shadow: 0 5px 20px rgba(90, 70, 162, 0.1);
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
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            gap: 24px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
        }

        .card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            border: 1px solid #f0eaff;
        }

        .card-header {
            background: linear-gradient(120deg, #f5f3ff, #faf5ff);
            padding: 1.2rem 1.5rem;
            font-size: 1.2rem;
            font-weight: 700;
            border-bottom: 1px solid #f0eaff;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--primary);
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 0.8rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.95rem;
            color: var(--primary);
        }

        .form-control {
            width: 100%;
            padding: 11px 15px;
            border: 2px solid #e8e6f2;
            border-radius: 10px;
            font-size: 0.93rem;
            background: #faf9ff;
            font-family: inherit;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.1);
        }

        .alert {
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 3px solid #4caf50;
        }

        .alert-error {
            background: #fff8f8;
            color: #c0392b;
            border-left: 3px solid var(--secondary);
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

        /* Footer preview styling — sesuai brand */
        .footer-preview {
            background: var(--primary);
            color: white;
            padding: 24px 20px;
            border-radius: 12px;
            font-size: 0.95rem;
            line-height: 1.5;
            text-align: center;
        }

        .footer-preview .copyright {
            font-weight: 600;
            margin-bottom: 6px;
        }

        .footer-preview .main-text {
            opacity: 0.9;
            font-size: 0.88rem;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 576px) {
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Form Card -->
        <div class="card" style="flex: 1;">
            <div class="card-header">
                <i class="fas fa-edit" style="color: var(--secondary);"></i>
                Edit
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?= $message['type'] ?>">
                        <i class="fas fa-<?= $message['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                        <?= htmlspecialchars($message['text']) ?>
                    </div>
                <?php endif; ?>

                <form id="footerForm" method="POST">
                    <div class="form-group">
                        <label for="copyright_text">Teks Hak Cipta
                        <input type="text"
                               id="copyright_text"
                               name="copyright_text"
                               class="form-control"
                               value="<?= htmlspecialchars($current['copyright_text'], ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="Contoh: © 2025 &lt;strong&gt;Dapoer Funraise&lt;/strong&gt;"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="main_text">Teks Utama</label>
                        <input type="text"
                               id="main_text"
                               name="main_text"
                               class="form-control"
                               value="<?= htmlspecialchars($current['main_text']) ?>"
                               placeholder="Contoh: Mendukung Expo Campus MAN 2 Samarinda"
                               required>
                    </div>

                    <div class="actions">
                        <a href="../pengaturan.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Card -->
        <div class="card" style="width: 380px; flex-shrink: 0;">
            <div class="card-header">
                <i class="fas fa-eye" style="color: var(--secondary);"></i>
                Preview Footer
            </div>
            <div class="card-body" style="display: flex; align-items: center; justify-content: center;">
                <div class="footer-preview" id="livePreview">
                    <div class="copyright" id="previewCopyright">
                        <?= html_entity_decode($current['copyright_text']) ?>
                    </div>
                    <div class="main-text" id="previewMain">
                        <?= htmlspecialchars($current['main_text']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Live preview (sesuai preferensi Anda)
        document.getElementById('copyright_text').addEventListener('input', function() {
            // Decode HTML entities secara aman (tanpa eval/XSS)
            const txt = this.value
                .replace(/&lt;/g, '<')
                .replace(/&gt;/g, '>')
                .replace(/&amp;/g, '&')
                .replace(/&quot;/g, '"')
                .replace(/&#039;/g, "'");
            document.getElementById('previewCopyright').innerHTML = txt || '—';
        });

        document.getElementById('main_text').addEventListener('input', function() {
            document.getElementById('previewMain').textContent = this.value || '—';
        });

        // Konfirmasi sebelum submit (sesuai preferensi Anda)
        document.getElementById('footerForm').addEventListener('submit', function(e) {
            if (!confirm('Simpan perubahan? Perubahan akan langsung tampil di website.')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>