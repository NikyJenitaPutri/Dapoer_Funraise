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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $icon = trim($_POST['icon_class'] ?? 'fa-cookie-bite');
    $step_number = intval($_POST['step_number']);
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');

    if (empty($title) || empty($desc)) {
        $error = 'Judul dan deskripsi wajib diisi.';
    } else {
        try {
            // Auto sort_order = max + 1
            $stmtOrd = $pdo->query("SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order FROM cara_pesan_steps");
            $nextOrder = $stmtOrd->fetchColumn();

            $stmt = $pdo->prepare("
                INSERT INTO cara_pesan_steps (icon_class, step_number, title, description, sort_order, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$icon, $step_number, $title, $desc, $nextOrder]);
            $_SESSION['cp_alert'] = 'Berhasil: Langkah baru telah ditambahkan.';
            header('Location: cara-pesan.php');
            exit;
        } catch (Exception $e) {
            $error = 'Gagal menyimpan langkah. Coba lagi.';
            error_log('Add step error: ' . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Langkah — Cara Pesan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Sama seperti style di cara-pesan.php — disingkat untuk hemat ruang */
        :root {
            --primary: #B64B62;
            --secondary: #5A46A2;
            --accent: #F9CC22;
            --cream: #FFF5EE;
            --dark: #2a1f3d;
            --font-main: 'Poppins', sans-serif;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-main);
            background-color: #f9f7fc;
            padding: 20px;
        }
        .container {
            max-width: 800px; margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(90, 70, 162, 0.1);
            overflow: hidden;
        }
        header {
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .content { padding: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; margin-bottom: 8px;
            font-weight: 600; color: var(--dark);
        }
        .form-control {
            width: 100%; padding: 12px 16px;
            border-radius: 10px;
            border: 2px solid #e0d6eb;
            font-size: 1rem;
        }
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 12px 24px; border-radius: 10px;
            font-weight: 600; cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #d05876);
            color: white;
        }
        .btn-gray {
            background: #9e9e9e;
            color: white;
        }
        .alert-error {
            background: #fee2e2; color: #b91c1c;
            padding: 12px; border-radius: 8px;
            margin-bottom: 20px;
        }
        .icon-help {
            font-size: 0.9rem; color: #666; margin-top: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div><i class="fas fa-plus"></i> Tambah Langkah Baru</div>
            <a href="cara-pesan.php" class="btn btn-gray">← Kembali</a>
        </header>
        <div class="content">
            <?php if (isset($error)): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="icon_class">Kelas Ikon Font Awesome (misal: fa-cart-plus)</label>
                    <input type="text" id="icon_class" name="icon_class" class="form-control"
                           value="fa-cookie-bite" placeholder="Contoh: fa-whatsapp, fa-truck" required>
                    <p class="icon-help">
                        ✅ Gunakan hanya bagian <code>fa-...</code>, contoh: <code>fa-whatsapp</code>, <code>fa-truck</code>
                        <br>🔗 Daftar: <a href="https://fontawesome.com/v6/search?o=r&m=free" target="_blank">fontawesome.com/search</a>
                    </p>
                </div>

                <div class="form-row" style="display:flex;gap:16px;flex-wrap:wrap">
                    <div style="flex:1;min-width:150px">
                        <div class="form-group">
                            <label for="step_number">Nomor Langkah</label>
                            <input type="number" id="step_number" name="step_number" class="form-control"
                                   value="<?= $nextNum ?>" min="1" required>
                        </div>
                    </div>
                    <div style="flex:2;min-width:300px">
                        <div class="form-group">
                            <label for="title">Judul Langkah</label>
                            <input type="text" id="title" name="title" class="form-control"
                                   placeholder="Contoh: Lihat Produk" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" class="form-control" rows="4"
                              placeholder="Jelaskan langkah secara singkat..." required></textarea>
                </div>

                <div style="margin-top: 20px; display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Langkah
                    </button>
                    <a href="cara-pesan.php" class="btn btn-gray">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>