<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

// Ambil data cara pesan
$stmtSec = $pdo->query("SELECT title, subtitle FROM cara_pesan_section WHERE id = 1");
$section = $stmtSec->fetch(PDO::FETCH_ASSOC);
if (!$section) {
    $pdo->exec("INSERT INTO cara_pesan_section (title, subtitle) VALUES ('Cara Pesan', 'Mudah dan cepat, hanya dalam 4 langkah')");
    $section = ['title' => 'Cara Pesan', 'subtitle' => 'Mudah dan cepat, hanya dalam 4 langkah'];
}

// Ambil semua steps (aktif & non-aktif, diurutkan)
$stmtSteps = $pdo->query("
    SELECT * FROM cara_pesan_steps 
    ORDER BY sort_order ASC, id ASC
");
$steps = $stmtSteps->fetchAll(PDO::FETCH_ASSOC);

$alert = '';
if (isset($_SESSION['cp_alert'])) {
    $alert = $_SESSION['cp_alert'];
    unset($_SESSION['cp_alert']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cara Pesan — Admin</title>
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
            max-width: 1100px;
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
        .content { padding: 30px; }
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
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .form-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }
        .form-col {
            flex: 1;
            min-width: 300px;
        }
        .form-group {
            margin-bottom: 18px;
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
            padding: 12px 16px;
            border-radius: 10px;
            border: 2px solid #e0d6eb;
            font-size: 1rem;
            transition: var(--transition);
        }
        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.2);
        }
        /* STEP LIST */
        .steps-heading {
            font-size: 1.5rem;
            margin: 30px 0 16px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .steps-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .step-card {
            background: white;
            border: 2px solid #e6e1f0;
            border-radius: 14px;
            padding: 20px;
            position: relative;
            display: flex;
            gap: 16px;
        }
        .step-icon-preview {
            width: 52px;
            height: 52px;
            background: var(--cream);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: var(--primary);
        }
        .step-info {
            flex: 1;
        }
        .step-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .step-number { font-weight: 700; color: var(--primary); font-size: 1.1rem; }
        .step-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #b91c1c; }
        .step-title { font-weight: 600; margin: 4px 0; color: var(--dark); }
        .step-desc { color: #555; font-size: 0.95rem; }
        .step-actions {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
        }
        .btn-sm {
            padding: 8px 14px;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #d05876);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(182, 75, 98, 0.3);
        }
        .btn-gray {
            background: #9e9e9e;
            color: white;
        }
        .btn-gray:hover { 
            background: #757575; 
        }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .no-steps {
            text-align: center;
            padding: 40px 20px;
            color: #777;
            background: var(--cream);
            border-radius: 14px;
        }
        .no-steps i { font-size: 2.5rem; margin-bottom: 16px; color: #ccc; }
        @media (max-width: 768px) {
            .form-row { flex-direction: column; }
            .steps-list { gap: 12px; }
            .step-card { flex-direction: column; }
            .step-actions { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-title">
                <i class="fas fa-list-ol"></i> Cara Pesan
            </div>
            <div class="nav-actions">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </div>
        </header>

        <div class="content">
            <h2 class="section-heading">Kelola Judul & Subjudul</h2>

            <?php if ($alert): ?>
                <div class="alert <?= strpos($alert, 'Gagal') !== false ? 'alert-error' : 'alert-success' ?>">
                    <?= htmlspecialchars($alert) ?>
                </div>
            <?php endif; ?>

            <form action="update_cara_pesan.php" method="POST">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="title">Judul</label>
                            <input type="text" id="title" name="title" class="form-control"
                                   value="<?= htmlspecialchars($section['title']) ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="subtitle">Subjudul</label>
                            <input type="text" id="subtitle" name="subtitle" class="form-control"
                                   value="<?= htmlspecialchars($section['subtitle']) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-actions" style="margin-top: 10px;">
                    <button type="submit" name="update_section" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Judul & Subjudul
                    </button>
                </div>
            </form>

            <h3 class="steps-heading"><i class="fas fa-sticky-note"></i> Langkah-langkah Pesan (<?= count($steps) ?> item)</h3>

            <?php if ($steps): ?>
                <div class="steps-list">
                    <?php foreach ($steps as $step): ?>
                        <div class="step-card">
                            <div class="step-icon-preview">
                                <i class="fa-solid <?= htmlspecialchars($step['icon_class']) ?>"></i>
                            </div>
                            <div class="step-info">
                                <div class="step-meta">
                                    <span class="step-number">Langkah <?= htmlspecialchars($step['step_number']) ?></span>
                                    <span class="step-status <?= $step['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                        <?= $step['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </div>
                                <div class="step-title"><?= htmlspecialchars($step['title']) ?></div>
                                <div class="step-desc"><?= htmlspecialchars(substr($step['description'], 0, 80)) ?><?= strlen($step['description']) > 80 ? '...' : '' ?></div>
                                
                                <div class="step-actions">
                                    <a href="edit-step.php?id=<?= $step['id'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if ($step['is_active']): ?>
                                        <a href="toggle-step.php?id=<?= $step['id'] ?>&action=deactivate" class="btn btn-gray btn-sm"
                                           onclick="return confirm('Nonaktifkan langkah ini? Akan menghilang dari halaman utama.')">
                                            <i class="fas fa-eye-slash"></i> Nonaktifkan
                                        </a>
                                    <?php else: ?>
                                        <a href="toggle-step.php?id=<?= $step['id'] ?>&action=activate" class="btn btn-gray btn-sm">
                                            <i class="fas fa-eye"></i> Aktifkan
                                        </a>
                                    <?php endif; ?>
                                    <a href="delete-step.php?id=<?= $step['id'] ?>" class="btn btn-danger btn-sm"
                                       onclick="return confirm('Hapus langkah ini? Tindakan tidak bisa dibatalkan.')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-steps">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Belum ada langkah. Tambahkan langkah pertama Anda.</p>
                </div>
            <?php endif; ?>

            <div style="margin-top: 30px; text-align: center;">
                <a href="add-step.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Langkah Baru
                </a>
                <a href="index.php" class="btn btn-gray">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>