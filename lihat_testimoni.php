<?php
session_start();
require 'config.php';
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Ambil semua testimoni, diurutkan dari yang terbaru
try {
    $stmt = $pdo->query("SELECT * FROM testimoni ORDER BY dikirim_pada DESC");
    $testimoni = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $testimoni = [];
}

// Pesan sukses/error
$msg = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimoni • Dapoer Funraise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5A46A2;
            --secondary: #B64B62;
            --bg: #f0ecfa;
            --card: #ffffff;
            --text: #333333;
            --border-light: #eae6ff;
            --border-medium: #d8d2f0;
            --border-card: #c9c1e8;
            --shadow: 0 6px 16px rgba(90, 70, 162, 0.12);
            --shadow-hover: 0 8px 24px rgba(90, 70, 162, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
            padding: 1rem;
            line-height: 1.5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 0.5rem;
        }

        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: var(--card);
            padding: 1.1rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
            text-align: center;
            border: 1px solid var(--border-card);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-value {
            font-size: 1.9rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1.1;
        }

        .stat-label {
            font-size: 0.95rem;
            font-weight: 600;
            margin-top: 0.25rem;
            color: #555;
        }

        /* Header */
        .page-header {
            background: var(--card);
            padding: 1.2rem 1.5rem;
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-card);
        }

        .page-header h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.4rem;
        }

        .page-header p {
            font-size: 0.95rem;
            color: #666;
        }

        /* Alert */
        .alert {
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        /* Desktop Table */
        .table-responsive {
            overflow-x: auto;
            margin-bottom: 1.5rem;
        }

        .table-admin {
            width: 100%;
            border-collapse: collapse;
            background: var(--card);
            box-shadow: var(--shadow);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border-card);
        }

        .table-admin th {
            background: #fbf9ff;
            padding: 1rem 1.1rem;
            text-align: left;
            font-weight: 600;
            color: var(--primary);
            font-size: 0.95rem;
            border-bottom: 1px solid var(--border-medium);
        }

        .table-admin td {
            padding: 1rem 1.1rem;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.95rem;
            vertical-align: top;
        }

        .table-admin tr:last-child td {
            border-bottom: none;
        }

        .table-admin tr:hover td {
            background: #fcfbff;
        }

        .no-data {
            text-align: center;
            padding: 2rem;
            color: #777;
            font-style: italic;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.87rem;
            transition: all 0.2s;
        }

        .btn-delete {
            background: #ffe8e8;
            color: var(--secondary);
            border: 1px solid #ffcdd2;
        }

        .btn-delete:hover {
            background: #ffd8d8;
        }

        /* Mobile Cards */
        .testimoni-mobile {
            display: none;
            flex-direction: column;
            gap: 1.2rem;
        }

        .testimoni-card {
            background: var(--card);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid var(--border-card);
            transition: all 0.25s;
        }

        .testimoni-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary);
        }

        .card-header {
            background: #fbf9ff;
            padding: 0.9rem 1.1rem;
            border-bottom: 1px solid var(--border-medium);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-id {
            font-weight: 600;
            color: var(--primary);
        }

        .card-date {
            font-size: 0.8rem;
            color: #777;
        }

        .card-body {
            padding: 1.1rem;
        }

        .row {
            display: flex;
            margin-bottom: 0.6rem;
        }

        .col-label {
            font-weight: 600;
            width: 80px;
            color: #555;
            flex-shrink: 0;
        }

        .col-value {
            flex: 1;
            word-break: break-word;
        }

        .komentar-label {
            font-weight: 600;
            margin-bottom: 0.4rem;
            color: #555;
        }

        .komentar-value {
            background: #fcfbff;
            padding: 0.7rem;
            border-radius: 6px;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-top: 0.3rem;
        }

        .card-footer {
            padding: 0.8rem 1.1rem;
            background: #fbf9ff;
            border-top: 1px solid var(--border-medium);
            text-align: right;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .table-responsive {
                display: none;
            }

            .testimoni-mobile {
                display: flex;
            }

            body {
                padding: 0.8rem;
            }

            .row {
                flex-wrap: wrap;
            }

            .col-label {
                width: auto;
                margin-right: 0.5rem;
            }

            .col-value {
                flex: none;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h2>Daftar Testimoni Masuk</h2>
        <p>Semua testimoni pelanggan yang telah dikirim.</p>
    </div>

    <?php if ($msg): ?>
        <div class="alert">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <!-- Desktop: Table -->
    <div class="table-responsive">
        <table class="table-admin">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tanggal Kirim</th>
                    <th>Nama</th>
                    <th>Nama Produk</th>
                    <th>Komentar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($testimoni)): ?>
                    <?php foreach ($testimoni as $t): ?>
                    <tr>
                        <td><?= (int)$t['id'] ?></td>
                        <td><?= htmlspecialchars(date('d M Y, H:i', strtotime($t['dikirim_pada']))) ?></td>
                        <td><?= htmlspecialchars($t['nama']) ?></td>
                        <td><?= htmlspecialchars($t['nama_produk'] ?? '-') ?></td>
                        <td style="max-width: 300px; word-break: break-word;">
                            <?= nl2br(htmlspecialchars($t['komentar'])) ?>
                        </td>
                        <td>
                            <a href="hapus_testimoni.php?id=<?= (int)$t['id'] ?>"
                               class="btn btn-delete"
                               onclick="return confirm('Yakin ingin menghapus testimoni dari <?= htmlspecialchars($t['nama']) ?>?')">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">Belum ada testimoni yang masuk.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile: Card List -->
    <div class="testimoni-mobile">
        <?php if (!empty($testimoni)): ?>
            <?php foreach ($testimoni as $t): ?>
                <div class="testimoni-card">
                    <div class="card-header">
                        <div class="card-id">#<?= (int)$t['id'] ?></div>
                        <div class="card-date"><?= htmlspecialchars(date('d M Y', strtotime($t['dikirim_pada']))) ?></div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <span class="col-label">Nama:</span>
                            <span class="col-value"><?= htmlspecialchars($t['nama']) ?></span>
                        </div>
                        <div class="row">
                            <span class="col-label">Produk:</span>
                            <span class="col-value"><?= htmlspecialchars($t['nama_produk'] ?? '-') ?></span>
                        </div>
                        <div>
                            <div class="komentar-label">Komentar:</div>
                            <div class="komentar-value">
                                <?= nl2br(htmlspecialchars($t['komentar'])) ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="hapus_testimoni.php?id=<?= (int)$t['id'] ?>"
                           class="btn btn-delete"
                           onclick="return confirm('Yakin ingin menghapus testimoni dari <?= htmlspecialchars($t['nama']) ?>?')">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="testimoni-card">
                <div class="card-body" style="text-align: center; padding: 2rem;">
                    <i class="fas fa-comment" style="font-size: 2.5rem; color: #ddd; margin-bottom: 1rem;"></i>
                    <p style="color: #777;">Belum ada testimoni yang masuk.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>