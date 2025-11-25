<?php
session_start();
require 'config.php';

// // Cek login
// if (!isset($_SESSION['username'])) {
//     header('Location: login.php');
//     exit;
// }

// $pengguna = $_SESSION['username'];

// Ambil statistik
$totalProduk = $pdo->query("SELECT COUNT(*) FROM produk")->fetchColumn();
$totalTestimoni = $pdo->query("SELECT COUNT(*) FROM testimoni")->fetchColumn();
$totalLangkah = $pdo->query("SELECT COUNT(*) FROM langkah_cara_order WHERE aktif = 1")->fetchColumn();
$totalGaleri = $pdo->query("SELECT COUNT(*) FROM galeri_foto WHERE aktif = 1")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Dapoer Funraise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #B64B62;
            --secondary: #5A46A2;
            --accent: #F9CC22;
            --sukses: #10b981;
            --peringatan: #f59e0b;
            --bahaya: #ef4444;
            --gelap: #2a1f3d;
            --terang: #f8f9fa;
            --krem: #FFF5EE;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--krem) 0%, #fef8f4 100%);
            min-height: 100vh;
        }
        .header-admin {
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            color: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 4px 20px rgba(90, 70, 162, 0.25);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-admin h1 {
            font-size: 1.8rem;
            font-weight: 700;
        }
        .navigasi-admin {
            display: flex;
            gap: 1rem;
        }
        .navigasi-admin a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 12px;
            background: rgba(255,255,255,0.1);
            transition: all 0.3s;
            font-weight: 600;
        }
        .navigasi-admin a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
        }
        .wadah {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        .grid-statistik {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .kartu-statistik {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: transform 0.3s;
        }
        .kartu-statistik:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .ikon-statistik {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }
        .ikon-statistik.primary { background: linear-gradient(135deg, var(--primary), #d05876); }
        .ikon-statistik.secondary { background: linear-gradient(135deg, var(--secondary), #7058c4); }
        .ikon-statistik.sukses { background: linear-gradient(135deg, var(--sukses), #34d399); }
        .ikon-statistik.peringatan { background: linear-gradient(135deg, var(--peringatan), #fbbf24); }
        .konten-statistik h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--gelap);
        }
        .konten-statistik p {
            color: #666;
            font-size: 1rem;
        }
        .grid-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .kartu-menu {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .kartu-menu:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(90, 70, 162, 0.2);
        }
        .ikon-menu {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            color: white;
        }
        .kartu-menu h3 {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
            color: var(--gelap);
        }
        .kartu-menu p {
            color: #666;
            font-size: 0.95rem;
        }
        .bagian-sambutan {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .bagian-sambutan h2 {
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }
        @media (max-width: 768px) {
            .header-admin {
                flex-direction: column;
                gap: 1rem;
            }
            .grid-statistik,
            .grid-menu {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="header-admin">
        <h1><i class="fas fa-tachometer-alt"></i> Dashboard Admin</h1>
        <nav class="navigasi-admin">
            <a href="../index.php"><i class="fas fa-home"></i> Ke Website</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </nav>
    </header>

    <div class="wadah">
        <div class="bagian-sambutan">
            <!-- <h2>Selamat Datang, <?= htmlspecialchars($pengguna) ?>! </h2> -->
            <p>Kelola seluruh konten website Dapoer Funraise dari sini.</p>
        </div>

        <div class="grid-statistik">
            <div class="kartu-statistik">
                <div class="ikon-statistik primary">
                    <i class="fas fa-cookie-bite"></i>
                </div>
                <div class="konten-statistik">
                    <h3><?= $totalProduk ?></h3>
                    <p>Total Produk</p>
                </div>
            </div>
            <div class="kartu-statistik">
                <div class="ikon-statistik sukses">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="konten-statistik">
                    <h3><?= $totalTestimoni ?></h3>
                    <p>Total Testimoni</p>
                </div>
            </div>
            <div class="kartu-statistik">
                <div class="ikon-statistik secondary">
                    <i class="fas fa-list-ol"></i>
                </div>
                <div class="konten-statistik">
                    <h3><?= $totalLangkah ?></h3>
                    <p>Langkah Order</p>
                </div>
            </div>
            <div class="kartu-statistik">
                <div class="ikon-statistik peringatan">
                    <i class="fas fa-images"></i>
                </div>
                <div class="konten-statistik">
                    <h3><?= $totalGaleri ?></h3>
                    <p>Foto Galeri</p>
                </div>
            </div>
        </div>

        <h2 style="margin: 2rem 0 1rem; color: var(--gelap);">Menu Pengaturan</h2>
        <div class="grid-menu">
            <a href="pengaturan_umum.php" class="kartu-menu">
                <div class="ikon-menu"><i class="fas fa-cog"></i></div>
                <h3>Pengaturan Umum</h3>
                <p>Edit teks, logo, dan konten website</p>
            </a>
            <a href="cara_order.php" class="kartu-menu">
                <div class="ikon-menu"><i class="fas fa-clipboard-list"></i></div>
                <h3>Cara Order</h3>
                <p>Kelola langkah-langkah pemesanan</p>
            </a>
            <a href="galeri.php" class="kartu-menu">
                <div class="ikon-menu"><i class="fas fa-images"></i></div>
                <h3>Galeri Foto</h3>
                <p>Upload dan kelola foto kegiatan</p>
            </a>
            <a href="kontak.php" class="kartu-menu">
                <div class="ikon-menu"><i class="fas fa-address-book"></i></div>
                <h3>Informasi Kontak</h3>
                <p>Edit WhatsApp, Instagram, Alamat</p>
            </a>
            <a href="testimoni.php" class="kartu-menu">
                <div class="ikon-menu"><i class="fas fa-star"></i></div>
                <h3>Kelola Testimoni</h3>
                <p>Lihat dan moderasi testimoni</p>
            </a>
            <a href="../produk.php" class="kartu-menu">
                <div class="ikon-menu"><i class="fas fa-cookie-bite"></i></div>
                <h3>Kelola Produk</h3>
                <p>Tambah, edit, hapus produk</p>
            </a>
        </div>
    </div>
</body>
</html>