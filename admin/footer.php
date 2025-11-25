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
    <title>Edit Footer — Admin Dapoer Funraise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #B64B62;
            --secondary: #5A46A2;
            --cream: #FFF5EE;
            --dark: #2a1f3d;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f7fc;
            color: var(--dark);
            padding: 20px;
        }
        .container { max-width: 800px; margin: 0 auto; }
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(90, 70, 162, 0.1);
            padding: 32px;
            margin-bottom: 24px;
        }
        .card-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 24px;
            color: var(--secondary);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark);
        }
        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0d6eb;
            border-radius: 12px;
            font-size: 1.05rem;
            background-color: var(--cream);
            transition: border-color 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.2);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 32px;
            border: none;
            border-radius: 14px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #d05876);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(182, 75, 98, 0.4);
        }
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }
        .btn-back {
            background: #e0e0e0;
            color: var(--dark);
        }
        .btn-back:hover {
            background: #d0d0d0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1 class="card-title">
                <i class="fas fa-edit"></i> Edit Footer Website
            </h1>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message['type'] ?>">
                    <i class="fas fa-<?= $message['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                    <?= htmlspecialchars($message['text']) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="copyright_text">Teks Hak Cipta (boleh pakai HTML, contoh: <code>&lt;strong&gt;</code>)</label>
                    <input type="text"
                           id="copyright_text"
                           name="copyright_text"
                           class="form-control"
                           value="<?= htmlspecialchars($current['copyright_text'], ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="Contoh: © 2025 &lt;strong&gt;Dapoer Funraise&lt;/strong&gt;"
                           required>
                </div>

                <div class="form-group">
                    <label for="main_text">Teks Utama Footer</label>
                    <input type="text"
                           id="main_text"
                           name="main_text"
                           class="form-control"
                           value="<?= htmlspecialchars($current['main_text']) ?>"
                           placeholder="Contoh: Mendukung Expo Campus MAN 2 Samarinda"
                           required>
                </div>

                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="index.php" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>
            </form>
        </div>

        <div class="card">
            <h2 class="card-title"><i class="fas fa-eye"></i> Preview Footer</h2>
            <div style="background: #2a1f3d; color: white; padding: 24px; border-radius: 16px; text-align: center;">
                <p style="font-size: 1.05rem; margin: 0;">
                    <?= $current['copyright_text'] ?> — 
                    <?= htmlspecialchars($current['main_text']) ?>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Client-side confirmation (sesuai preferensi Anda)
        document.querySelector('form').addEventListener('submit', function(e) {
            if (!confirm('Apakah Anda yakin ingin menyimpan perubahan pada footer?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>