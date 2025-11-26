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
$alertType = 'success';
if (isset($_SESSION['cp_alert'])) {
    $alert = $_SESSION['cp_alert'];
    $alertType = strpos($_SESSION['cp_alert'], 'Gagal') !== false ? 'error' : 'success';
    unset($_SESSION['cp_alert']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cara Pesan — Dapoer Funraise</title>
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

        .steps-box {
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
            .steps-box {
                width: 100%;
                max-width: 100%;
            }
        }

        .form-header, .steps-header {
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
        .steps-header { color: var(--secondary); }

        .form-body, .steps-body {
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

        input[type="text"] {
            width: 100%;
            padding: 11px 15px;
            border: 2px solid #e8e6f2;
            border-radius: 10px;
            font-size: 0.93rem;
            background: #faf9ff;
            font-family: inherit;
            transition: all 0.2s;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.1);
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

        .btn-secondary, .btn-gray {
            background: linear-gradient(135deg, var(--soft), #c8a5d0);
            color: var(--primary);
            flex: 1;
        }

        .btn-secondary:hover, .btn-gray:hover {
            background: linear-gradient(135deg, #d0a8d5, #c095cb);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid #e8e6f2;
        }

        .btn-outline:hover {
            background: #faf9ff;
            border-color: var(--primary);
        }

        .btn-danger {
            background: #f9d9d9;
            color: #d32f2f;
            border: 1px solid #f1b7b7;
        }

        .btn-danger:hover {
            background: #f5baba;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
            min-height: auto;
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

        .step-card {
            background: #faf9ff;
            border: 1px solid #f0eaff;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 14px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .step-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--soft);
            color: var(--primary);
            border-radius: 50%;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .step-info {
            flex: 1;
            min-width: 0;
        }

        .step-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 0.83rem;
        }

        .step-number { font-weight: 600; color: var(--primary); }
        .step-status { font-weight: 500; }
        .status-active { color: #2e7d32; }
        .status-inactive { color: var(--text-muted); }

        .step-title {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 3px;
            font-size: 0.95rem;
        }

        .step-desc {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 10px;
            line-height: 1.4;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .step-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .no-steps {
            text-align: center;
            padding: 30px 10px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .no-steps i {
            font-size: 2rem;
            margin-bottom: 12px;
            color: #dcd6f7;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="form-box">
            <div class="form-header">
                <i class="fas fa-heading" style="color: var(--secondary);"></i>
                Judul & Subjudul
            </div>

            <div class="form-body">
                <?php if ($alert): ?>
                    <div class="alert <?= $alertType === 'success' ? 'alert-success' : '' ?>">
                        <i class="fas fa-<?= $alertType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                        <?= htmlspecialchars($alert) ?>
                    </div>
                <?php endif; ?>

                <form action="update_cara_pesan.php" method="POST">
                    <div class="row">
                        <div class="form-group">
                            <label for="title">Judul</label>
                            <input 
                                type="text" 
                                id="title" 
                                name="title"
                                value="<?= htmlspecialchars($section['title']) ?>"
                                required
                                placeholder="Contoh: Cara Pesan"
                            >
                        </div>

                        <div class="form-group">
                            <label for="subtitle">Subjudul</label>
                            <input 
                                type="text" 
                                id="subtitle" 
                                name="subtitle"
                                value="<?= htmlspecialchars($section['subtitle']) ?>"
                                required
                                placeholder="Contoh: Mudah dan cepat..."
                            >
                        </div>
                    </div>

                    <button type="submit" name="update_section" class="btn btn-primary" style="width:100%; margin-top:0.5rem;">
                        <i class="fas fa-save"></i> Simpan Judul & Subjudul
                    </button>
                </form>
            </div>

            <div class="action-bar">
                <a href="../pengaturan.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="add-step.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Langkah Baru
                </a>
            </div>
        </div>

        <div class="steps-box">
            <div class="steps-header">
                <i class="fas fa-list-ol"></i>
                Langkah-langkah (<?= count($steps) ?> item)
            </div>

            <div class="steps-body">
                <?php if ($steps): ?>
                    <?php foreach ($steps as $step): ?>
                        <div class="step-card">
                            <div class="step-icon">
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
                                <div class="step-desc"><?= htmlspecialchars($step['description']) ?></div>
                                <div class="step-actions">
                                    <a href="edit-step.php?id=<?= $step['id'] ?>" class="btn btn-outline btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <?php if ($step['is_active']): ?>
                                        <a href="toggle-step.php?id=<?= $step['id'] ?>&action=deactivate" 
                                           class="btn btn-gray btn-sm"
                                           onclick="return confirm('Nonaktifkan langkah ini? Akan menghilang dari halaman utama.')">
                                            <i class="fas fa-eye-slash"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="toggle-step.php?id=<?= $step['id'] ?>&action=activate" 
                                           class="btn btn-gray btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="delete-step.php?id=<?= $step['id'] ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Hapus langkah ini? Tindakan tidak bisa dibatalkan.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-steps">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Belum ada langkah.</p>
                        <a href="add-step.php" class="btn btn-primary" style="margin-top:12px; width:100%;">
                            <i class="fas fa-plus"></i> Tambah Langkah
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>