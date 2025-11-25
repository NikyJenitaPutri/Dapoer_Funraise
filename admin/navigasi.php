<?php
session_start();
require '../config.php';

// Pastikan admin login
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

// Ambil semua link
$stmt = $pdo->query("SELECT * FROM navigation_links ORDER BY sort_order ASC, id ASC");
$navLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle POST: tambah / update / delete
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'add') {
                $label = trim($_POST['label'] ?? '');
                $href = trim($_POST['href'] ?? '');
                $sort_order = intval($_POST['sort_order'] ?? 0);
                if ($label && $href) {
                    $stmt = $pdo->prepare("INSERT INTO navigation_links (label, href, sort_order, is_active) VALUES (?, ?, ?, 1)");
                    $stmt->execute([$label, $href, $sort_order]);
                    $message = 'Tautan navigasi berhasil ditambahkan.';
                    $messageType = 'success';
                } else {
                    throw new Exception('Label dan href wajib diisi.');
                }
            } elseif ($action === 'update') {
                $id = intval($_POST['id']);
                $label = trim($_POST['label'] ?? '');
                $href = trim($_POST['href'] ?? '');
                $sort_order = intval($_POST['sort_order'] ?? 0);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                if ($id && $label && $href) {
                    $stmt = $pdo->prepare("UPDATE navigation_links SET label = ?, href = ?, sort_order = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$label, $href, $sort_order, $is_active, $id]);
                    $message = 'Tautan navigasi berhasil diperbarui.';
                    $messageType = 'success';
                } else {
                    throw new Exception('Data tidak lengkap.');
                }
            } elseif ($action === 'delete') {
                $id = intval($_POST['id']);
                if ($id) {
                    $stmt = $pdo->prepare("DELETE FROM navigation_links WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'Tautan navigasi berhasil dihapus.';
                    $messageType = 'success';
                }
            }
        }
        // Reload setelah sukses
        if ($messageType === 'success') {
            header("Location: nav_edit.php?msg=" . urlencode($message) . "&type=success");
            exit;
        }

    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Ambil pesan dari redirect
if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
    $messageType = $_GET['type'] ?? 'info';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Kelola Navigasi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #B64B62;
            --secondary: #5A46A2;
            --accent: #F9CC22;
            --cream: #FFF5EE;
            --dark: #2a1f3d;
            --shadow: 0 4px 12px rgba(90, 70, 162, 0.15);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f7fc;
            color: #333;
            padding: 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        header {
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            color: white;
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
        }
        .btn {
            padding: 10px 20px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background: #d05876;
            transform: translateY(-2px);
        }
        .btn-outline {
            background: transparent;
            border: 2px solid var(--secondary);
            color: var(--secondary);
        }
        .btn-outline:hover {
            background: var(--secondary);
            color: white;
        }
        .btn-danger {
            background: #e53e3e;
            color: white;
        }
        .btn-danger:hover {
            background: #c53030;
        }
        .alert {
            padding: 14px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
        }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #059669; }
        .alert-error { background: #fee2e2; color: #b91c1c; border-left: 4px solid #dc2626; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        th, td {
            padding: 16px;
            text-align: left;
        }
        th {
            background: var(--secondary);
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) { background-color: #faf9fd; }
        tr:hover { background-color: #f3f0fa; }
        .form-row {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 12px;
            align-items: center;
            margin-bottom: 14px;
        }
        .form-row label {
            font-weight: 600;
            color: var(--dark);
        }
        .form-row input, .form-row select {
            padding: 10px 14px;
            border: 2px solid #e2ddef;
            border-radius: 8px;
            font-size: 1rem;
        }
        .form-row input:focus, .form-row select:focus {
            outline: none;
            border-color: var(--secondary);
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .toggle-active {
            display: inline-block;
            width: 48px;
            height: 24px;
            background: #e2e8f0;
            border-radius: 12px;
            position: relative;
            cursor: pointer;
        }
        .toggle-active::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: all 0.3s;
        }
        .toggle-active.active {
            background: var(--primary);
        }
        .toggle-active.active::after {
            transform: translateX(24px);
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow);
        }
        .card h2 {
            margin-bottom: 20px;
            color: var(--secondary);
            font-weight: 700;
        }
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                flex-direction: column;
            }
            th, td {
                padding: 12px 8px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>KELOLA NAVIGASI</h1>
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>"><?= $message ?></div>
        <?php endif; ?>

        <!-- Form Tambah -->
        <div class="card">
            <h2>Tambah Tautan Navigasi Baru</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <label for="label">Label</label>
                    <input type="text" id="label" name="label" required placeholder="Contoh: Beranda">
                </div>
                <div class="form-row">
                    <label for="href">Href (Anchor)</label>
                    <input type="text" id="href" name="href" required placeholder="Contoh: #beranda">
                </div>
                <div class="form-row">
                    <label for="sort_order">Urutan</label>
                    <input type="number" id="sort_order" name="sort_order" value="<?= count($navLinks) + 1 ?>" min="0" required>
                </div>
                <div class="form-row">
                    <label></label>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Tautan
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar Link -->
        <div class="card">
            <h2>Daftar Tautan Navigasi</h2>
            <?php if (empty($navLinks)): ?>
                <p style="text-align:center; color:#777; padding:20px;">Belum ada tautan navigasi.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Label</th>
                            <th>Href</th>
                            <th>Urutan</th>
                            <th>Aktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($navLinks as $link): ?>
                        <tr>
                            <td><?= $link['id'] ?></td>
                            <td><?= htmlspecialchars($link['label']) ?></td>
                            <td><?= htmlspecialchars($link['href']) ?></td>
                            <td><?= $link['sort_order'] ?></td>
                            <td>
                                <span class="toggle-active <?= $link['is_active'] ? 'active' : '' ?>">
                                </span>
                            </td>
                            <td class="action-buttons">
                                <!-- Tombol Edit: buka form inline -->
                                <button type="button" class="btn btn-outline" onclick="document.getElementById('edit-<?= $link['id'] ?>').style.display='block'">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus tautan ini?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $link['id'] ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <!-- Form Edit Inline (sembunyi default) -->
                        <tr id="edit-<?= $link['id'] ?>" style="display:none; background:#fdf8f0;">
                            <td colspan="6">
                                <div class="card" style="margin:16px 0; padding:16px;">
                                    <h3 style="margin-bottom:12px;">Edit: <?= htmlspecialchars($link['label']) ?></h3>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?= $link['id'] ?>">
                                        <div class="form-row">
                                            <label>Label</label>
                                            <input type="text" name="label" value="<?= htmlspecialchars($link['label']) ?>" required>
                                        </div>
                                        <div class="form-row">
                                            <label>Href</label>
                                            <input type="text" name="href" value="<?= htmlspecialchars($link['href']) ?>" required>
                                        </div>
                                        <div class="form-row">
                                            <label>Urutan</label>
                                            <input type="number" name="sort_order" value="<?= $link['sort_order'] ?>" min="0" required>
                                        </div>
                                        <div class="form-row">
                                            <label>Aktif?</label>
                                            <label style="display:flex;align-items:center;gap:8px;">
                                                <input type="checkbox" name="is_active" <?= $link['is_active'] ? 'checked' : '' ?>>
                                                Ya
                                            </label>
                                        </div>
                                        <div class="form-row">
                                            <label></label>
                                            <div class="action-buttons">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save"></i> Simpan Perubahan
                                                </button>
                                                <button type="button" class="btn btn-outline" onclick="this.closest('tr').style.display='none'">
                                                    <i class="fas fa-times"></i> Batal
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-hide success message after 5 detik
        const alerts = document.querySelectorAll('.alert-success');
        alerts.forEach(el => setTimeout(() => el.style.display = 'none', 5000));
    </script>
</body>
</html>