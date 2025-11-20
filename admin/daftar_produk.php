<?php
include "../config.php"; 
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: ../login.php');
    exit;
}

// Pagination
$perPage = 5;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$searchTerm = trim($_GET['search'] ?? '');

// Query produk
try {
    if ($searchTerm !== '') {
        $sql = "SELECT * FROM produk 
                WHERE ID LIKE ? OR Nama LIKE ? 
                ORDER BY ID DESC 
                LIMIT ?, ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["%$searchTerm%", "%$searchTerm%", $offset, $perPage]);
    } else {
        $sql = "SELECT * FROM produk ORDER BY ID DESC LIMIT ?, ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$offset, $perPage]);
    }
    $produk = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hitung total
    $countSql = $searchTerm !== '' 
        ? "SELECT COUNT(*) FROM produk WHERE ID LIKE ? OR Nama LIKE ?"
        : "SELECT COUNT(*) FROM produk";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($searchTerm !== '' ? ["%$searchTerm%", "%$searchTerm%"] : []);
    $totalData = (int)$countStmt->fetchColumn();
    $totalPage = ceil($totalData / $perPage);
} catch (Exception $e) {
    $produk = [];
    $totalData = 0;
    $totalPage = 0;
}

$msg = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk • Dapoer Funraise</title>
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

        /* === SATU SECTION: Header + Search (tanpa garis & tanpa emoji) === */
        .header-section {
            background: var(--card);
            border-radius: 14px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .header-content {
            padding: 1.2rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-title h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }

        .header-title p {
            font-size: 0.95rem;
            color: #666;
            margin-top: 0.3rem;
        }

        .header-actions {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.65rem 1.4rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.25s;
        }

        .btn-primary:hover {
            background: #4a3a8a;
            transform: translateY(-1px);
        }

        /* Search Form */
        .search-container {
            padding: 1.2rem 1.5rem;
        }

        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 0.8rem;
            align-items: flex-end;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 0.65rem 1rem;
            border: 1px solid var(--border-medium);
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.15);
        }

        .search-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.65rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 0.95rem;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
        }

        .btn-search {
            background: var(--primary);
            color: white;
        }

        .btn-search:hover {
            background: #4a3a8a;
            transform: translateY(-1px);
        }

        .btn-reset {
            background: #f0f0f0;
            color: #555;
        }

        .btn-reset:hover {
            background: #e0e0e0;
        }

        /* Alert */
        .alert {
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            background: #e8f5e9;
            color: #2e7d32;
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
        }

        .table-admin th {
            background: #fbf9ff;
            padding: 1rem 1.1rem;
            text-align: left;
            font-weight: 600;
            color: var(--primary);
            font-size: 0.95rem;
        }

        .table-admin td {
            padding: 1rem 1.1rem;
            font-size: 0.95rem;
            vertical-align: middle;
        }

        .table-admin tr:hover td {
            background: #fcfbff;
        }

        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            background: #f8f6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
        }

        .badge {
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .no-data {
            text-align: center;
            padding: 2.5rem;
            color: #777;
            font-style: italic;
        }

        /* Action Buttons — shared */
        .btn-group {
            display: flex;
            gap: 0.5rem;
        }

        .btn-info {
            background: #e3f2fd;
            color: #1565c0;
        }

        .btn-info:hover {
            background: #d0e8fd;
        }

        .btn-warning {
            background: #fff8e1;
            color: #ef6c00;
        }

        .btn-warning:hover {
            background: #fff0c4;
        }

        .btn-danger {
            background: #ffe8e8;
            color: var(--secondary);
        }

        .btn-danger:hover {
            background: #ffd8d8;
        }

        /* Mobile Cards */
        .produk-mobile {
            display: none;
            flex-direction: column;
            gap: 1.2rem;
        }

        .produk-card {
            background: var(--card);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.25s;
        }

        .produk-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .card-header {
            padding: 0.9rem 1.1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fbf9ff;
        }

        .card-id {
            font-weight: 600;
            color: var(--primary);
        }

        .card-actions {
            display: flex;
            gap: 0.4rem;
        }

        .card-body {
            padding: 1.1rem;
            display: flex;
            gap: 1rem;
        }

        .card-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            background: #f8f6ff;
            flex-shrink: 0;
        }

        .card-info {
            flex: 1;
        }

        .card-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.4rem;
            color: var(--text);
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-top: 0.8rem;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 0.8rem;
            color: #777;
        }

        .meta-value {
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.3rem;
            margin-top: 1.2rem;
        }

        .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: var(--card);
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .page-link:hover:not(.disabled) {
            background: var(--primary);
            color: white;
        }

        .page-link.active {
            background: var(--primary);
            color: white;
        }

        .page-link.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .header-actions {
                width: 100%;
                justify-content: center;
            }

            .search-form {
                flex-direction: column;
                align-items: stretch;
            }

            .table-responsive {
                display: none;
            }

            .produk-mobile {
                display: flex;
            }

            .btn-group {
                flex-wrap: wrap;
            }

            .card-body {
                flex-direction: column;
            }

            .card-img {
                width: 100%;
                height: 140px;
                border-radius: 8px 8px 0 0;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- SATU SECTION: Header + Search (tanpa garis, tanpa emoji) -->
    <div class="header-section">
        <div class="header-content">
            <div class="header-title">
                <h2>Kelola Produk</h2>
                <p>Daftar semua produk yang tersedia.</p>
            </div>
            <div class="header-actions">
                <a href="../tambah_produk.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Tambah Produk
                </a>
            </div>
        </div>
        <div class="search-container">
            <form method="GET" class="search-form">
                <input type="text" name="search" class="search-input"
                       placeholder="Cari berdasarkan ID atau nama produk"
                       value="<?= htmlspecialchars($searchTerm) ?>">
                <div class="search-buttons">
                    <button type="submit" class="btn btn-search">
                        <i class="fas fa-search"></i> Cari
                    </button>
                    <button type="button" class="btn btn-reset" onclick="resetSearch()">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
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
                    <th>Foto</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Upload</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($produk)): ?>
                    <?php foreach ($produk as $p): ?>
                    <tr>
                        <td><?= (int)($p['ID'] ?? 0) ?></td>
                        <td>
                            <?php if (!empty($p['Foto_Produk']) && file_exists(__DIR__ . '/../uploads/' . $p['Foto_Produk'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($p['Foto_Produk']) ?>" 
                                     alt="<?= htmlspecialchars($p['Nama'] ?? '') ?>" 
                                     class="product-img">
                            <?php else: ?>
                                <span class="product-img"><i class="fas fa-image"></i></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['Nama'] ?? '-') ?></td>
                        <td>
                            <span class="badge badge-success">
                                Rp <?= number_format($p['Harga'] ?? 0, 0, ',', '.') ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($p['created_at'] ?? '-') ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="../detail_produk.php?id=<?= (int)($p['ID'] ?? 0) ?>" 
                                   class="btn btn-info" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="../edit_produk.php?id=<?= (int)($p['ID'] ?? 0) ?>" 
                                   class="btn btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="../hapus_produk.php?id=<?= (int)($p['ID'] ?? 0) ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="no-data">
                            Tidak ada produk ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile: Card List -->
    <div class="produk-mobile">
        <?php if (!empty($produk)): ?>
            <?php foreach ($produk as $p): ?>
                <div class="produk-card">
                    <div class="card-header">
                        <div class="card-id">#<?= (int)($p['ID'] ?? 0) ?></div>
                        <div class="card-actions">
                            <a href="../detail_produk.php?id=<?= (int)($p['ID'] ?? 0) ?>" 
                               class="btn btn-info" title="Detail" style="padding:4px 8px;">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="../edit_produk.php?id=<?= (int)($p['ID'] ?? 0) ?>" 
                               class="btn btn-warning" title="Edit" style="padding:4px 8px;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="../hapus_produk.php?id=<?= (int)($p['ID'] ?? 0) ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Yakin ingin menghapus produk ini?')"
                               style="padding:4px 8px;">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($p['Foto_Produk']) && file_exists(__DIR__ . '/../uploads/' . $p['Foto_Produk'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($p['Foto_Produk']) ?>" 
                                 alt="<?= htmlspecialchars($p['Nama'] ?? '') ?>" 
                                 class="card-img">
                        <?php else: ?>
                            <div class="card-img" style="display:flex;align-items:center;justify-content:center;color:#aaa;">
                                <i class="fas fa-image fa-2x"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-info">
                            <div class="card-name"><?= htmlspecialchars($p['Nama'] ?? '-') ?></div>
                            <div class="card-meta">
                                <div class="meta-item">
                                    <span class="meta-label">Harga</span>
                                    <span class="meta-value">Rp <?= number_format($p['Harga'] ?? 0, 0, ',', '.') ?></span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">Upload</span>
                                    <span class="meta-value"><?= htmlspecialchars(substr($p['created_at'] ?? '-', 0, 10)) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="produk-card">
                <div class="card-body" style="text-align:center;padding:2rem;">
                    <i class="fas fa-box-open" style="font-size:2.5rem;color:#ddd;margin-bottom:1rem;"></i>
                    <p style="color:#777;">Tidak ada produk ditemukan.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPage > 1): ?>
    <div class="pagination">
        <a href="?page=<?= max(1, $page - 1) ?><?= $searchTerm ? '&search=' . urlencode($searchTerm) : '' ?>" 
           class="page-link <?= $page <= 1 ? 'disabled' : '' ?>">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php for ($i = 1; $i <= $totalPage; $i++): ?>
            <a href="?page=<?= $i ?><?= $searchTerm ? '&search=' . urlencode($searchTerm) : '' ?>" 
               class="page-link <?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        <a href="?page=<?= min($totalPage, $page + 1) ?><?= $searchTerm ? '&search=' . urlencode($searchTerm) : '' ?>" 
           class="page-link <?= $page >= $totalPage ? 'disabled' : '' ?>">
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>
    <?php endif; ?>
</div>

<script>
function resetSearch() {
    const url = new URL(window.location);
    url.searchParams.delete('search');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}
</script>

</body>
</html>