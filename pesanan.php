<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

$error = $success = '';

// --- Pencarian: satu field (ID atau Nama) ---
$q = trim($_GET['q'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1)); // Halaman saat ini
$per_page = 6; // Maksimal 6 order card per halaman
$offset = ($page - 1) * $per_page;

$where = "1=1";
$params = [];

// Filter berdasarkan status
if ($status_filter !== '' && in_array($status_filter, ['baru', 'diproses', 'selesai', 'batal'])) {
    $where .= " AND status = ?";
    $params[] = $status_filter;
}

// Filter berdasarkan pencarian
if ($q !== '') {
    if (ctype_digit($q)) {
        // Jika semua karakter angka → cari berdasarkan ID
        $where .= " AND id = ?";
        $params[] = (int)$q;
    } else {
        // Jika ada huruf → cari berdasarkan nama (case-insensitive)
        $where .= " AND LOWER(nama_pelanggan) LIKE ?";
        $params[] = '%' . strtolower($q) . '%';
    }
}

// --- Update status ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $allowed = ['baru', 'diproses', 'selesai', 'batal'];
        if ($id <= 0 || !in_array($status, $allowed)) {
            throw new Exception("Status tidak valid.");
        }
        $stmt = $pdo->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
        $updated = $stmt->execute([$status, $id]);
        if ($updated && $stmt->rowCount() > 0) {
            $success = "Status pesanan #$id berhasil diperbarui.";
        } else {
            $error = "Tidak ada perubahan.";
        }
    } catch (Exception $e) {
        $error = htmlspecialchars($e->getMessage());
    }
}

// --- Hapus ---
if (isset($_GET['hapus'])) {
    try {
        $id = (int)$_GET['hapus'];
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM pesanan WHERE id = ?");
            $deleted = $stmt->execute([$id]);
            if ($deleted && $stmt->rowCount() > 0) {
                $_SESSION['success'] = 'Pesanan berhasil dihapus!';
                header('Location: kelola-pesanan.php' . 
                    ($q ? '?q=' . urlencode($q) : '') . 
                    ($status_filter ? ($q ? '&' : '?') . 'status=' . urlencode($status_filter) : ''));
                exit;
            }
        }
        $error = 'Gagal menghapus pesanan.';
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// --- Ambil data pesanan dengan filter ---
try {
    // Query untuk hitung total data (untuk pagination)
    $sql_count = "SELECT COUNT(*) FROM pesanan WHERE $where";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute($params);
    $total_orders = (int)$stmt_count->fetchColumn();
    $total_pages = max(1, ceil($total_orders / $per_page));
    
    // Query untuk data yang ditampilkan (dengan filter + pagination)
    $sql = "SELECT id, nama_pelanggan, alamat, produk, total, pengambilan, metode_bayar, status, created_at
            FROM pesanan WHERE $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Query untuk statistik (tanpa filter, semua data)
    $stmt_all = $pdo->prepare("SELECT status FROM pesanan");
    $stmt_all->execute();
    $all_orders = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
    
    // Hitung jumlah per status dari semua data
    $count_baru = count(array_filter($all_orders, fn($o) => $o['status'] === 'baru'));
    $count_diproses = count(array_filter($all_orders, fn($o) => $o['status'] === 'diproses'));
    $count_selesai = count(array_filter($all_orders, fn($o) => $o['status'] === 'selesai'));
    $count_batal = count(array_filter($all_orders, fn($o) => $o['status'] === 'batal'));
    
} catch (Exception $e) {
    $error = "Gagal memuat data.";
    $orders = [];
    $all_orders = [];
    $total_orders = 0;
    $total_pages = 1;
    $count_baru = $count_diproses = $count_selesai = $count_batal = 0;
}

// Render produk: satu per baris (tidak digabung)
function renderProduk($produkJson) {
    $data = json_decode($produkJson, true);
    if (!is_array($data)) return '<div class="produk-item">[Data produk tidak valid]</div>';
    
    $items = [];
    foreach ($data as $item) {
        $qty = (int)($item['qty'] ?? 0);
        if ($qty <= 0) continue;
        
        $nama = htmlspecialchars($item['nama'] ?? '—');
        $varian = htmlspecialchars($item['varian'] ?? '');
        $text = $nama;
        if ($varian) {
            $text .= " <span class=\"varian\">($varian)</span>";
        }
        
        $items[] = "<div class=\"produk-item\">• $text × <strong>$qty</strong></div>";
    }
    
    return $items ? implode("\n", $items) : '<div class="produk-item">—</div>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan • Dapoer Funraise</title>
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 0.5rem;
        }

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
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .stat-card.active {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(90, 70, 162, 0.1), rgba(182, 75, 98, 0.1));
        }

        .stat-card.active::after {
            content: '✓';
            position: absolute;
            top: 8px;
            right: 8px;
            background: var(--primary);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
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

        .search-bar {
            background: var(--card);
            padding: 1.2rem;
            border-radius: 14px;
            box-shadow: var(--shadow);
            margin-bottom: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
            border: 1px solid var(--border-card);
        }

        .search-group {
            flex: 1;
            min-width: 250px;
        }

        .search-group label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
            color: var(--primary);
        }

        .search-group input {
            width: 100%;
            padding: 0.65rem 1rem;
            border: 1px solid var(--border-medium);
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .search-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.15);
        }

        .btn-search,
        .btn-reset {
            padding: 0.65rem 1.4rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.25s ease;
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

        .alert {
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
            margin-bottom: 1.2rem;
            font-weight: 600;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        /* 🎯 UBAH MENJADI 3 KOLOM */
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.4rem;
        }

        .order-card {
            background: var(--card);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--border-card);
            display: flex;
            flex-direction: column;
        }

        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary);
        }

        .order-head {
            background: #fbf9ff;
            padding: 0.9rem 1.1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-medium);
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        .order-id {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary);
        }

        .order-time {
            font-size: 0.8rem;
            color: #777;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .status-baru { background: #e3f2fd; color: #1565c0; border-color: #bbdefb; }
        .status-diproses { background: #fff8e1; color: #ef6c00; border-color: #ffe082; }
        .status-selesai { background: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
        .status-batal { background: #ffebee; color: #c62828; border-color: #ffcdd2; }

        .order-body {
            padding: 1rem;
            font-size: 0.92rem;
            flex: 1;
        }

        .customer-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.3rem;
        }

        .customer-addr {
            color: #555;
            line-height: 1.4;
            margin-bottom: 0.8rem;
            font-size: 0.88rem;
        }

        .produk-list {
            background: #fcfbff;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
            border: 1px solid #f0ecfa;
        }

        .produk-item {
            margin-bottom: 0.3rem;
            line-height: 1.4;
            color: #333;
        }

        .produk-item:last-child {
            margin-bottom: 0;
        }

        .varian {
            color: #777;
            font-weight: normal;
            opacity: 0.9;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            margin: 0.3rem 0;
            font-size: 0.88rem;
        }

        .total {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--secondary);
            text-align: right;
            margin-top: 0.6rem;
            padding-top: 0.5rem;
            border-top: 1px solid var(--border-medium);
        }

        .order-footer {
            padding: 0.8rem 1rem;
            background: #fbf9ff;
            display: flex;
            justify-content: flex-end;
            gap: 0.6rem;
            flex-wrap: wrap;
            border-top: 1px solid var(--border-medium);
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
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .btn-update { background: #e8eaff; color: var(--primary); border: 1px solid #d0c9f0; }
        .btn-update:hover { background: #dde5ff; }
        .btn-delete { background: #ffe8e8; color: var(--secondary); border: 1px solid #ffcdd2; }
        .btn-delete:hover { background: #ffd8d8; }

        select {
            padding: 5px 9px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: white;
            font-size: 0.85rem;
            font-family: inherit;
            cursor: pointer;
        }

        .empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 3rem 1.5rem;
            color: #777;
            background: var(--card);
            border-radius: 12px;
            border: 1px dashed var(--border-medium);
        }

        .empty i {
            font-size: 3.2rem;
            color: #ddd;
            margin-bottom: 1rem;
        }

        .empty h3 {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #555;
        }

        /* 📱 RESPONSIVE: Tablet → 2 kolom */
        @media (max-width: 1024px) {
            .orders-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* 📱 RESPONSIVE: Mobile → 1 kolom */
        @media (max-width: 768px) {
            .search-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .orders-grid {
                grid-template-columns: 1fr;
            }
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
            body {
                padding: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .btn-search,
            .btn-reset {
                flex: 1;
                justify-content: center;
            }
            .order-footer {
                flex-direction: column;
                align-items: stretch;
            }
            .order-footer form,
            .order-footer a {
                width: 100%;
                justify-content: center;
            }
            .stats {
                grid-template-columns: 1fr;
            }
        }

        /* 📄 PAGINATION */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 0.6rem 1rem;
            border: 1px solid var(--border-medium);
            border-radius: 8px;
            text-decoration: none;
            color: var(--text);
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            background: var(--card);
            min-width: 40px;
            text-align: center;
        }

        .pagination a:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .pagination .active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            cursor: default;
        }

        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .pagination-info {
            text-align: center;
            margin-top: 1rem;
            color: #777;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="stats">
        <div class="stat-card <?= $status_filter === 'baru' ? 'active' : '' ?>" onclick="filterByStatus('baru')">
            <div class="stat-value"><?= $count_baru ?></div>
            <div class="stat-label">Baru</div>
        </div>
        <div class="stat-card <?= $status_filter === 'diproses' ? 'active' : '' ?>" onclick="filterByStatus('diproses')">
            <div class="stat-value"><?= $count_diproses ?></div>
            <div class="stat-label">Diproses</div>
        </div>
        <div class="stat-card <?= $status_filter === 'selesai' ? 'active' : '' ?>" onclick="filterByStatus('selesai')">
            <div class="stat-value"><?= $count_selesai ?></div>
            <div class="stat-label">Selesai</div>
        </div>
        <div class="stat-card <?= $status_filter === 'batal' ? 'active' : '' ?>" onclick="filterByStatus('batal')">
            <div class="stat-value"><?= $count_batal ?></div>
            <div class="stat-label">Dibatalkan</div>
        </div>
    </div>

    <!-- Pencarian: SATU FIELD SAJA -->
    <div class="search-bar">
        <div class="search-group">
            <label for="search">Cari Pesanan (ID atau Nama)</label>
            <input type="text" id="search" name="q"
                   value="<?= htmlspecialchars($q) ?>"
                   placeholder="Contoh: 123 atau Budi">
        </div>
        <div style="display:flex;gap:0.6rem;flex-wrap:wrap;min-width:200px;">
            <button type="button" class="btn-search" onclick="submitSearch()">
                <i class="fas fa-search"></i> Cari
            </button>
            <button type="button" class="btn-reset" onclick="resetAll()">
                <i class="fas fa-undo"></i> Reset Semua
            </button>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="orders-grid">
        <?php if (empty($orders)): ?>
            <div class="empty">
                <i class="fas fa-inbox"></i>
                <h3>Belum Ada Pesanan</h3>
                <p>Pesanan akan muncul setelah pelanggan melakukan checkout.</p>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $o): ?>
                <div class="order-card">
                    <div class="order-head">
                        <div>
                            <div class="order-id">#<?= htmlspecialchars($o['id']) ?></div>
                            <div class="order-time"><?= date('d M Y • H:i', strtotime($o['created_at'])) ?></div>
                        </div>
                        <span class="status-badge status-<?= htmlspecialchars($o['status']) ?>">
                            <?= [
                                'baru' => 'Baru',
                                'diproses' => 'Diproses',
                                'selesai' => 'Selesai',
                                'batal' => 'Dibatalkan'
                            ][$o['status']] ?? ucfirst($o['status']) ?>
                        </span>
                    </div>
                    <div class="order-body">
                        <div class="customer-name"><?= htmlspecialchars($o['nama_pelanggan']) ?></div>
                        <div class="customer-addr"><?= htmlspecialchars($o['alamat']) ?></div>

                        <div class="produk-list">
                            <?= renderProduk($o['produk']) ?>
                        </div>

                        <div class="meta-row">
                            <span>Pengambilan</span>
                            <strong><?= $o['pengambilan'] === 'ambil' ? 'Ambil di Toko' : 'Diantar' ?></strong>
                        </div>
                        <div class="meta-row">
                            <span>Pembayaran</span>
                            <strong><?= $o['metode_bayar'] === 'cash' ? 'Cash' : 'Transfer' ?></strong>
                        </div>

                        <div class="total">Rp <?= number_format($o['total'], 0, ',', '.') ?></div>
                    </div>
                    <div class="order-footer">
                        <form method="POST">
                            <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                            <select name="status">
                                <option value="baru" <?= $o['status'] === 'baru' ? 'selected' : '' ?>>Baru</option>
                                <option value="diproses" <?= $o['status'] === 'diproses' ? 'selected' : '' ?>>Diproses</option>
                                <option value="selesai" <?= $o['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="batal" <?= $o['status'] === 'batal' ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-update">
                                <i class="fas fa-sync-alt"></i> Simpan
                            </button>
                        </form>
                        <a href="?hapus=<?= (int)$o['id'] ?><?= $q ? '&q=' . urlencode($q) : '' ?><?= $status_filter ? ($q ? '&' : '?') . 'status=' . urlencode($status_filter) : '' ?>"
                           class="btn btn-delete"
                           onclick="return confirm('Yakin hapus pesanan #<?= (int)$o['id'] ?>? Data tidak bisa dikembalikan.')">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <!-- PAGINATION -->
        <div class="pagination">
            <!-- Tombol Previous -->
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $q ? '&q=' . urlencode($q) : '' ?><?= $status_filter ? '&status=' . urlencode($status_filter) : '' ?>">
                    <i class="fas fa-chevron-left"></i> Prev
                </a>
            <?php else: ?>
                <span class="disabled">
                    <i class="fas fa-chevron-left"></i> Prev
                </span>
            <?php endif; ?>

            <!-- Nomor Halaman -->
            <?php
            // Tampilkan maksimal 5 nomor halaman
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            
            // Tampilkan halaman 1 jika tidak termasuk range
            if ($start_page > 1): ?>
                <a href="?page=1<?= $q ? '&q=' . urlencode($q) : '' ?><?= $status_filter ? '&status=' . urlencode($status_filter) : '' ?>">1</a>
                <?php if ($start_page > 2): ?>
                    <span>...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?><?= $q ? '&q=' . urlencode($q) : '' ?><?= $status_filter ? '&status=' . urlencode($status_filter) : '' ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <!-- Tampilkan halaman terakhir jika tidak termasuk range -->
            <?php if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                    <span>...</span>
                <?php endif; ?>
                <a href="?page=<?= $total_pages ?><?= $q ? '&q=' . urlencode($q) : '' ?><?= $status_filter ? '&status=' . urlencode($status_filter) : '' ?>"><?= $total_pages ?></a>
            <?php endif; ?>

            <!-- Tombol Next -->
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?><?= $q ? '&q=' . urlencode($q) : '' ?><?= $status_filter ? '&status=' . urlencode($status_filter) : '' ?>">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php else: ?>
                <span class="disabled">
                    Next <i class="fas fa-chevron-right"></i>
                </span>
            <?php endif; ?>
        </div>

        <div class="pagination-info">
            Menampilkan halaman <?= $page ?> dari <?= $total_pages ?> (Total: <?= $total_orders ?> pesanan)
        </div>
    <?php endif; ?>
</div>

<script>
// Filter berdasarkan status
function filterByStatus(status) {
    const url = new URL(window.location);
    const currentStatus = url.searchParams.get('status');
    
    // Toggle: jika status yang sama diklik, hapus filter
    if (currentStatus === status) {
        url.searchParams.delete('status');
    } else {
        url.searchParams.set('status', status);
    }
    
    window.location.href = url.toString();
}

// Submit pencarian dengan mempertahankan filter status
function submitSearch() {
    const q = document.getElementById('search').value.trim();
    const url = new URL(window.location);
    
    if (q) {
        url.searchParams.set('q', q);
    } else {
        url.searchParams.delete('q');
    }
    
    window.location.href = url.toString();
}

// Reset semua filter (status dan pencarian)
function resetAll() {
    const url = new URL(window.location);
    url.searchParams.delete('q');
    url.searchParams.delete('status');
    window.location.href = url.toString();
}
</script>

</body>
</html>