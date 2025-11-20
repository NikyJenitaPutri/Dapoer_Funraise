<?php
require 'config.php';

// 🔹 Ambil data produk
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    die('<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Error</title><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"><style>body{font-family:sans-serif;text-align:center;padding:3rem;background:#f8f9fa;}</style></head><body><div style="max-width:600px;margin:auto;background:white;padding:2rem;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);"><h2><i class="fas fa-times-circle" style="color:#B64B62"></i> ID produk tidak valid.</h2><p><a href="index.php" style="display:inline-block;margin-top:1rem;padding:0.5rem 1.5rem;background:#5A46A2;color:white;text-decoration:none;border-radius:6px;">Kembali ke Beranda</a></p></div></body></html>');
}

try {
    $stmt = $pdo->prepare('SELECT * FROM produk WHERE ID = ?');
    $stmt->execute([$id]);
    $p = $stmt->fetch();

    if (!$p) {
        die('<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Error</title><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"><style>body{font-family:sans-serif;text-align:center;padding:3rem;background:#f8f9fa;}</style></head><body><div style="max-width:600px;margin:auto;background:white;padding:2rem;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);"><h2><i class="fas fa-search" style="color:#5A46A2"></i> Produk tidak ditemukan.</h2><p><a href="index.php" style="display:inline-block;margin-top:1rem;padding:0.5rem 1.5rem;background:#5A46A2;color:white;text-decoration:none;border-radius:6px;">Kembali ke Beranda</a></p></div></body></html>');
    }
} catch (Exception $e) {
    error_log("Detail Produk Error (ID: $id): " . $e->getMessage());
    die('<!DOCTYPE html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Error</title><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"><style>body{font-family:sans-serif;text-align:center;padding:3rem;background:#f8f9fa;}</style></head><body><div style="max-width:600px;margin:auto;background:white;padding:2rem;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1);"><h2><i class="fas fa-exclamation-triangle" style="color:#B64B62"></i> Terjadi kesalahan sistem.</h2><p><a href="index.php" style="display:inline-block;margin-top:1rem;padding:0.5rem 1.5rem;background:#5A46A2;color:white;text-decoration:none;border-radius:6px;">Kembali ke Beranda</a></p></div></body></html>');
}

function formatHarga(float $harga): string {
    return "Rp " . number_format($harga, 0, ',', '.');
}

// 🔹 Parsing varian (teks saja)
$varian_list = [];
if (!empty($p['Varian'])) {
    $varian_list = array_filter(array_map('trim', explode(',', $p['Varian'])));
}
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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
            font-size: 15px;
        }

        /* 🔹 MAIN CONTENT - ENLARGED */
        .main-content {
            flex: 1;
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem;
            width: 100%;
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 3rem);
        }

        /* 🔹 PRODUCT DETAIL CARD - ENLARGED */
        .product-detail-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(90, 70, 162, 0.18);
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.03);
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .detail-body {
            display: flex;
            gap: 2.5rem;
            padding: 2rem;
            flex: 1;
            overflow: hidden;
        }

        /* 🔹 GALLERY - ENLARGED */
        .gallery-section {
            flex: 0 0 48%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-image-container {
            background: #fafafa;
            border-radius: 12px;
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            border: 2px solid #f0eaff;
            min-height: 450px;
        }

        .main-image-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
        }

        .no-image {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--bg-light), #f9f5ff);
            border: 2px dashed var(--soft);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
        }
        .no-image i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.6;
        }
        .no-image p {
            font-size: 1.1rem;
            font-weight: 600;
        }

        /* 🔹 INFO SECTION - ENLARGED */
        .info-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .product-id-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #f5f3ff, #faf5ff);
            border: 1px solid #f0eaff;
            border-radius: 20px;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 600;
            width: fit-content;
        }

        .product-title {
            font-size: 2.2rem;
            color: var(--primary);
            font-weight: 700;
            line-height: 1.2;
            margin: 0;
        }

        .price-box {
            background: linear-gradient(135deg, #fff5f7, #fff8fa);
            border: 2px solid #ffe5eb;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .price-box i {
            font-size: 1.8rem;
            color: var(--secondary);
        }
        .price-label {
            font-size: 0.85rem;
            color: #999;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .price-value {
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--secondary);
        }

        /* 🔹 VARIANT BOX - ENLARGED */
        .variant-box {
            background: linear-gradient(135deg, #faf5ff, #f9f5ff);
            border: 2px solid #f0eaff;
            border-radius: 12px;
            padding: 1.5rem;
        }
        .variant-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: var(--primary);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        .variant-header i {
            color: var(--secondary);
            font-size: 1.2rem;
        }
        .variant-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .variant-tag {
            background: var(--soft);
            color: var(--primary);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .variant-tag:hover {
            background: #d0a8d5;
            transform: translateY(-1px);
        }
        .no-variant {
            color: var(--text-muted);
            font-style: italic;
            font-size: 1rem;
        }

        /* 🔹 DESCRIPTION BOX - ENLARGED */
        .description-box {
            background: #fbf9ff;
            border: 2px solid #f3f0ff;
            border-radius: 12px;
            padding: 1.5rem;
        }
        .description-header {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: var(--primary);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        .description-header i {
            color: var(--secondary);
            font-size: 1.2rem;
        }
        .description-text {
            font-size: 1rem;
            line-height: 1.7;
            color: #555;
            max-height: 350px;
            overflow-y: auto;
        }

        /* 🔹 META INFO - ENLARGED */
        .meta-info {
            padding: 1rem 1.2rem;
            background: #f8f9fa;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* 🔹 ACTION BAR - ENLARGED */
        .action-bar {
            padding: 2rem 2rem;
            background: #fbf9ff;
            border-top: 2px solid #f3f0ff;
            display: flex;
            gap: 12px;
            flex-shrink: 0;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.3s;
            font-family: inherit;
            min-height: 48px;
        }
        .btn-back {
            background: linear-gradient(135deg, var(--soft), #c8a5d0);
            color: var(--primary);
        }
        .btn-back:hover {
            background: linear-gradient(135deg, #d0a8d5, #c095cb);
            transform: translateY(-2px);
        }

        /* 🔹 SCROLLBAR STYLING */
        .info-section::-webkit-scrollbar,
        .description-text::-webkit-scrollbar {
            width: 6px;
        }
        .info-section::-webkit-scrollbar-track,
        .description-text::-webkit-scrollbar-track {
            background: #f0eaff;
            border-radius: 10px;
        }
        .info-section::-webkit-scrollbar-thumb,
        .description-text::-webkit-scrollbar-thumb {
            background: var(--soft);
            border-radius: 10px;
        }
        .info-section::-webkit-scrollbar-thumb:hover,
        .description-text::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }

        /* 🔹 RESPONSIVE */
        @media (max-width: 992px) {
            .detail-body {
                flex-direction: column;
                gap: 2rem;
            }
            .gallery-section {
                flex: 0 0 auto;
                height: 400px;
            }
            .main-image-container {
                min-height: 350px;
            }
            .product-title {
                font-size: 1.9rem;
            }
        }

        @media (max-width: 768px) {
            body {
                font-size: 14px;
            }
            .main-content {
                padding: 1rem;
                min-height: auto;
            }
            .detail-body {
                padding: 1.5rem;
            }
            .gallery-section {
                height: 300px;
            }
            .main-image-container {
                min-height: 280px;
                padding: 1.5rem;
            }
            .product-title {
                font-size: 1.6rem;
            }
            .price-value {
                font-size: 1.6rem;
            }
        }

        @media (max-width: 480px) {
            .product-title {
                font-size: 1.4rem;
            }
            .price-value {
                font-size: 1.5rem;
            }
            .btn {
                font-size: 0.95rem;
                padding: 10px 18px;
            }
            .detail-body {
                padding: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <main class="main-content">
        <div class="product-detail-card">
            <div class="detail-body">
                <!-- 🔹 GALLERY SECTION -->
                <div class="gallery-section">
                    <div class="main-image-container">
                        <?php 
                        $foto_path = __DIR__ . '/uploads/' . $p['Foto_Produk'];
                        if (!empty($p['Foto_Produk']) && @file_exists($foto_path)): 
                        ?>
                            <img src="uploads/<?= htmlspecialchars($p['Foto_Produk']) ?>" alt="<?= htmlspecialchars($p['Nama']) ?>">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-image"></i>
                                <p>Foto belum tersedia</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 🔹 INFO SECTION -->
                <div class="info-section">
                    <div class="product-id-badge">
                        <i class="fas fa-barcode"></i>
                        ID Produk: <?= (int)$p['ID'] ?>
                    </div>

                    <h1 class="product-title"><?= htmlspecialchars($p['Nama']) ?></h1>

                    <div class="price-box">
                        <i class="fas fa-tags"></i>
                        <div>
                            <div class="price-label">Harga</div>
                            <div class="price-value"><?= formatHarga((float)$p['Harga']) ?></div>
                        </div>
                    </div>

                    <!-- 🔹 VARIANT BOX -->
                    <div class="variant-box">
                        <div class="variant-header">
                            <i class="fas fa-palette"></i>
                            Varian Tersedia
                        </div>
                        <?php if (!empty($varian_list)): ?>
                            <div class="variant-tags">
                                <?php foreach ($varian_list as $v): ?>
                                    <span class="variant-tag">
                                        <i class="fas fa-check-circle"></i>
                                        <?= htmlspecialchars($v) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-variant">
                                <i class="fas fa-info-circle"></i> Tidak ada varian khusus
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 🔹 DESCRIPTION BOX -->
                    <div class="description-box">
                        <div class="description-header">
                            <i class="fas fa-align-left"></i>
                            Deskripsi Produk
                        </div>
                        <div class="description-text">
                            <?= nl2br(htmlspecialchars($p['Deskripsi_Produk'])) ?>
                        </div>
                    </div>

                    <div class="meta-info">
                        <i class="far fa-calendar-alt"></i>
                        Ditambahkan: <?= htmlspecialchars(date('d M Y', strtotime($p['created_at'] ?? 'now'))) ?>
                    </div>
                </div>
            </div>

            <!-- 🔹 ACTION BAR -->
            <div class="action-bar">
                <a href="javascript:history.back()" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </main>

</body>
</html>