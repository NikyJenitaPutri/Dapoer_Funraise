<?php
session_start();
require_once '../config.php';

// === DATA SECTION ===
$stmtSec = $pdo->prepare("SELECT title, subtitle FROM kontak_section WHERE id = 1");
$stmtSec->execute();
$kontak_section = $stmtSec->fetch(PDO::FETCH_ASSOC);
if (!$kontak_section) {
    $pdo->exec("INSERT INTO kontak_section (title, subtitle) VALUES ('Hubungi Kami', 'Siap melayani pesanan Anda dengan senang hati')");
    $kontak_section = ['title' => 'Hubungi Kami', 'subtitle' => 'Siap melayani pesanan Anda dengan senang hati'];
}

// === DAFTAR CARD ===
$stmtCards = $pdo->prepare("
    SELECT * FROM contact_cards 
    ORDER BY sort_order ASC, id ASC
");
$stmtCards->execute();
$contact_cards = $stmtCards->fetchAll(PDO::FETCH_ASSOC);

// === ALERT ===
$alert = '';
if (isset($_SESSION['kontak_alert'])) {
    $alert = $_SESSION['kontak_alert'];
    unset($_SESSION['kontak_alert']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak — Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #B64B62;
            --secondary: #5A46A2;
            --accent: #F9CC22;
            --cream: #FFF5EE;
            --dark: #2a1f3d;
            --gray-btn: #6c757d;
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
        /* CARD LIST */
        .cards-heading {
            font-size: 1.5rem;
            margin: 30px 0 16px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .cards-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .card-item {
            background: white;
            border: 2px solid #e6e1f0;
            border-radius: 14px;
            padding: 20px;
            position: relative;
            transition: var(--transition);
        }
        .card-item:hover {
            box-shadow: 0 6px 16px rgba(0,0,0,0.08);
            transform: translateY(-4px);
        }
        .card-icon-preview {
            width: 50px;
            height: 50px;
            background: var(--cream);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 1.3rem;
            color: var(--primary);
        }
        .card-title { font-weight: 700; margin: 4px 0; color: var(--dark); }
        .card-label { font-size: 0.95rem; color: #555; margin: 4px 0; }
        .card-href { 
            font-size: 0.85rem; 
            color: #777; 
            margin: 4px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 12px 0 16px;
            font-size: 0.85rem;
        }
        .card-order { font-weight: 600; color: var(--primary); }
        .card-status {
            padding: 3px 8px;
            border-radius: 16px;
            font-weight: 600;
        }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #b91c1c; }
        .card-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.92rem;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            text-decoration: none;
            text-align: center;
        }
        .btn-sm { padding: 6px 12px; font-size: 0.875rem; }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #d05876);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(182, 75, 98, 0.3);
        }
        .btn-gray {
            background: var(--gray-btn);
            color: white;
        }
        .btn-gray:hover { background: #5a6268; }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-warning:hover { background: #e0a800; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .no-cards {
            text-align: center;
            padding: 40px 20px;
            color: #777;
            background: var(--cream);
            border-radius: 14px;
        }
        .no-cards i { font-size: 2.5rem; margin-bottom: 16px; color: #ccc; }
        @media (max-width: 768px) {
            .form-row { flex-direction: column; }
            .cards-list { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-title">
                <i class="fas fa-address-book"></i> Kontak
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

            <form action="update_kontak_section.php" method="POST">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="title">Judul</label>
                            <input type="text" id="title" name="title" class="form-control"
                                   value="<?= htmlspecialchars($kontak_section['title']) ?>" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="subtitle">Subjudul</label>
                            <input type="text" id="subtitle" name="subtitle" class="form-control"
                                   value="<?= htmlspecialchars($kontak_section['subtitle']) ?>" required>
                        </div>
                    </div>
                </div>
                <button type="submit" name="update_section" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>

            <h3 class="cards-heading">
                <i class="fas fa-id-card"></i> Daftar Kontak (<?= count($contact_cards) ?> item)
            </h3>

            <?php if ($contact_cards): ?>
                <div class="cards-list">
                    <?php foreach ($contact_cards as $card): ?>
                        <div class="card-item">
                            <div class="card-icon-preview">
                                <i class="fa <?= htmlspecialchars($card['icon_class']) ?>"></i>
                            </div>
                            <div class="card-title"><?= htmlspecialchars($card['title']) ?></div>
                            <div class="card-label"><?= htmlspecialchars($card['label']) ?></div>
                            <div class="card-href"><?= htmlspecialchars($card['href']) ?></div>
                            <div class="card-meta">
                                <span class="card-order">Urutan: <?= $card['sort_order'] ?></span>
                                <span class="card-status <?= $card['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                    <?= $card['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                </span>
                            </div>
                            <div class="card-actions">
                                <a href="edit-contact-card.php?id=<?= $card['id'] ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <?php if ($card['is_active']): ?>
                                    <form method="POST" action="toggle-contact-card.php" style="display:inline;"
                                          onsubmit="return confirm('Nonaktifkan kontak ini? Tidak tampil di halaman utama.')">
                                        <input type="hidden" name="id" value="<?= $card['id'] ?>">
                                        <input type="hidden" name="action" value="deactivate">
                                        <button type="submit" class="btn btn-warning btn-sm">
                                            <i class="fas fa-eye-slash"></i> Nonaktifkan
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="toggle-contact-card.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $card['id'] ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fas fa-eye"></i> Aktifkan
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" action="delete-contact-card.php" style="display:inline;"
                                      onsubmit="return confirm('Hapus kontak ini? Tindakan tidak bisa dibatalkan.')">
                                    <input type="hidden" name="id" value="<?= $card['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-cards">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Belum ada kontak. Tambahkan kontak pertama Anda.</p>
                </div>
            <?php endif; ?>

            <div style="margin-top: 30px; text-align: center;">
                <a href="add-contact-card.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Kontak Baru
                </a>
                <a href="index.php" class="btn btn-gray">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>