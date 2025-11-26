<?php
require 'config.php';

// 🔹 Ambil & validasi ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    http_response_code(400);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Error</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <style>body{font-family:sans-serif;text-align:center;padding:2rem;background:#faf9ff;}</style>
    </head>
    <body>
        <div style="max-width:600px;margin:auto;background:white;padding:1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(90,70,162,0.1);">
            <h2><i class="fas fa-times-circle" style="color:#B64B62"></i> ID produk tidak valid.</h2>
            <p><a href="index.php" style="display:inline-block;margin-top:1rem;padding:0.6rem 1.4rem;background:#5A46A2;color:white;text-decoration:none;border-radius:8px;">Kembali ke Beranda</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 🔹 Ambil data produk
try {
    $stmt = $pdo->prepare('SELECT * FROM produk WHERE ID = ?');
    $stmt->execute([$id]);
    $p = $stmt->fetch();

    if (!$p) {
        http_response_code(404);
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>Error</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
            <style>body{font-family:sans-serif;text-align:center;padding:2rem;background:#faf9ff;}</style>
        </head>
        <body>
            <div style="max-width:600px;margin:auto;background:white;padding:1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(90,70,162,0.1);">
                <h2><i class="fas fa-search" style="color:#5A46A2"></i> Produk tidak ditemukan.</h2>
                <p><a href="index.php" style="display:inline-block;margin-top:1rem;padding:0.6rem 1.4rem;background:#5A46A2;color:white;text-decoration:none;border-radius:8px;">Kembali ke Beranda</a></p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
} catch (Exception $e) {
    error_log("Detail Produk Error (ID: $id): " . $e->getMessage());
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Error</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <style>body{font-family:sans-serif;text-align:center;padding:2rem;background:#faf9ff;}</style>
    </head>
    <body>
        <div style="max-width:600px;margin:auto;background:white;padding:1.5rem;border-radius:8px;box-shadow:0 4px 12px rgba(90,70,162,0.1);">
            <h2><i class="fas fa-exclamation-triangle" style="color:#B64B62"></i> Terjadi kesalahan sistem.</h2>
            <p><a href="index.php" style="display:inline-block;margin-top:1rem;padding:0.6rem 1.4rem;background:#5A46A2;color:white;text-decoration:none;border-radius:8px;">Kembali ke Beranda</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 🔹 Helper
function formatHarga(float $harga): string {
    return "Rp " . number_format($harga, 0, ',', '.');
}

$varian_list = !empty($p['Varian']) ? array_filter(array_map('trim', explode(',', $p['Varian']))) : [];
$created_at = $p['created_at'] ?? 'now';
$formatted_date = date('d M Y', strtotime($created_at));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= htmlspecialchars($p['Nama']) ?> — Dapoer Funraise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5A46A2;
            --secondary: #B64B62;
            --accent: #F9CC22;
            --bg-light: #FFF5EE;
            --soft: #DFBEE0;
            --text-muted: #9180BB;
            --border: #f0eaff;
            --shadow: 0 4px 12px rgba(90, 70, 162, 0.1);
            --fs-xs: 0.8125rem;
            --fs-sm: 0.875rem;
            --fs-md: 0.9375rem;
            --fs-lg: 1rem;
            --gap-xs: 0.4rem;
            --gap-sm: 0.6rem;
            --gap-md: 0.8rem;
            --gap-lg: 1rem;
            --radius: 8px;
            --btn-h: 38px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f1e8fdff;
            color: #333;
            font-size: var(--fs-md);
            line-height: 1.5;
            padding: 0;
            margin:0;
            min-height: 100vh;
        }

        .main-wrapper {
            width: 100%;
            margin: 0 auto;
            padding: 0rem;
        }

        .detail-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .detail-header {
            background: #f9f7ff;
            color: var(--primary);
            padding: 0.6rem 1rem;
            font-size: 1rem;
            font-weight: 600;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .detail-body {
            padding: var(--gap-sm);
        }

        /* 🔸 Layout 3 kolom: gambar | meta | deskripsi */
        .detail-content {
            display: grid;
            grid-template-columns: 260px 1fr 300px;
            gap: var(--gap-md);
        }

        /* Responsif */
        @media (max-width: 900px) {
            .detail-content {
                grid-template-columns: 200px 1fr;
                grid-template-areas:
                    "image meta"
                    "image desc";
            }
            .image-section { grid-area: image; }
            .meta-section { grid-area: meta; }
            .description-box { grid-area: desc; }
        }

        @media (max-width: 768px) {
            .detail-content {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "image"
                    "meta"
                    "desc";
            }
            .image-section,
            .meta-section,
            .description-box {
                grid-area: auto;
            }
            .product-img-container { aspect-ratio: 4/3; }
        }

        .image-section { flex-shrink: 0; }
        .product-img-container {
            width: 100%;
            aspect-ratio: 1;
            background: #fcfbff;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: var(--radius);
        }

        .no-image {
            color: var(--text-muted);
            font-size: 2.4rem;
        }

        .meta-section,
        .description-box {
            display: flex;
            flex-direction: column;
            gap: var(--gap-md);
        }

        .product-id {
            font-size: var(--fs-xs);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: var(--gap-xs);
        }

        .product-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0 0 var(--gap-sm);
            line-height: 1.3;
        }

        .product-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: var(--gap-md);
        }

        .price-tag {
            background: var(--secondary);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: var(--fs-md);
        }

        .variant-inline {
            color: var(--primary);
            opacity: 0.85;
            font-size: var(--fs-sm);
        }
        .variant-inline::before { content: "•"; margin: 0 0.4rem; color: var(--text-muted); }

        .section-label {
            font-size: var(--fs-sm);
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .variant-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }

        .variant-tag {
            background: var(--soft);
            color: var(--primary);
            padding: 3px 10px;
            border-radius: 16px;
            font-size: var(--fs-xs);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .description-text {
            font-size: var(--fs-md);
            line-height: 1.5;
            color: #444;
            white-space: pre-line;
            max-height: 220px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .meta-info {
            font-size: var(--fs-xs);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: auto;
            padding-top: var(--gap-sm);
            border-top: 1px dashed var(--border);
        }

        .action-bar {
            padding: 0.6rem 1rem;
            background: #fbf9ff;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 6px 14px;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: var(--fs-md);
            text-decoration: none;
            transition: all 0.2s ease;
            font-family: inherit;
            min-height: var(--btn-h);
            white-space: nowrap;
        }

        .btn-secondary {
            background: var(--soft);
            color: var(--primary);
            flex: 1;
        }
        .btn-secondary:hover {
            background: #d5b4d9;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="detail-card">
            <div class="detail-header">
                <i class="fas fa-info-circle"></i>
                Detail Produk
            </div>
            <div class="detail-body">
                <div class="detail-content">
                    <!-- 🔹 Kolom 1: Gambar -->
                    <div class="image-section">
                        <div class="product-img-container">
                            <?php if (!empty($p['Foto_Produk']) && @file_exists(__DIR__ . '/uploads/' . $p['Foto_Produk'])): ?>
                                <img class="product-img" src="uploads/<?= htmlspecialchars($p['Foto_Produk']) ?>" alt="<?= htmlspecialchars($p['Nama']) ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 🔹 Kolom 2: Info Utama -->
                    <div class="meta-section">
                        <div class="product-id">
                            <i class="fas fa-barcode"></i>
                            ID: <?= (int)$p['ID'] ?>
                        </div>

                        <h1 class="product-title"><?= htmlspecialchars($p['Nama']) ?></h1>

                        <div class="product-meta">
                            <span class="price-tag"><?= formatHarga((float)$p['Harga']) ?></span>
                            <?php if (!empty($varian_list)): ?>
                                <span class="variant-inline"><?= htmlspecialchars(implode(', ', $varian_list)) ?></span>
                            <?php endif; ?>
                        </div>

                        <div>
                            <div class="section-label">
                                <i class="fas fa-palette"></i> Varian Tersedia
                            </div>
                            <?php if (!empty($varian_list)): ?>
                                <div class="variant-tags">
                                    <?php foreach ($varian_list as $v): ?>
                                        <span class="variant-tag">
                                            <i class="fas fa-check"></i>
                                            <?= htmlspecialchars($v) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p style="font-size:var(--fs-sm);color:var(--text-muted);margin-top:4px;">Tidak ada varian khusus</p>
                            <?php endif; ?>
                        </div>

                        <div class="meta-info">
                            <i class="far fa-calendar-alt"></i>
                            Ditambahkan: <?= htmlspecialchars($formatted_date) ?>
                        </div>
                    </div>

                    <!-- 🔹 Kolom 3: Deskripsi (SEKARANG DI SEBELAH KANAN) -->
                    <div class="description-box">
                        <div class="section-label">
                            <i class="fas fa-align-left"></i> Deskripsi Produk
                        </div>
                        <div class="description-text">
                            <?= nl2br(htmlspecialchars($p['Deskripsi_Produk'] ?: '—')) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="action-bar">
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</body>
</html>