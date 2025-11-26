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
            --shadow: 0 2px 6px rgba(90,70,162,0.04);
            --shadow-hover: 0 4px 10px rgba(90,70,162,0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* 🔷 FULL-WIDTH RESET */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f1e8fdff;
            font-size: 0.8125rem; /* 13px — padat */
            line-height: 1.4;
            color: var(--text);
        }

        /* 🔷 WRAPPER FULL */
        .page-wrapper {
            width: 100vw;
            max-width: 100%;
            margin: 0;
            padding: 0;
        }

        /* 🔷 HEADER */
        .header-section {
            background: var(--card);
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .header-content {
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        .header-title h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
        }

        .header-title p {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.2rem;
        }

        .header-actions {
            display: flex;
            gap: 0.6rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 0.9rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: #4a3a8a;
            transform: translateY(-1px);
        }

        /* 🔷 SEARCH */
        .search-container {
            padding: 0.75rem 1rem;
            background: #fbf9ff;
        }

        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            align-items: flex-end;
        }

        .search-input {
            flex: 1;
            min-width: 220px;
            padding: 0.5rem 0.8rem;
            border: 1px solid var(--border-medium);
            border-radius: 6px;
            font-size: 0.9rem;
            font-family: inherit;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(90,70,162,0.1);
        }

        .search-buttons {
            display: flex;
            gap: 0.4rem;
        }

        .btn {
            padding: 0.5rem 0.9rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            border: none;
            text-decoration: none;
        }

        .btn-search {
            background: var(--primary);
            color: white;
        }

        .btn-search:hover {
            background: #4a3a8a;
        }

        .btn-reset {
            background: #f0f0f0;
            color: #555;
        }

        .btn-reset:hover {
            background: #e0e0e0;
        }

        /* 🔷 ALERT */
        .alert {
            padding: 0.6rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-weight: 600;
            background: #e8f5e9;
            color: #2e7d32;
            font-size: 0.875rem;
        }

        /* 🔷 TABLE */
        .table-responsive {
            overflow-x: auto;
            margin-bottom: 1rem;
        }

        .table-admin {
            width: 100%;
            border-collapse: collapse;
            background: var(--card);
            box-shadow: var(--shadow);
            border-radius: 0px 0px 8px 8px;
            overflow: hidden;
        }

        .table-admin th {
            background: #fbf9ff;
            padding: 0.65rem 0.8rem;
            text-align: center;
            font-weight: 600;
            color: var(--primary);
            font-size: 0.875rem;
        }

        .table-admin td {
            padding: 0.65rem 0.8rem;
            font-size: 0.875rem;
            vertical-align: middle;
            text-align: center;
        }

        .table-admin tr:hover td {
            background: #fcfbff;
        }

        .product-img {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 4px;
            background: #f8f6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 0.8rem;
            margin: 0 auto;
        }

        .badge {
            padding: 0.2rem 0.45rem;
            border-radius: 4px;
            font-size: 0.78rem;
            font-weight: 600;
            margin: 0 auto;
            justify-content: center;
        }

        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .no-data {
            text-align: center;
            padding: 2rem;
            color: #777;
            font-style: italic;
            font-size: 0.9rem;
        }

        .btn-group {
            flex-wrap: wrap;
            gap: 0.35rem;;
            justify-content: center;
        }

        .btn-info { background: #e3f2fd; color: #1565c0; padding: 0.4rem 0.7rem; }
        .btn-warning { background: #fff8e1; color: #ef6c00; padding: 0.4rem 0.7rem; }
        .btn-danger { background: #ffe8e8; color: var(--secondary); padding: 0.4rem 0.7rem; }

        /* 🔷 MOBILE CARDS */
        .produk-mobile {
            display: none;
            flex-direction: column;
            gap: 1rem;
        }

        .produk-card {
            background: var(--card);
            border-radius: 8px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.2s;
        }

        .produk-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .card-header {
            padding: 0.65rem 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fbf9ff;
            font-size: 0.85rem;
        }

        .card-id {
            font-weight: 600;
            color: var(--primary);
        }

        .card-actions .btn {
            padding: 0.35rem 0.6rem;
            font-size: 0.8rem;
        }

        .card-body {
            padding: 0.65rem 0.9rem;
            display: flex;
            gap: 0.8rem;
        }

        .card-img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 6px;
            background: #f8f6ff;
            flex-shrink: 0;
        }

        .card-info { flex: 1; }
        .card-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.3rem;
            color: var(--text);
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.6rem;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 0.75rem;
            color: #777;
        }

        .meta-value {
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* 🔷 PAGINATION */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.25rem;
            margin-top: 1rem;
        }

        .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: var(--card);
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.8rem;
            border-radius: 4px;
            transition: all 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
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

        /* 🔷 RESPONSIVE */
        @media (max-width: 768px) {
            .page-wrapper { padding: 0 8px 16px; }
            .header-content { flex-direction: column; text-align: center; }
            .header-actions { width: 100%; justify-content: center; }
            .search-form { flex-direction: column; }
            .table-responsive { display: none; }
            .produk-mobile { display: flex; }
            .card-body { flex-direction: column; }
            .card-img { width: 100%; height: 120px; border-radius: 6px 6px 0 0; }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="header-section">
            <div class="header-content">
                <div class="header-title">
                    <h2>Kelola Produk</h2>
                    <p>Daftar semua produk yang tersedia.</p>
                </div>
                <div class="header-actions">
                    <a href="../tambah_produk.php" class="btn-primary">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                </div>
            </div>
            <div class="search-container">
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="search-input"
                           placeholder="Cari ID atau nama produk"
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
                                       onclick="return confirm('Yakin hapus produk ini?')">
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

        <div class="produk-mobile">
            <?php if (!empty($produk)): ?>
                <?php foreach ($produk as $p): ?>
                    <div class="produk-card">
                        <div class="card-header">
                            <div class="card-id">#<?= (int)($p['ID'] ?? 0) ?></div>
                            <div class="card-actions">
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
                                   onclick="return confirm('Yakin hapus produk ini?')">
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
                                    <i class="fas fa-image"></i>
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
                    <div class="card-body" style="text-align:center;padding:1.5rem;">
                        <i class="fas fa-box-open" style="font-size:2rem;color:#ddd;margin-bottom:0.8rem;"></i>
                        <p style="color:#777;font-size:0.9rem;">Tidak ada produk.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

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