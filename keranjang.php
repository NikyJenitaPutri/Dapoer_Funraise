<?php
session_start();
require 'config.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Hapus item
if (isset($_GET['remove'])) {
    $key = $_GET['remove'];
    if (isset($_SESSION['cart'][$key])) {
        unset($_SESSION['cart'][$key]);
        header('Location: keranjang.php');
        exit;
    }
}

// Update kuantitas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['quantity']) && is_array($_POST['quantity'])) {
        foreach ($_POST['quantity'] as $key => $qty) {
            $qty = (int)$qty;
            if (isset($_SESSION['cart'][$key])) {
                if ($qty > 0 && $qty <= 100) {
                    $_SESSION['cart'][$key]['quantity'] = $qty;
                } else {
                    unset($_SESSION['cart'][$key]);
                }
            }
        }
        header('Location: keranjang.php');
        exit;
    }

    // Proses checkout
    // Proses checkout — SIMPAN KE DATABASE + Kirim ke WhatsApp
    if (isset($_POST['generate_wa'])) {
        $errors = [];
        $nama = trim($_POST['nama'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $pengambilan = $_POST['pengambilan'] ?? '';
        $metode_bayar = $_POST['metode_bayar'] ?? '';

        if (!$nama) $errors[] = 'Nama wajib diisi.';
        if (!$alamat) $errors[] = 'Alamat wajib diisi.';
        if (!in_array($pengambilan, ['ambil', 'antar'])) $errors[] = 'Pilih metode pengambilan.';
        if (!in_array($metode_bayar, ['cash', 'tf'])) $errors[] = 'Pilih metode pembayaran.';

        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) $errors[] = 'Keranjang belanja kosong.';

        if (empty($errors)) {
            try {
                $total = 0;
                $produk_list = [];
                foreach ($cart as $item) {
                    $qty = (int)($item['quantity'] ?? 0);
                    if ($qty <= 0) continue; // ✅ Hanya simpan yang dipilih
                    $harga = (int)($item['harga'] ?? 0);
                    $subtotal = $harga * $qty;
                    $total += $subtotal;
                    $produk_list[] = [
                        'nama' => $item['nama'],
                        'varian' => $item['varian'] ?? null,
                        'qty' => $qty,
                        'harga' => $harga,
                        'subtotal' => $subtotal
                    ];
                }

                if (empty($produk_list)) {
                    throw new Exception("Tidak ada produk dengan jumlah > 0.");
                }

                // 🔹 SIMPAN KE DATABASE (tabel `pesanan`)
                $produk_json = json_encode($produk_list, JSON_UNESCAPED_UNICODE);
                $stmt = $pdo->prepare("
                    INSERT INTO pesanan (nama_pelanggan, alamat, produk, total, pengambilan, metode_bayar, status)
                    VALUES (?, ?, ?, ?, ?, ?, 'baru')
                ");
                $stmt->execute([
                    $nama,
                    $alamat,
                    $produk_json,
                    $total,
                    $pengambilan,
                    $metode_bayar
                ]);
                $order_id = $pdo->lastInsertId(); // opsional: bisa ditampilkan/log

                // 🔹 Format WhatsApp
                $wa_text = "Halo Dapoer Funraise!\n\n";
                $wa_text .= "Saya ingin memesan:\n";
                foreach ($produk_list as $p) {
                    $wa_text .= "• " . htmlspecialchars_decode($p['nama']) ." " ;
                    if (!empty($p['varian'])) {
                        $wa_text .= " (_" . htmlspecialchars_decode($p['varian']) . "_)";
                    }
                    $wa_text .= "\n      Jumlah: " . $p['qty'] . " × Rp " . number_format($p['harga'], 0, ',', '.') .
                                " = Rp " . number_format($p['subtotal'], 0, ',', '.') . "\n";
                }
                $wa_text .= "\n" . str_repeat("─", 24) . "\n";
                $wa_text .= "Total: _Rp " . number_format($total, 0, ',', '.') . "_\n\n";
                $wa_text .= "Detail Pemesan:\n";
                $wa_text .= "• Nama      : " . htmlspecialchars_decode($nama) . "\n";
                $wa_text .= "• Alamat    : " . htmlspecialchars_decode($alamat) . "\n";
                $wa_text .= "• Pengambilan: " . ($pengambilan === 'ambil' ? 'Ambil di Toko' : 'Diantar') . "\n";
                $wa_text .= "• Pembayaran : " . ($metode_bayar === 'cash' ? 'Cash (di Tempat)' : 'Transfer Bank') . "\n\n";
                $wa_text .= "Terima kasih ";

                $wa_encoded = rawurlencode($wa_text);
                $whatsapp_link = "https://wa.me/6285393340911?text=" . $wa_encoded;

                $_SESSION['cart'] = []; // Reset keranjang
                header("Location: " . $whatsapp_link);
                exit;

            } catch (Exception $e) {
                error_log("Checkout DB Error: " . $e->getMessage());
                $errors[] = 'Gagal menyimpan pesanan. Silakan coba lagi.';
            }
        }
    }
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cart as $item) {
    $total += $item['harga'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Keranjang — Dapoer Funraise</title>
    <link rel="stylesheet" href="  https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css  ">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5A46A2;
            --secondary: #B64B62;
            --accent: #F9CC22;
            --bg-light: #FFF5EE;
            --soft: #DFBEE0;
            --text-muted: #9180BB;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            font-family: 'Poppins', 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--bg-light) 0%, #f9f5ff 100%);
            color: #333;
            display: flex;
            flex-direction: column;
        }

        .app-header {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: white;
            padding: 1.2rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .app-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            z-index: 1;
        }
        .app-header > * { position: relative; z-index: 2; }

        .logo {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .logo:hover { transform: scale(1.02); }
        .logo-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            backdrop-filter: blur(4px);
        }
        .logo-text {
            display: flex;
            flex-direction: column;
        }
        .logo-main {
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .logo-sub {
            font-size: 0.95rem;
            font-weight: 500;
            opacity: 0.9;
            margin-top: -2px;
        }

        .main-content {
            flex: 1;
            display: flex;
            padding: 1rem;
            overflow: auto;
        }

        .content-wrapper {
            background: white;
            border-radius: 18px;
            box-shadow: 0 12px 36px rgba(90, 70, 162, 0.18);
            width: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0,0,0,0.02);
        }

        .page-header {
            background: linear-gradient(120deg, #f8f6ff, #faf5ff);
            color: var(--primary);
            padding: 1.2rem 1.8rem;
            font-size: 1.4rem;
            font-weight: 700;
            border-bottom: 1px solid #f0eaff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-body {
            flex: 1;
            padding: 1.4rem;
            overflow-y: auto;
        }

        .alert {
            background: #fff8f8;
            color: #c0392b;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--secondary);
            font-weight: 600;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 5px rgba(182, 75, 98, 0.06);
        }

        .empty-state {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--text-muted);
        }
        .empty-icon { font-size: 3.8rem; margin-bottom: 1rem; color: var(--soft); }
        .empty-title { font-size: 1.45rem; font-weight: 700; color: var(--primary); margin-bottom: 0.6rem; }
        .empty-btn {
            display: inline-flex; align-items: center; gap: 8px; margin-top: 1rem;
            padding: 12px 26px; background: var(--primary); color: white;
            border-radius: 12px; font-weight: 700; font-size: 1.15rem;
            text-decoration: none;
        }

        .two-columns {
            display: flex;
            gap: 1.5rem;
        }

        .column {
            flex: 1;
            min-width: 0;
        }

        .section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
            margin-bottom: 1.2rem;
            border: 1px solid #f5f0ff;
        }
        .section-header {
            background: linear-gradient(120deg, #faf8ff, #f8f5ff);
            color: var(--primary);
            padding: 1rem 1.5rem;
            font-weight: 700;
            font-size: 1.25rem;
            border-bottom: 1px solid #f0eaff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cart-items {
            padding: 1.2rem 1.2rem 0.6rem;
        }

        .cart-item {
            display: flex;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #f8f5ff;
        }
        .cart-item:last-child { border-bottom: none; }

        .item-img {
            width: 72px;
            height: 72px;
            background: #fcfbff;
            border-radius: 10px;
            flex-shrink: 0;
            border: 2px solid #f8f6ff;
            overflow: hidden;
        }
        .item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .item-img-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 1.6rem;
        }
        .item-info { flex: 1; }
        .item-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 4px;
            line-height: 1.3;
        }
        .item-meta {
            font-size: 1.05rem;
            color: var(--text-muted);
            margin-bottom: 4px;
            display: flex;
            gap: 8px;
        }
        .item-price { font-weight: 700; color: var(--secondary); font-size: 1.1rem; }

        .item-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .quantity-btn {
            width: 34px;
            height: 34px;
            border: none;
            background: #f0eaff;
            color: var(--primary);
            font-weight: 700;
            font-size: 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .quantity-btn:hover { background: #e6d9ff; }
        .quantity-input {
            width: 56px;
            padding: 6px 0;
            border: none;
            background: #faf9ff;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
            text-align: center;
            border-radius: 6px;
        }
        .remove-btn {
            width: 34px;
            height: 34px;
            border: none;
            background: #ffe8e8;
            color: var(--secondary);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .remove-btn:hover {
            background: #ffd5d5;
            transform: scale(1.05);
        }

        .checkout-form {
            padding: 1.4rem 1.2rem;
        }

        .form-label {
            display: block;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--primary);
            font-size: 1.25rem;
        }
        .required { color: var(--secondary); }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e8e6f2;
            border-radius: 10px;
            font-size: 1.15rem;
            line-height: 1.4;
            transition: all 0.25s;
            background: #faf9ff;
            font-weight: 500;
            font-family: inherit;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.2);
            background: white;
        }
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }
        .radio-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border: 2px solid #f0eaff;
            border-radius: 10px;
            background: #faf8ff;
            cursor: pointer;
            transition: all 0.2s;
        }
        .radio-option:hover {
            border-color: var(--primary);
            background: #f5f2ff;
        }
        .radio-option input {
            width: 18px;
            height: 18px;
            accent-color: var(--secondary);
        }
        .radio-label-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #444;
        }
        .radio-option input:checked + i + .radio-label-text {
            color: var(--primary);
        }
        .radio-option i {
            font-size: 1.2rem;
            color: var(--primary);
        }
        .radio-option input:checked ~ i {
            color: var(--secondary);
        }

        .cart-summary {
            background: linear-gradient(135deg, #fbf9ff, #f7f3ff);
            border-radius: 14px;
            padding: 1rem 1.4rem;
            margin-top: 1.4rem;
            border: 1px solid #f0eaff;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 1.15rem;
        }
        .summary-label { color: var(--text-muted); }
        .summary-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--secondary);
            border-top: 2px solid var(--soft);
            padding-top: 10px;
            margin-top: 6px;
        }

        .form-footer {
            margin-top: 1.4rem;
            padding-top: 1rem;
            border-top: 1px solid #f0eaff;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1.2rem;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.3s;
            font-family: inherit;
        }
        .btn-secondary {
            background: linear-gradient(135deg, var(--soft), #c8a5d0);
            color: var(--primary);
            flex: 1;
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #d0a8d5, #c095cb);
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--secondary), #9e3e52);
            color: white;
            flex: 1;
            box-shadow: 0 4px 12px rgba(182, 75, 98, 0.25);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 16px rgba(182, 75, 98, 0.35);
        }

        .form-row {
            display: flex;
            gap: 1.2rem;
            flex-wrap: wrap;
            margin-bottom: 1.3rem;
        }
        .form-row > div {
            flex: 1;
            min-width: 240px;
        }

        /* 🔹 Media Queries — Responsif untuk Semua Layar */
        @media (max-width: 899px) {
            .two-columns {
                flex-direction: column;
                gap: 1rem;
            }

            .column {
                min-width: auto;
            }

            .form-row {
                flex-direction: column;
                gap: 1rem;
            }

            .form-row > div {
                min-width: auto;
            }

            .app-header {
                padding: 1rem 1.2rem;
            }

            .logo {
                gap: 10px;
            }

            .logo-icon {
                width: 44px;
                height: 44px;
                font-size: 1.5rem;
            }

            .logo-main {
                font-size: 1.45rem;
            }

            .logo-sub {
                font-size: 0.85rem;
            }

            .page-header {
                font-size: 1.25rem;
                padding: 1rem 1.2rem;
            }

            .section-header {
                font-size: 1.15rem;
                padding: 0.8rem 1.2rem;
            }

            .cart-item {
                gap: 10px;
                padding: 10px 0;
            }

            .item-img {
                width: 64px;
                height: 64px;
            }

            .item-name {
                font-size: 1.1rem;
            }

            .item-meta {
                font-size: 0.95rem;
            }

            .quantity-input {
                width: 50px;
                padding: 5px 0;
                font-size: 1rem;
            }

            .quantity-btn, .remove-btn {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }

            .form-label {
                font-size: 1.15rem;
            }

            .btn {
                padding: 10px 18px;
                font-size: 1.1rem;
            }

            .form-footer {
                flex-direction: column;
                gap: 0.8rem;
            }
        }

        @media (max-width: 599px) {
            .app-header {
                padding: 0.9rem 1rem;
            }

            .logo-main {
                font-size: 1.3rem;
            }

            .logo-sub {
                display: none;
            }

            .page-body {
                padding: 1rem;
            }

            .section {
                margin-bottom: 1rem;
            }

            .cart-items, .checkout-form {
                padding: 1rem 1rem 0.5rem;
            }

            .cart-item {
                flex-wrap: wrap;
            }

            .item-info {
                flex-basis: calc(100% - 74px);
            }

            .item-controls {
                width: 100%;
                margin-top: 8px;
                justify-content: flex-start;
            }

            .quantity-input {
                width: 48px;
            }

            .summary-row, .summary-total {
                font-size: 1rem;
            }

            .btn {
                font-size: 1.05rem;
                padding: 9px 16px;
            }
        }
    </style>
</head>
<body>

    <header class="app-header">
        <div class="logo">
            <div class="logo-icon">
                <img src="assets/logo.png" alt="Logo Dapoer Funraise" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div class="logo-text">
                <div class="logo-main">Dapoer Funraise</div>
                <div class="logo-sub">Keranjang Belanja</div>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="content-wrapper">
            <div class="page-header">
                <i class="fas fa-shopping-cart" style="color: var(--secondary);"></i>
                Keranjang & Detail Pemesanan
            </div>
            <div class="page-body">

                <?php if (!empty($errors)): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <strong>Perbaiki kesalahan berikut:</strong>
                            <ul style="margin:6px 0 0 20px; font-size:1rem;">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (empty($cart)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h2 class="empty-title">Keranjang Belanja Kosong</h2>
                        <p>Tambahkan produk terlebih dahulu untuk melanjutkan pembelian.</p>
                        <a href="produk.php" class="empty-btn">
                            <i class="fas fa-scarf"></i> Lihat Produk
                        </a>
                    </div>
                <?php else: ?>

                    <div class="two-columns">
                        <!-- 🔸 KIRI: PRODUK -->
                        <div class="column">
                            <div class="section">
                                <div class="section-header">
                                    <i class="fas fa-list"></i> Produk yang Dipesan
                                </div>
                                <div class="cart-items">
                                    <form method="POST" id="cartForm">
                                        <?php foreach ($cart as $key => $item): ?>
                                            <div class="cart-item">
                                                <div class="item-img">
                                                    <?php
                                                    $foto = trim($item['foto'] ?? '');
                                                    $foto_path = $foto ? 'uploads/' . $foto : '';
                                                    $full_path = __DIR__ . '/' . $foto_path;
                                                    $use_image = $foto && is_file($full_path);
                                                    ?>
                                                    <?php if ($use_image): ?>
                                                        <img src="<?= htmlspecialchars($foto_path) ?>" 
                                                             alt="<?= htmlspecialchars($item['nama']) ?>">
                                                    <?php else: ?>
                                                        <div class="item-img-placeholder">
                                                            <i class="fas fa-cookie-bite"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="item-info">
                                                    <div class="item-name"><?= htmlspecialchars($item['nama']) ?></div>
                                                    <div class="item-meta">
                                                        <?php if (!empty($item['varian'])): ?>
                                                            <span><i class="fas fa-tag"></i> <?= htmlspecialchars($item['varian']) ?></span>
                                                        <?php endif; ?>
                                                        <span class="item-price">Rp <?= number_format($item['harga'], 0, ',', '.') ?></span>
                                                    </div>
                                                </div>
                                                <div class="item-controls">
                                                    <button type="button" class="quantity-btn" onclick="adjustQty(this, -1)">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input 
                                                        type="number" 
                                                        name="quantity[<?= htmlspecialchars($key) ?>]" 
                                                        value="<?= (int)$item['quantity'] ?>" 
                                                        min="1" 
                                                        max="100" 
                                                        class="quantity-input"
                                                    >
                                                    <button type="button" class="quantity-btn" onclick="adjustQty(this, 1)">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                    <a href="?remove=<?= urlencode($key) ?>" class="remove-btn" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <button type="submit" class="btn btn-secondary" style="width:100%; margin-top:1rem;">
                                            <i class="fas fa-sync-alt"></i> Perbarui
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- 🔸 KANAN: DETAIL -->
                        <div class="column">
                            <div class="section">
                                <div class="section-header">
                                    <i class="fas fa-file-invoice"></i> Detail Pemesanan
                                </div>
                                <div class="checkout-form">
                                    <form method="POST">

                                        <!-- 🔹 Baris 1: Nama & Alamat -->
                                        <div class="form-row">
                                            <div>
                                                <label class="form-label" for="nama">
                                                    Nama Lengkap <span class="required">*</span>
                                                </label>
                                                <input 
                                                    type="text" 
                                                    id="nama" 
                                                    name="nama" 
                                                    class="form-control"
                                                    value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" 
                                                    required 
                                                    placeholder="Contoh: Siti Aisyah"
                                                >
                                            </div>
                                            <div>
                                                <label class="form-label" for="alamat">
                                                    Alamat Lengkap <span class="required">*</span>
                                                </label>
                                                <textarea 
                                                    id="alamat" 
                                                    name="alamat" 
                                                    class="form-control"
                                                    rows="2"
                                                    required 
                                                    placeholder="Jl. Melati No. 12, Bandung"
                                                ><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                                            </div>
                                        </div>

                                        <!-- 🔹 Baris 2: Pengiriman & Pembayaran -->
                                        <div class="form-row">
                                            <div>
                                                <label class="form-label">
                                                    Metode Pengiriman <span class="required">*</span>
                                                </label>
                                                <div class="radio-group">
                                                    <label class="radio-option">
                                                        <input type="radio" name="pengambilan" value="ambil" required 
                                                            <?= (($_POST['pengambilan'] ?? '') === 'ambil') ? 'checked' : '' ?>>
                                                        <i class="fas fa-store"></i>
                                                        <span class="radio-label-text">Ambil di Toko</span>
                                                    </label>
                                                    <label class="radio-option">
                                                        <input type="radio" name="pengambilan" value="antar" required 
                                                            <?= (($_POST['pengambilan'] ?? '') === 'antar') ? 'checked' : '' ?>>
                                                        <i class="fas fa-shipping-fast"></i>
                                                        <span class="radio-label-text">Diantar</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="form-label">
                                                    Metode Pembayaran <span class="required">*</span>
                                                </label>
                                                <div class="radio-group">
                                                    <label class="radio-option">
                                                        <input type="radio" name="metode_bayar" value="cash" required 
                                                            <?= (($_POST['metode_bayar'] ?? '') === 'cash') ? 'checked' : '' ?>>
                                                        <i class="fas fa-money-bill-wave"></i>
                                                        <span class="radio-label-text">Cash (di Tempat)</span>
                                                    </label>
                                                    <label class="radio-option">
                                                        <input type="radio" name="metode_bayar" value="tf" required 
                                                            <?= (($_POST['metode_bayar'] ?? '') === 'tf') ? 'checked' : '' ?>>
                                                        <i class="fas fa-university"></i>
                                                        <span class="radio-label-text">Transfer Bank</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- 🔹 Summary -->
                                        <div class="cart-summary">
                                            <div class="summary-row">
                                                <span class="summary-label">Subtotal</span>
                                                <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                                            </div>
                                            <div class="summary-total">
                                                <span>Total</span>
                                                <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                                            </div>
                                        </div>

                                        <div class="form-footer">
                                            <a href="produk.php" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left"></i> Lanjut Belanja
                                            </a>
                                            <button type="submit" name="generate_wa" class="btn btn-primary">
                                                <i class="fab fa-whatsapp"></i> Kirim ke WhatsApp
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </main>

    <script>
        function adjustQty(button, delta) {
            const controls = button.closest('.item-controls');
            const input = controls.querySelector('.quantity-input');
            if (!input) return;
            
            let val = parseInt(input.value) || 1;
            val += delta;
            val = Math.max(1, Math.min(100, val));
            input.value = val;
            
            const form = document.getElementById('cartForm');
            if (form) form.submit();
        }
    </script>
</body>
</html> 