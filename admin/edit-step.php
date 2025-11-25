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

$alert = '';
if (isset($_SESSION['edit_alert'])) {
    $alert = $_SESSION['edit_alert'];
    unset($_SESSION['edit_alert']);
}

// Proses update jika POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $icon_class = trim($_POST['icon_class'] ?? 'fa-cookie-bite');
    $step_number = intval($_POST['step_number'] ?? 1);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($description)) {
        $error = 'Gagal: Judul dan deskripsi wajib diisi.';
    } else {
        try {
            $stmtUpd = $pdo->prepare("
                UPDATE cara_pesan_steps 
                SET icon_class = ?, step_number = ?, title = ?, description = ?
                WHERE id = ?
            ");
            $stmtUpd->execute([$icon_class, $step_number, $title, $description, $id]);
            $_SESSION['cp_alert'] = 'Berhasil: Langkah telah diperbarui.';
            header('Location: cara-pesan.php');
            exit;
        } catch (Exception $e) {
            $error = 'Gagal memperbarui langkah. Coba lagi.';
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
    <title>Edit Langkah — Cara Pesan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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
            max-width: 800px;
            margin: 0 auto;
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
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 2px solid #e0d6eb;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.2);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-align: center;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #d05876);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(182, 75, 98, 0.3);
        }
        .btn-gray {
            background: #9e9e9e;
            color: white;
        }
        .btn-gray:hover {
            background: #757575;
        }
        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .icon-help {
            font-size: 0.9rem;
            color: #666;
            margin-top: 6px;
        }
        .icon-preview {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: var(--cream);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--primary);
            margin-right: 8px;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div>
                <i class="fas fa-edit"></i> Edit Langkah #<?= (int)$step['id'] ?>
            </div>
            <a href="cara-pesan.php" class="btn btn-gray">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </header>

        <div class="content">
            <?php if (isset($error) || $alert): ?>
                <div class="alert-error">
                    <?= htmlspecialchars($error ?? $alert) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="id" value="<?= (int)$step['id'] ?>">

                <div class="form-group">
                    <label for="icon_class">
                        Kelas Ikon Font Awesome
                        <span class="icon-preview">
                            <i class="fa-solid <?= htmlspecialchars($step['icon_class']) ?>"></i>
                        </span>
                    </label>
                    <input type="text" 
                           id="icon_class" 
                           name="icon_class" 
                           class="form-control"
                           value="<?= htmlspecialchars($step['icon_class']) ?>"
                           placeholder="Contoh: fa-whatsapp, fa-truck" 
                           required>
                    <p class="icon-help">
                        ✅ Gunakan hanya nama kelas ikon, misal: <code>fa-cookie-bite</code>, <code>fa-cart-plus</code>
                        <br>🔗 Cari di: <a href="https://fontawesome.com/v6/search?o=r&m=free" target="_blank">fontawesome.com</a>
                    </p>
                </div>

                <div class="form-row" style="display:flex;gap:16px;flex-wrap:wrap">
                    <div style="flex:1;min-width:150px">
                        <div class="form-group">
                            <label for="step_number">Nomor Langkah</label>
                            <input type="number" 
                                   id="step_number" 
                                   name="step_number" 
                                   class="form-control"
                                   value="<?= (int)$step['step_number'] ?>" 
                                   min="1" 
                                   required>
                        </div>
                    </div>
                    <div style="flex:2;min-width:300px">
                        <div class="form-group">
                            <label for="title">Judul Langkah</label>
                            <input type="text" 
                                   id="title" 
                                   name="title" 
                                   class="form-control"
                                   value="<?= htmlspecialchars($step['title']) ?>" 
                                   placeholder="Contoh: Tambah ke Keranjang" 
                                   required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-control" 
                        rows="4"
                        placeholder="Jelaskan langkah secara singkat..."
                        required><?= htmlspecialchars($step['description']) ?></textarea>
                </div>

                <div style="margin-top: 20px; display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
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