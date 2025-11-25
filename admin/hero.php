<?php
session_start();
require_once '../config.php';

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

// Ambil data hero saat ini
$stmt = $pdo->query("SELECT * FROM hero_section WHERE id = 1");
$hero = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika belum ada record, buat default
if (!$hero) {
    $defaultData = [
        'background_path' => 'assets/bg.jpg',
        'cta_button_text' => 'Lihat Produk'
    ];
    $stmtIns = $pdo->prepare("
        INSERT INTO hero_section (background_path, cta_button_text) 
        VALUES (?, ?)
    ");
    $stmtIns->execute([$defaultData['background_path'], $defaultData['cta_button_text']]);
    $hero = $defaultData;
}

$alert = '';
if (isset($_SESSION['hero_alert'])) {
    $alert = $_SESSION['hero_alert'];
    unset($_SESSION['hero_alert']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hero Section — Admin</title>
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
            --transition: all 0.3s ease;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-main);
            background-color: #f9f7fc;
            color: #333;
            padding: 20px;
        }
        .container {
            max-width: 900px;
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
        .header-title { font-size: 1.5rem; font-weight: 600; }
        .header-title i { margin-right: 8px; }
        .nav-actions a {
            color: white;
            background: rgba(255,255,255,0.2);
            padding: 8px 16px;
            border-radius: 10px;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
        }
        .nav-actions a:hover { background: rgba(255,255,255,0.3); }
        .content {
            padding: 30px;
        }
        .section-heading {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--dark);
        }
        .alert {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: left;
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
        .form-group {
            margin-bottom: 24px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 1.05rem;
        }
        .form-control {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 2px solid #e0d6eb;
            font-size: 1.05rem;
            transition: var(--transition);
            background: white;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.2);
        }
        .form-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .form-col {
            flex: 1;
            min-width: 250px;
        }
        .preview-box {
            background: #faf9fc;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            margin-top: 10px;
        }
        .preview-box img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            object-fit: cover;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.05rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-align: center;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #d05876);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(182, 75, 98, 0.3);
        }
        .btn-gray {
            background: #9e9e9e;
            color: white;
        }
        .btn-gray:hover {
            background: #757575;
        }
        .actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        @media (max-width: 600px) {
            .form-row { flex-direction: column; }
            .actions { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-title">
                <i class="fas fa-home"></i> Hero Section
            </div>
            <div class="nav-actions">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </div>
        </header>

        <div class="content">
            <h2 class="section-heading">Kelola Tampilan Beranda</h2>

            <?php if ($alert): ?>
                <div class="alert <?= strpos($alert, 'Gagal') !== false ? 'alert-error' : 'alert-success' ?>">
                    <?= htmlspecialchars($alert) ?>
                </div>
            <?php endif; ?>

            <form action="update_hero.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="cta_button_text">Teks Tombol CTA</label>
                    <input type="text" 
                           id="cta_button_text" 
                           name="cta_button_text" 
                           class="form-control"
                           value="<?= htmlspecialchars($hero['cta_button_text']) ?>"
                           placeholder="Contoh: Lihat Produk"
                           required>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="background_image">Ganti Gambar Latar (Opsional)</label>
                            <input type="file" 
                                   id="background_image" 
                                   name="background_image" 
                                   class="form-control"
                                   accept="image/jpeg,image/png,image/webp">
                            <small style="display:block; margin-top:6px; color:#666;">
                                Format: JPG, PNG, WebP (Maks. 2 MB)
                            </small>
                        </div>
                    </div>
                    <div class="form-col">
                        <label>Gambar Saat Ini</label>
                        <div class="preview-box">
                            <?php
                            $bgPath = $hero['background_path'];
                            $bgUrl = (filter_var($bgPath, FILTER_VALIDATE_URL)) ? $bgPath : '../' . $bgPath;
                            ?>
                            <img src="<?= htmlspecialchars($bgUrl) ?>" alt="Background saat ini">
                            <p style="margin-top:8px; font-size:0.9rem; color:#555;">
                                <?= basename($bgPath) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" name="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="index.php" class="btn btn-gray">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>