<?php
// === KONEKSI & AMBIL DATA ===
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../config.php";

$error = '';

// Validasi koneksi PDO
if (!isset($pdo) || !$pdo instanceof PDO) {
    die('<!DOCTYPE html><html><head><meta charset="utf-8"><title>Error</title></head><body><div style="padding:2rem;max-width:600px;margin:2rem auto;background:#fee;border-radius:8px;color:#c00;font-family:sans-serif;"><h2>❌ Koneksi Database Gagal</h2><p>File <code>config.php</code> tidak menyediakan variabel <code>$pdo</code> yang valid.</p><p>Harap pastikan config.php berisi koneksi PDO ke database <strong>dapoer_funraise</strong>.</p></div></body></html>');
}

// 🔹 AMBIL PARAMETER BULAN & TAHUN DARI GET (atau default ke bulan ini)
$bulan_pilihan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('n');
$tahun_pilihan = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

// Validasi bulan (1-12)
if ($bulan_pilihan < 1 || $bulan_pilihan > 12) {
    $bulan_pilihan = (int)date('n');
}

// Validasi tahun (misal: 2020-2030)
if ($tahun_pilihan < 2020 || $tahun_pilihan > 2030) {
    $tahun_pilihan = (int)date('Y');
}

try {
    // 🔹 Rentang BULAN PILIHAN (1st → last day)
    $month_start = sprintf('%04d-%02d-01 00:00:00', $tahun_pilihan, $bulan_pilihan);
    $month_end   = date('Y-m-t 23:59:59', strtotime($month_start)); // 't' = last day of month

    // 🔹 Pendapatan BULAN PILIHAN: hanya dari pesanan 'selesai'
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(total), 0) AS total_pendapatan
        FROM pesanan 
        WHERE status = 'selesai' 
          AND created_at BETWEEN :start AND :end
    ");
    $stmt->execute(['start' => $month_start, 'end' => $month_end]);
    $pendapatan = (float) $stmt->fetchColumn();

    // 🔹 PESANAN MASUK BULAN PILIHAN: SEMUA STATUS
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM pesanan 
        WHERE created_at BETWEEN :start AND :end
    ");
    $stmt->execute(['start' => $month_start, 'end' => $month_end]);
    $pesanan_masuk = (int) $stmt->fetchColumn();

    // 🔹 Pesanan Selesai BULAN PILIHAN
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM pesanan 
        WHERE status = 'selesai' 
          AND created_at BETWEEN :start AND :end
    ");
    $stmt->execute(['start' => $month_start, 'end' => $month_end]);
    $pesanan_selesai = (int) $stmt->fetchColumn();

    // 🔹 Jumlah produk (total semua waktu)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM produk");
    $stmt->execute();
    $jumlah_produk = (int) $stmt->fetchColumn();

    // 🔹 Jumlah testimoni (total semua waktu)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM testimoni");
    $stmt->execute();
    $jumlah_testimoni = (int) $stmt->fetchColumn();

    // 🔹 PESANAN DIBATALKAN BULAN PILIHAN
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM pesanan 
        WHERE status = 'batal' 
          AND created_at BETWEEN :start AND :end
    ");
    $stmt->execute(['start' => $month_start, 'end' => $month_end]);
    $pesanan_dibatalkan = (int) $stmt->fetchColumn();

    // 🔹 TOP 3 PRODUK TERJUAL (opsional, tetap dipakai di grafik)
    $top_produk = [];
    $stmt = $pdo->prepare("
        SELECT produk 
        FROM pesanan 
        WHERE status = 'selesai'
    ");
    $stmt->execute();
    $pesanan_list = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($pesanan_list as $produk_json) {
        $items = json_decode($produk_json, true);
        if (!is_array($items)) continue;

        foreach ($items as $item) {
            $qty = (int)($item['qty'] ?? 0);
            if ($qty <= 0) continue;

            $nama = trim($item['nama'] ?? '');
            $varian = trim($item['varian'] ?? '');

            $key = $nama;
            if ($varian) $key .= " • " . $varian;

            if (!isset($top_produk[$key])) {
                $top_produk[$key] = 0;
            }
            $top_produk[$key] += $qty;
        }
    }

} catch (PDOException $e) {
    $error = "Database error: " . htmlspecialchars($e->getMessage());
    $pendapatan = $pesanan_masuk = $pesanan_selesai = $pesanan_dibatalkan = $jumlah_produk = $jumlah_testimoni = 0;
}

// Helper: Format Rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Helper: Nama bulan dalam bahasa Indonesia
function namaBulan($bulan) {
    $nama = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return $nama[(int)$bulan] ?? 'Bulan';
}
$bulan_ini = namaBulan($bulan_pilihan);
$tahun_ini = $tahun_pilihan;
?>

<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard Admin • Dapoer Funraise</title>
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <style>
      .iframe-container {
        width: 100%; height: 85vh; min-height: 600px; border: none;
        background: #fff; border-radius: 8px; overflow: auto;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      }
      .content-section { display: none; }
      .content-section.active { display: block; }
      .iframe-card { margin-bottom: 20px; }
      .iframe-card .card-body { padding: 0 !important; }
      @media (max-width: 768px) {
        .iframe-container { height: 70vh; min-height: 500px; }
      }
      .alert-db-error {
        background: #ffebee; color: #c62828; padding: 0.8rem 1.2rem;
        border-radius: 6px; margin-bottom: 1.2rem; font-weight: 500;
      }
      .stat-card { min-height: 145px; }
      .card-subtitle {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.95) !important;
        font-weight: 500;
        margin-bottom: 0;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
      }
      
      /* 🎨 Style untuk Filter Bulan */
      .filter-bulan-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
      }
      .filter-bulan-wrapper select {
        padding: 6px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        background: white;
        font-size: 14px;
        cursor: pointer;
        transition: border-color 0.2s;
      }
      .filter-bulan-wrapper select:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
      }
      .filter-bulan-wrapper .btn-apply {
        background: linear-gradient(to right, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 6px 20px;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        transition: transform 0.2s;
      }
      .filter-bulan-wrapper .btn-apply:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      }
      .filter-bulan-wrapper label {
        margin: 0;
        font-weight: 500;
        color: #495057;
        font-size: 14px;
      }
    </style>
  </head>
  <body>
    <div class="container-scroller">
      <div class="row p-0 m-0 proBanner" id="proBanner">
        <div class="col-md-12 p-0 m-0">
          <div class="card-body card-body-padding d-flex align-items-center justify-content-between">
            <div class="ps-lg-3">
              <div class="d-flex align-items-center justify-content-between"></div>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <a href="#"><i class="mdi mdi-home me-3 text-white"></i></a>
              <button id="bannerClose" class="btn border-0 p-0">
                <i class="mdi mdi-close text-white mr-0"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Navbar -->
      <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
        <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
          <a class="navbar-brand brand-logo" href="index.php">
            <img src="assets/images/logo.png" alt="logo" />
          </a>
        </div>
        <div class="navbar-menu-wrapper d-flex align-items-stretch">
          <ul class="navbar-nav navbar-nav-right">
            <li class="nav-item nav-profile dropdown">
              <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown">
                <div class="nav-profile-img">
                  <img src="assets/images/profile.jpg" alt="image">
                  <span class="availability-status online"></span>
                </div>
                <div class="nav-profile-text">
                  <p class="mb-1 text-black">Admin</p>
                </div>
              </a>
              <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                <a class="dropdown-item" href="../logout.php">
                  <i class="mdi mdi-logout me-2 text-primary"></i> Keluar
                </a>
              </div>
            </li>
            <li class="nav-item d-none d-lg-block full-screen-link">
              <a class="nav-link"><i class="mdi mdi-fullscreen" id="fullscreen-button"></i></a>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
          </button>
        </div>
      </nav>

      <!-- Sidebar -->
      <div class="container-fluid page-body-wrapper">
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <li class="nav-item nav-profile">
              <a href="#" class="nav-link">
                <div class="nav-profile-image">
                  <img src="assets/images/profile.jpg" alt="profile" />
                  <span class="login-status online"></span>
                </div>
                <div class="nav-profile-text d-flex flex-column">
                  <span class="font-weight-bold mb-2">Admin Dashboard</span>
                  <span class="text-secondary text-small">Admin</span>
                </div>
                <i class="mdi mdi-bookmark-check text-success nav-profile-badge"></i>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="#" onclick="showSection('dashboard'); return false;">
                <span class="menu-title">Dashboard</span>
                <i class="mdi mdi-home menu-icon"></i>
              </a>
            </li>
            <!-- 📊 MENU LAPORAN KEUANGAN BARU -->
            <li class="nav-item">
              <a class="nav-link" href="#" onclick="showSection('laporan'); return false;">
                <span class="menu-title">Laporan Keuangan</span>
                <i class="mdi mdi-file-document-box menu-icon"></i>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" onclick="showSection('produk'); return false;">
                <span class="menu-title">Daftar Produk</span>
                <i class="mdi mdi-pencil menu-icon"></i>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" onclick="showSection('pesanan'); return false;">
                <span class="menu-title">Kelola Pesanan</span>
                <i class="mdi mdi-cart menu-icon"></i>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" onclick="showSection('testimoni'); return false;">
                <span class="menu-title">Lihat Testimoni</span>
                <i class="mdi mdi-comment-text-outline menu-icon"></i>
              </a>
            </li>
            
            <li class="nav-item">
              <a class="nav-link" href="#" onclick="showSection('pengaturan'); return false;">
                <span class="menu-title">PENGATURAN</span>
                <i class="mdi mdi-cog-outline menu-icon"></i>
              </a>
            </li>
          </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-panel">
          <div class="content-wrapper">
            
            <!-- ✅ ERROR HANDLING -->
            <?php if ($error): ?>
            <div class="alert-db-error">
              <i class="mdi mdi-alert-circle-outline me-2"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section active">
              <div class="page-header">
                <h3 class="page-title">
                  <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-home"></i>
                  </span> Dashboard
                </h3>
                <nav aria-label="breadcrumb">
                  <ul class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                      <!-- 🎯 FORM FILTER BULAN -->
                      <form method="GET" action="" class="filter-bulan-wrapper" id="filterForm">
                        <label><i class="mdi mdi-calendar"></i> Laporan:</label>
                        <select name="bulan" id="selectBulan">
                          <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $i == $bulan_pilihan ? 'selected' : '' ?>>
                              <?= namaBulan($i) ?>
                            </option>
                          <?php endfor; ?>
                        </select>
                        <select name="tahun" id="selectTahun">
                          <?php for ($y = 2020; $y <= 2030; $y++): ?>
                            <option value="<?= $y ?>" <?= $y == $tahun_pilihan ? 'selected' : '' ?>>
                              <?= $y ?>
                            </option>
                          <?php endfor; ?>
                        </select>
                        <button type="submit" class="btn-apply">
                          <i class="mdi mdi-filter"></i> Terapkan
                        </button>
                      </form>
                    </li>
                  </ul>
                </nav>
              </div>

              <!-- ✅ ENAM KARTU -->
              <div class="row">
                <!-- Kartu 1: Pendapatan -->
                <div class="col-md-4 stretch-card grid-margin stat-card">
                  <div class="card bg-gradient-danger card-img-holder text-white">
                    <div class="card-body">
                      <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                      <h4 class="font-weight-normal mb-3">
                        Pendapatan <i class="mdi mdi-currency-usd mdi-24px float-end"></i>
                      </h4>
                      <h2 class="mb-2"><?= formatRupiah($pendapatan) ?></h2>
                      <p class="card-subtitle mb-0">Dari pesanan selesai (<?= $bulan_ini ?>)</p>
                    </div>
                  </div>
                </div>

                <!-- Kartu 2: Produk -->
                <div class="col-md-4 stretch-card grid-margin stat-card">
                  <div class="card bg-gradient-success card-img-holder text-white">
                    <div class="card-body">
                      <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                      <h4 class="font-weight-normal mb-3">
                        Total Produk <i class="mdi mdi-package-variant mdi-24px float-end"></i>
                      </h4>
                      <h2 class="mb-2"><?= $jumlah_produk ?></h2>
                      <p class="card-subtitle mb-0">Semua waktu</p>
                    </div>
                  </div>
                </div>

                <!-- Kartu 3: Testimoni -->
                <div class="col-md-4 stretch-card grid-margin stat-card">
                  <div class="card bg-gradient-info card-img-holder text-white">
                    <div class="card-body">
                      <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                      <h4 class="font-weight-normal mb-3">
                        Testimoni <i class="mdi mdi-comment-multiple-outline mdi-24px float-end"></i>
                      </h4>
                      <h2 class="mb-2"><?= $jumlah_testimoni ?></h2>
                      <p class="card-subtitle mb-0">Semua waktu</p>
                    </div>
                  </div>
                </div>

                <!-- Kartu 4: Pesanan Masuk -->
                <div class="col-md-4 stretch-card grid-margin stat-card">
                  <div class="card bg-gradient-warning card-img-holder text-white">
                    <div class="card-body">
                      <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                      <h4 class="font-weight-normal mb-3">
                        Pesanan Masuk <i class="mdi mdi-inbox-arrow-down mdi-24px float-end"></i>
                      </h4>
                      <h2 class="mb-2"><?= $pesanan_masuk ?></h2>
                      <p class="card-subtitle mb-0">Semua status (<?= $bulan_ini ?>)</p>
                    </div>
                  </div>
                </div>

                <!-- Kartu 5: Pesanan Selesai -->
                <div class="col-md-4 stretch-card grid-margin stat-card">
                  <div class="card bg-gradient-primary card-img-holder text-white">
                    <div class="card-body">
                      <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                      <h4 class="font-weight-normal mb-3">
                        Pesanan Selesai <i class="mdi mdi-check-circle mdi-24px float-end"></i>
                      </h4>
                      <h2 class="mb-2"><?= $pesanan_selesai ?></h2>
                      <p class="card-subtitle mb-0">Status: selesai (<?= $bulan_ini ?>)</p>
                    </div>
                  </div>
                </div>

                <!-- Kartu 6: Pesanan Dibatalkan -->
                <div class="col-md-4 stretch-card grid-margin stat-card">
                  <div class="card bg-gradient-dark card-img-holder text-white">
                    <div class="card-body">
                      <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                      <h4 class="font-weight-normal mb-3">
                        Pesanan Dibatalkan <i class="mdi mdi-close-circle-outline mdi-24px float-end"></i>
                      </h4>
                      <h2 class="mb-2"><?= $pesanan_dibatalkan ?></h2>
                      <p class="card-subtitle mb-0">Status: batal (<?= $bulan_ini ?>)</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 📊 SECTION LAPORAN KEUANGAN BARU -->
            <div id="laporan-section" class="content-section">
              <div class="page-header">
                <h3 class="page-title">
                  <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-file-document-box"></i>
                  </span> Laporan Keuangan
                </h3>
                <nav aria-label="breadcrumb">
                  <ul class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                      <span></span>Laporan Penjualan per Bulan
                    </li>
                  </ul>
                </nav>
              </div>
              <div class="row">
                <div class="col-12">
                  <div class="card iframe-card">
                    <div class="card-body">
                      <iframe src="laporan_keuangan.php" class="iframe-container" frameborder="0" scrolling="auto" loading="lazy" title="Laporan Keuangan"></iframe>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Daftar Produk -->
            <div id="produk-section" class="content-section">
              <div class="page-header">
                <h3 class="page-title">
                  <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-pencil"></i>
                  </span> Daftar Produk
                </h3>
                <nav aria-label="breadcrumb">
                  <ul class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                      <span></span>Kelola Produk
                    </li>
                  </ul>
                </nav>
              </div>
              <div class="row">
                <div class="col-12">
                  <div class="card iframe-card">
                    <div class="card-body">
                      <iframe src="daftar_produk.php" class="iframe-container" frameborder="0" scrolling="auto" loading="lazy" title="Daftar Produk"></iframe>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Kelola Pesanan -->
            <div id="pesanan-section" class="content-section">
              <div class="page-header">
                <h3 class="page-title">
                  <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-cart"></i>
                  </span> Kelola Pesanan
                </h3>
                <nav aria-label="breadcrumb">
                  <ul class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                      <span></span>Manajemen Pesanan
                    </li>
                  </ul>
                </nav>
              </div>
              <div class="row">
                <div class="col-12">
                  <div class="card iframe-card">
                    <div class="card-body">
                      <iframe src="../pesanan.php" class="iframe-container" frameborder="0" scrolling="auto" loading="lazy" title="Kelola Pesanan"></iframe>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Testimoni -->
            <div id="testimoni-section" class="content-section">
              <div class="page-header">
                <h3 class="page-title">
                  <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-comment-text-outline"></i>
                  </span> Lihat Testimoni
                </h3>
                <nav aria-label="breadcrumb">
                  <ul class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">
                      <span></span>Testimoni Pelanggan
                    </li>
                  </ul>
                </nav>
              </div>
              <div class="row">
                <div class="col-12">
                  <div class="card iframe-card">
                    <div class="card-body">
                      <iframe src="../lihat_testimoni.php" class="iframe-container" frameborder="0" scrolling="auto" loading="lazy" title="Lihat Testimoni"></iframe>
                    </div>
                  </div>
                </div>
              </div>
            </div>

              <!-- Pengaturan -->
              <div id="pengaturan-section" class="content-section">
                <div class="page-header">
                  <h3 class="page-title">
                    <span class="page-title-icon bg-gradient-primary text-white me-2">
                      <i class="mdi mdi-cog-outline"></i>
                    </span> Pengaturan
                  </h3>
                  <nav aria-label="breadcrumb">
                    <ul class="breadcrumb">
                      <li class="breadcrumb-item active" aria-current="page">
                        <span></span>Kelola Konten Website
                      </li>
                    </ul>
                  </nav>
                </div>
                <div class="row">
                  <div class="col-12">
                    <div class="card iframe-card">
                      <div class="card-body">
                        <iframe src="../pengaturan.php" class="iframe-container" frameborder="0" scrolling="auto" loading="lazy" title="Pengaturan"></iframe>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

          </div>

          
          <!-- Footer -->
          <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
              <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">
                Copyright © <?= date('Y') ?> Dapoer Funraise. All rights reserved.
              </span>
              <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">
                Cemilan rumahan yang bikin nagih! <i class="mdi mdi-heart text-danger"></i>
              </span>
            </div>
          </footer>
        </div>
      </div>
    </div>

    <!-- JS Plugins -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <script src="assets/vendors/chart.js/chart.umd.js"></script>
    <script src="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/misc.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>
    <script src="assets/js/jquery.cookie.js"></script>
    <script src="assets/js/dashboard.js"></script>

    <script>
      function showSection(section) {
        document.querySelectorAll('.content-section').forEach(el => el.classList.remove('active'));
        const target = document.getElementById(section + '-section');
        if (target) target.classList.add('active');
        
        document.querySelectorAll('.nav-item').forEach(el => {
          el.classList.remove('active');
          if (el.querySelector(`[onclick="showSection('${section}')"]`)) {
            el.classList.add('active');
          }
        });
      }

      document.getElementById('bannerClose')?.addEventListener('click', function() {
        document.getElementById('proBanner').style.display = 'none';
      });

      window.addEventListener('DOMContentLoaded', () => {
        showSection('dashboard');
      });
    </script>
  </body>
</html>