<?php
session_start();
require 'config.php';

// Ambil data header
$stmtHeader = $pdo->query("SELECT logo_path, business_name, tagline FROM header WHERE id = 1");
$header = $stmtHeader->fetch(PDO::FETCH_ASSOC);
if (!$header) {
    $header = [
        'logo_path' => 'assets/logo.png',
        'business_name' => 'Dapoer Funraise',
        'tagline' => 'Cemilan rumahan yang bikin nagih!'
    ];
}

// Ambil semua label dari DB
$stmt = $pdo->query("SELECT label FROM navigation_links ORDER BY id ASC");
$labels = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Mapping label → href (pastikan konsisten dengan ID section di HTML)
$hrefMap = [
    'Beranda'       => '#beranda',
    'Cara Pesan'    => '#cara-pesan',
    'Tentang Kami'  => '#tentang-kami',
    'Testimoni'     => '#testimoni-section',
    'Kontak'        => '#kontak',
];

// Bangun array $navLinks agar kompatibel dengan template
$navLinks = [];
foreach ($labels as $label) {
    $href = $hrefMap[trim($label)] ?? '#';
    $navLinks[] = ['label' => $label, 'href' => $href];
}

// Testimoni tetap
$stmtTesti = $pdo->query("
    SELECT id, nama, nama_produk, komentar, dikirim_pada 
    FROM testimoni 
    ORDER BY dikirim_pada DESC 
    LIMIT 3
");
$testimoni_terbaru = $stmtTesti->fetchAll();

// Ambil data hero dari DB (tambahkan di awal index.php, setelah header)
$stmtHero = $pdo->query("SELECT background_path, cta_button_text FROM hero_section WHERE id = 1");
$heroData = $stmtHero->fetch(PDO::FETCH_ASSOC);
$hero_bg = $heroData['background_path'] ?? 'assets/bg.jpg';
$cta_text = $heroData['cta_button_text'] ?? 'Lihat Produk';


$stmtCaraPesan = $pdo->query("
    SELECT title, subtitle FROM cara_pesan_section WHERE id = 1
");
$caraPesanSec = $stmtCaraPesan->fetch(PDO::FETCH_ASSOC);
$cara_title = $caraPesanSec['title'] ?? 'Cara Pesan';
$cara_subtitle = $caraPesanSec['subtitle'] ?? 'Mudah dan cepat, hanya dalam 4 langkah';

$stmtSteps = $pdo->query("
    SELECT * FROM cara_pesan_steps 
    WHERE is_active = 1 
    ORDER BY sort_order ASC, step_number ASC
");
$cara_steps = $stmtSteps->fetchAll();


// --- AMBIL DATA KONTAK SECTION (title & subtitle) ---
$stmtKontakSec = $pdo->prepare("SELECT title, subtitle FROM kontak_section WHERE id = 1");
$stmtKontakSec->execute();
$kontak_section = $stmtKontakSec->fetch(PDO::FETCH_ASSOC);
if (!$kontak_section) {
    $kontak_section = [
        'title'    => 'Hubungi Kami',
        'subtitle' => 'Siap melayani pesanan Anda dengan senang hati'
    ];
}

// --- AMBIL CARD KONTAK AKTIF, DIURUTKAN ---
$stmtCards = $pdo->prepare("
    SELECT icon_class, title, label, href 
    FROM contact_cards 
    WHERE is_active = 1 
    ORDER BY sort_order ASC, id ASC
");
$stmtCards->execute();
$contact_cards = $stmtCards->fetchAll(PDO::FETCH_ASSOC);


// --- AMBIL DATA FOOTER (SESUAI PREFERENSI: TERPISAH, SINGLE ROW) ---
$stmtFooter = $pdo->prepare("SELECT main_text, copyright_text FROM footer_section WHERE id = 1 AND is_active = 1");
$stmtFooter->execute();
$footerData = $stmtFooter->fetch(PDO::FETCH_ASSOC);

if (!$footerData) {
    // fallback (aman & sesuai branding)
    $footerData = [
        'main_text' => 'Mendukung Expo Campus MAN 2 Samarinda',
        'copyright_text' => '© 2025 <strong>Dapoer Funraise</strong>'
    ];
}

$timeline_items = [
            ['date' => 'AUG 2024', 'desc' => 'Ide Awal Fundraising'],
            ['date' => 'NOV 2024', 'desc' => 'Validasi Konsep Produk'],
            ['date' => 'FEB 2025', 'desc' => 'Pembentukan Tim Inti'],
            ['date' => 'MAR 2025', 'desc' => 'Dapoer Funraise Beroperasi'],
            ['date' => 'MAY 2025', 'desc' => 'Laporan Keuntungan Final']
        ];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dapoer Funraise</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #B64B62;
            --secondary: #5A46A2;
            --accent: #F9CC22;
            --purple-light: #DFBEE0;
            --purple-mid: #9180BB;
            --cream: #FFF5EE;
            --dark: #2a1f3d;
            --font-main: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            --transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            --shadow-md: 0 12px 30px rgba(90, 70, 162, 0.15);
            --shadow-lg: 0 24px 50px rgba(90, 70, 162, 0.2);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-main);
            color: #333;
            overflow-x: hidden;
            scroll-behavior: smooth;
            background: linear-gradient(135deg, var(--cream) 0%, #fef8f4 100%);
            line-height: 1.6;
        }
        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; height: auto; display: block; }
        .container { width: 90%; max-width: 1400px; margin: 0 auto; }

        /* === BUTTONS === */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 16px 32px;
            border-radius: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            border: none;
            outline: none;
            box-shadow: var(--shadow-md);
            white-space: nowrap;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #d05876);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 16px 40px rgba(182, 75, 98, 0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, var(--secondary), #7058c4);
            color: white;
        }
        .btn-secondary:hover {
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 16px 40px rgba(90, 70, 162, 0.4);
        }
        .btn-outline {
            background: transparent;
            border: 2px solid white;
            color: white;
        }
        .btn-outline:hover {
            background: white;
            color: var(--secondary);
            transform: translateY(-3px);
        }

        /* 🔹 HEADER PREMIUM */
        .app-header {
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            color: white;
            padding: 1.2rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            box-shadow: 0 4px 20px rgba(90, 70, 162, 0.25);
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
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            z-index: 1;
        }
        .app-header > * { position: relative; z-index: 2; }
        .logo {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            transition: transform 0.3s ease;
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
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .logo-sub {
            font-size: 0.95rem;
            font-weight: 500;
            opacity: 0.9;
            color: rgba(255,255,255,0.95);
            margin-top: -2px;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 1.2rem;
            margin: 0;
            padding: 0;
        }
        .nav-links a {
            font-weight: 600;
            font-size: 1.05rem;
            position: relative;
            color: rgba(255,255,255,0.92);
            transition: var(--transition);
        }
        .nav-links a:hover {
            color: white;
        }
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: white;
            border-radius: 1px;
            transition: var(--transition);
        }
        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-menus {
            display: flex;
            gap: 1rem;
        }
        .nav-menu {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 0.98rem;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        .nav-menu:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* === SECTIONS === */
        section {
            min-height: 85vh;
            padding: 100px 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            scroll-snap-align: start;
        }
        .section-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1.2rem;
            text-align: center;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
        }
        .section-subtitle {
            font-size: 1.4rem;
            margin-bottom: 3rem;
            text-align: center;
            max-width: 800px;
            color:var(--dark);
            line-height: 1.6;
        }

        /* === BERANDA === */
        #beranda {
            background: url('<?= $hero_bg ?>') center/cover no-repeat;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        #beranda::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1;
        }
        #beranda .hero-content {
            max-width: 950px;
            position: relative;
            z-index: 2;
        }
        #beranda h2 {
            font-size: 4.4rem;
            font-weight: 900;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 12px rgba(0,0,0,0.25);
        }
        #beranda .highlight {
            background: linear-gradient(to right, var(--accent), var(--purple-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        #beranda p {
            font-size: 1.6rem;
            margin-bottom: 2.8rem;
            font-weight: 400;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0.95;
        }

        /* === CARA ORDER === */
        #cara-order { background: var(--cream); }
        .order-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            width: 100%;
            max-width: 1300px;
        }
        .order-card {
            background: white;
            padding: 2.5rem 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
            background-clip: padding-box;
        }
        .order-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            z-index: -1;
            border-radius: 20px;
            opacity: 0;
            transition: var(--transition);
        }
        .order-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }
        .order-card:hover::before { opacity: 1; }
        .order-card i {
            font-size: 2.8rem;
            margin-bottom: 1.4rem;
            transition: var(--transition);
            color: var(--secondary);
        }
        .order-card:hover i { color: white; transform: scale(1.2); }
        .order-card h3 {
            font-size: 1.6rem;
            margin-bottom: 1rem;
            color: var(--dark);
            transition: var(--transition);
        }
        .order-card:hover h3 { color: white; }
        .order-card p {
            font-size: 1.05rem;
            color: #555;
            line-height: 1.6;
            transition: var(--transition);
        }
        .order-card:hover p { color: rgba(255,255,255,0.9); }

        /* === KONTAK === */
        #kontak {
            background: white;
            color: #333;
        }
        .contact-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 28px;
            max-width: 1200px;
        }
        .contact-card {
            background: linear-gradient(135deg, #B64B62, #8e3a4d);
            backdrop-filter: blur(12px);
            padding: 2.4rem 1.8rem;
            border-radius: 20px;
            text-align: center;
            transition: var(--transition);
            border: 1px solid rgba(182, 75, 98, 0.2);
            color: white;
        }
        .contact-card:hover {
            background: linear-gradient(135deg, #d05876, #B64B62);
            transform: translateY(-6px) scale(1.02);
            box-shadow: 0 16px 40px rgba(182, 75, 98, 0.3);
        }
        .card-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--accent), var(--purple-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.2rem;
            font-size: 2rem;
            color: var(--dark);
            box-shadow: 0 4px 12px rgba(249, 204, 34, 0.3);
        }
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: white;
        }
        .contact-link {
            display: block;
            font-size: 1.2rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }
        .whatsapp-btn {
            background: linear-gradient(135deg, var(--accent), #ffd84d);
            color: var(--dark) !important;
            padding: 10px 24px;
            border-radius: 12px;
            display: inline-block;
            margin-top: 10px;
            font-weight: 700;
        }

        /* === TESTIMONI & FORM COMBINED === */
        #testimoni-section {
            
            background: linear-gradient(135deg, var(--purple-light), var(--cream));
            padding: 80px 20px;
        }
        .testimoni-combined {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            max-width: 1400px;
            width: 100%;
            margin: 0 auto;
        }
        .testimoni-combined > div {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }
        .testimoni-combined > div:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-lg);
        }
        .testimoni-combined h3 {
            font-size: 2rem;
            margin-bottom: 1.2rem;
            color: var(--secondary);
            font-weight: 700;
        }
        .testimoni-combined p.subtitle {
            margin-bottom: 1.8rem;
            font-size: 1.1rem;
            color: #666;
        }

        /* 🔻 ACCORDION TESTIMONI — RAPI, INTUITIF, RESPONSIF */
        .testimoni-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .testimoni-accordion {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border: 1px solid #f0e6f6;
            transition: box-shadow 0.3s ease;
        }
        .testimoni-accordion:hover {
            box-shadow: 0 6px 18px rgba(90,70,162,0.1);
        }
        .accordion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 1.5rem;
            cursor: pointer;
            background: #fbf8ff;
            transition: background 0.3s ease;
        }
        .accordion-header:hover {
            background: #f8f4ff;
        }
        .header-content {
            flex: 1;
        }
        .header-content cite {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.12rem;
            display: block;
        }
        .testimoni-date {
            font-size: 0.88rem;
            color: #666;
            margin-top: 4px;
        }
        .chevron {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            transition: transform 0.3s ease;
        }
        .testimoni-accordion.active .chevron {
            transform: rotate(180deg);
        }
        .accordion-body {
            max-height: 0;
            overflow: hidden;
            background: white;
            transition: max-height 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .testimoni-accordion.active .accordion-body {
            max-height: 400px;
        }
        .accordion-body blockquote {
            margin: 0;
            padding: 0 1.5rem 1.5rem;
            position: relative;
        }
        .accordion-body blockquote::before {
            content: '"';
            position: absolute;
            top: 10px;
            left: 10px;
            font-size: 2.4rem;
            color: var(--accent);
            opacity: 0.2;
            font-family: serif;
        }
        .accordion-body blockquote p {
            font-size: 1.05rem;
            line-height: 1.6;
            color: #333;
            margin: 1rem 0 0.8rem;
            font-style: italic;
        }
        .testimoni-product {
            font-size: 0.95rem;
            color: var(--secondary);
            font-weight: 600;
            margin-top: 0.6rem;
        }

        /* Empty state */
        .no-testimoni {
            text-align: center;
            padding: 2.2rem 1.2rem;
            color: #777;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        }
        .no-testimoni i {
            font-size: 2.2rem;
            color: #ddd;
            margin-bottom: 1rem;
        }
        .no-testimoni p {
            font-size: 1.1rem;
            margin-top: 0.5rem;
        }

        /* === FORM TESTIMONI === */
        .form-testimoni {
            width: 100%;
        }
        .form-row {
            margin-bottom: 1.6rem;
        }
        .form-row label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--dark);
        }
        .form-row input,
        .form-row textarea {
            width: 100%;
            padding: 14px 18px;
            border-radius: 12px;
            border: 2px solid var(--purple-light);
            background: var(--cream);
            color: #333;
            font-size: 1.05rem;
            transition: var(--transition);
        }
        .form-row input:focus,
        .form-row textarea:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(90, 70, 162, 0.2);
        }
        .form-row textarea { resize: vertical; min-height: 130px; }

        /* === TENTANG KAMI === */
        #tentang-kami {
            background: white;
            padding: 40px 20px;
        }
        /* NEW: Timeline Section - Ringkas & SATU BARIS */
        /* Hapus semua style box yang tidak diperlukan */
        /* Kontainer Utama Timeline */
       /* Kontainer Utama Timeline */
        
      /* --- KODE CSS LENGKAP UNTUK TIMELINE --- */
        
        /* Kontainer Utama Timeline */
        .timeline-container {
            display: flex; 
            flex-wrap: nowrap;
            justify-content: space-between;
            gap: 1px;
            padding-top: 7px; 
            padding-bottom: 10px;
            overflow-x: hidden;
            background: transparent;
            border: none;
            position: relative; 
            z-index: 1;
            margin-bottom: 20px; 
            /* Tambahkan padding kanan-kiri untuk ruang panah */
            padding-left: 10px; 
            padding-right: 10px;
        }
        
        /* Garis Penghubung Horizontal (Menggunakan ::before pada kontainer) */
        .timeline-container::before {
            content: '';
            position: absolute;
            top: 40px; /* Posisi vertikal garis */
            left: 10px; /* Dimulai setelah panah kiri */
            right: 10px; /* Berakhir sebelum panah kanan */
            height: 2px;
            background: #e0e0e0;
            z-index: -1;
        }

        /* Panah KANAN (Menggunakan ::after pada kontainer) */
        .timeline-container::after {
            content: '';
            position: absolute;
            top: 33px; /* 40px - 4px (agar panah di tengah garis) */
            right: 10px; 
            width: 0;
            height: 0;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
            border-left: 8px solid #e0e0e0; /* Panah menunjuk ke KIRI */
            z-index: 0; 
        }
        
        /* Panah KIRI (Menggunakan ::before pada elemen dot PERTAMA, tambahkan ini di bawah) */
        .timeline-dot-item:first-child::before {
            content: '';
            position: absolute;
            top: 33px; 
            left: 10px; 
            width: 0;
            height: 0;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
            border-right: 8px solid #e0e0e0; /* Panah menunjuk ke KANAN */
            z-index: 0;
            transform: translateX(-100%); /* Dorong panah agar tepat di luar padding kiri kontainer */
        }
        
        /* Koreksi margin dot agar tepat di tengah */
        .timeline-dot {
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
            margin: 30px auto 5px; /* Margin atas 30px agar dot berada di posisi 40px */
            box-shadow: 0 0 0 4px rgba(182, 75, 98, 0.1);
            transition: all 0.3s ease;
        }
        .timeline-dot-item.dot-active .timeline-dot {
            background: var(--secondary);
            box-shadow: 0 0 0 6px rgba(90, 70, 162, 0.3);
            transform: scale(1.2);
        }
        .timeline-date {
            display: block;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 3px;
            font-size: 0.75rem;
            text-align: center;
        }
        .timeline-dot-item p {
            font-size: 0.85rem;
            line-height: 1.3;
            color: #555;
            margin: 0;
            /* Memotong teks agar tidak terlalu panjang, jika diperlukan */
            text-align: center;
            overflow: hidden; 
            text-overflow: ellipsis; 
            white-space: normal;
        }

        /* KOREKSI: Wrapper Detail Box */
        .timeline-detail-box-wrapper {
            width: 100%;
            /* Ganti height: auto; menjadi max-height: 0 dan overflow: hidden; */
            max-height: 0; 
            overflow: hidden; 
            
            margin-bottom: 0px; /* Set margin ke 0 saat tertutup */
            position: relative;
            
            /* Tambahkan transisi untuk animasi tinggi */
            transition: max-height 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94), margin-bottom 0.4s;
        }

        /* NEW: Saat ada kotak detail yang aktif, wrapper menyesuaikan tinggi */
        .timeline-detail-box-wrapper.active {
             /* Nilai yang cukup besar untuk menampung konten kotak detail */
            max-height: 500px; 
            margin-bottom: 20px; /* Kembalikan margin agar ada jarak ke kotak teks di bawah */
        }

        .timeline-dot-item.clickable {
            cursor: pointer; /* Menunjukkan bahwa item dapat diklik */
        }

        .timeline-detail-box {
            /* Gaya Box */
            background: var(--cream);
            border: 2px solid var(--primary);
            border-radius: 12px;
            padding: 15px;
            
            /* HAPUS position: absolute; agar memakan ruang */
            position: static; 
            width: 100%; /* Mengisi lebar penuh */
            
            /* Animasi untuk transisi yang halus */
            transition: opacity 0.3s ease-in-out;
            z-index: 10;
            margin-top: 15px; /* Tambahkan jarak ke timeline dot */
        }

        .timeline-detail-box.hidden {
            display: none; /* Menyembunyikan secara default */
            opacity: 0;
        }
        
        /* NEW: Saat detail box aktif (bersamaan dengan wrapper aktif) */
        .timeline-detail-box.active {
            opacity: 1;
            display: block; /* Pastikan display block agar terlihat */
        }

        .timeline-detail-box h4 {
            color: var(--secondary);
            font-size: 1.2rem;
            margin-top: 0;
            margin-bottom: 8px;
        }
        .timeline-detail-box p {
            font-size: 1rem;
            line-height: 1.5;
            color: #555;
            margin-bottom: 0;
        }
        
        #tentang-kami .section-title {
            margin-bottom: 1.5rem;
        }
        #tentang-kami .section-subtitle {
            margin-bottom: 2rem;
            color: var(--dark);
        }
        .about-content-wrapper {
            max-width: 1200px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: flex-start;
        }
        .about-content {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
        }
        .about-content p {
            font-size: 1rem;
            line-height: 1.8;
            color: #444;
            margin-bottom: 0.5rem;
        }

        /* === PHOTO CAROUSEL === */
        .photo-carousel {
            width: 100%;
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(90, 70, 162, 0.2);
            background: white;
        }
        .carousel-wrapper {
            display: flex;
            scroll-behavior: smooth;
            scroll-snap-type: x mandatory;
        }
        .carousel-slide {
            min-width: 100%;
            scroll-snap-align: start;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 0;
        }
        .photo-grid {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
        }
        .photo-item {
            width: 100%;
            max-width: 500px;
            aspect-ratio: 16 / 10.8;
            overflow: hidden;
            border-radius: 12px;
        }
        .photo-item:hover {
            transform: scale(1.02);
        }
        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .carousel-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
            transform: translateY(-50%);
            z-index: 10;
        }
        .carousel-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255,255,255,0.85);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: var(--secondary);
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(90, 70, 162, 0.3);
        }
        .carousel-btn:hover {
            background: white;
            transform: scale(1.1);
        }
        .carousel-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
            transform: none;
        }

        /* === FOOTER === */
        footer {
            background: linear-gradient(135deg, var(--secondary), var(--dark));
            color: rgba(255,255,255,0.85);
            text-align: center;
            padding: 28px 20px;
            font-size: 1.05rem;
            font-weight: 500;
        }

        /* === ANIMATIONS === */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease, transform 0.8s ease;
        }
        .fade-in.appear {
            opacity: 1;
            transform: translateY(0);
        }

        /* Alert */
        .alert {
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 1.8rem;
            font-weight: 600;
            text-align: center;
            font-size: 1.1rem;
            max-width: 100%;
            width: 100%;
        }
        .alert-sukses {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* BACK TO TOP */
        .back-to-top {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, var(--primary), #d05876);
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: 0 6px 16px rgba(182, 75, 98, 0.4);
            z-index: 99;
            bottom: max(24px, env(safe-area-inset-bottom, 16px));
            right: max(24px, env(safe-area-inset-right, 16px));
            z-index: 1000;
        }
        .back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .back-to-top:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 8px 24px rgba(182, 75, 98, 0.6);
            background: linear-gradient(135deg, #d05876, var(--primary));
        }
        .back-to-top:focus {
            outline: 2px solid white;
            outline-offset: 2px;
        }

        /* === RESPONSIVE MEDIA QUERIES === */
        @media (max-width: 1024px) {
            .section-title { font-size: 2.6rem; }
            .section-subtitle { font-size: 1.2rem; }
            #beranda h2 { font-size: 3.4rem; }
            #beranda p { font-size: 1.4rem; }
            .order-container,
            .contact-cards,
            .testimoni-combined,
            .about-content-wrapper {
                grid-template-columns: 1fr;
                gap: 24px;
            }
            .testimoni-combined > div { padding: 30px 20px; }
            .logo-main { font-size: 1.6rem; }
            .logo-sub { font-size: 0.9rem; }
            .nav-links { display: none; }
        }
        @media (max-width: 768px) {
            .app-header { padding: 1rem; flex-wrap: wrap; }
            .logo { gap: 10px; }
            .logo-icon { width: 44px; height: 44px; font-size: 1.5rem; }
            .logo-main { font-size: 1.4rem; }
            .logo-sub { font-size: 0.85rem; margin-top: -4px; }
            .button-area { display: flex; gap: 8px; }
            section { padding: 80px 16px; min-height: auto; }
            .section-title { font-size: 2rem; }
            .section-subtitle { font-size: 1.1rem; margin-bottom: 2rem; }
            #beranda h2 { font-size: 2.6rem; line-height: 1.3; }
            #beranda p { font-size: 1.2rem; }
            .order-card { padding: 2rem 1.5rem; }
            .order-card i { font-size: 2.3rem; }
            .order-card h3 { font-size: 1.4rem; }
            .order-card p { font-size: 1rem; }
            .carousel-btn { width: 44px; height: 44px; font-size: 1.2rem; }
            .accordion-header { padding: 1rem 1.2rem; }
            .header-content cite { font-size: 1.05rem; }
            .testimoni-date { font-size: 0.85rem; }
            .form-row input,
            .form-row textarea { padding: 12px 16px; font-size: 1rem; }
            .btn { padding: 14px 24px; font-size: 1rem; }
        }
        @media (max-width: 480px) {
            .section-title { font-size: 1.8rem; }
            #beranda h2 { font-size: 2.2rem; }
            #beranda p { font-size: 1.1rem; }
            .logo-main { font-size: 1.3rem; }
            .logo-sub { display: none; }
            .button-area a { padding: 12px 18px; font-size: 0.95rem; }
            .carousel-btn { width: 40px; height: 40px; }
            .photo-item { max-width: 90vw; aspect-ratio: 3 / 2; }
            .contact-card { padding: 20px 16px; }
            footer { padding: 20px 16px; font-size: 0.95rem; }
        }
    </style>
</head>
<body>
    <header class="app-header">
        <div class="logo">
            <div class="logo-icon">
                <img src="<?= htmlspecialchars($header['logo_path']) ?>" alt="Logo <?= htmlspecialchars($header['business_name']) ?>" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div class="logo-text">
                <span class="logo-main"><?= htmlspecialchars($header['business_name']) ?></span>
                <span class="logo-sub"><?= htmlspecialchars($header['tagline']) ?></span>
            </div>
        </div>

        <ul class="nav-links">
            <?php foreach ($navLinks as $link): ?>
                <li><a href="<?= htmlspecialchars($link['href']) ?>"><?= htmlspecialchars($link['label']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </header>

    <main>
        <section id="beranda" class="fade-in">
            <div class="hero-content">
                <a href="produk.php" class="btn btn-primary">
                    <i class="fa-solid fa-cookie-bite"></i> <?= htmlspecialchars($cta_text) ?>
                </a>
            </div>
        </section>

        <section id="cara-pesan" class="fade-in">
            <h2 class="section-title"><?= htmlspecialchars($cara_title) ?></h2>
            <p class="section-subtitle"><?= htmlspecialchars($cara_subtitle) ?></p>
            <div class="order-container">
                <?php foreach ($cara_steps as $step): ?>
                    <div class="order-card">
                        <i class="fa-solid <?= htmlspecialchars($step['icon_class']) ?>"></i>
                        <h3><?= htmlspecialchars($step['step_number']) ?>. <?= htmlspecialchars($step['title']) ?></h3>
                        <p><?= htmlspecialchars($step['description']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <?php
        // --- AMAN: Ambil data tentang_kami_section ---
        $stmtTentang = $pdo->prepare("SELECT title, subtitle, content FROM tentang_kami_section WHERE id = 1");
        $stmtTentang->execute();
        $tentang = $stmtTentang->fetch(PDO::FETCH_ASSOC);

        // Jika belum ada data, gunakan fallback (sesuai preferensi Anda)
        if (!$tentang) {
            $tentang = [
                'title'    => 'Tentang Kami',
                'subtitle' => 'Dapur kecil, dampak besar untuk pendidikan',
                'content'  => '
                    Dapoer Funraise adalah wujud kepedulian alumni MAN 2 Samarinda dalam mendukung 
                    <strong>Expo Campus MAN 2 Samarinda</strong> — acara tahunan untuk memperkenalkan perguruan tinggi kepada siswa.
                    Seluruh keuntungan penjualan cemilan digunakan untuk kebutuhan acara. konsumsi, dekorasi, dan logistik.
                    Kami percaya: bisnis kecil bisa berdampak besar!
                    
                '
            ];
        }

        // --- AMAN: Ambil foto carousel aktif ---
        $stmtPhotos = $pdo->prepare("
            SELECT image_path, alt_text 
            FROM carousel_photos 
            WHERE is_active = 1 
            ORDER BY sort_order ASC, id ASC
        ");
        $stmtPhotos->execute();
        $photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

        // Fallback jika belum ada foto
        if (empty($photos)) {
            $photos = [
                ['image_path' => 'assets/kegiatan1.jpg', 'alt_text' => 'Tim Dapoer Funraise'],
                ['image_path' => 'assets/kegiatan2.jpg', 'alt_text' => 'Kegiatan Expo Campus 2024'],
                ['image_path' => 'assets/kegiatan3.jpg', 'alt_text' => 'Proses pembuatan cemilan'],
                ['image_path' => 'assets/kegiatan4.jpg', 'alt_text' => 'Distribusi cemilan ke acara']
            ];
        }
        ?>

       <?php
        // ... (Data $tentang, $photos)
        
        // --- TAMBAHAN: Data Timeline Sementara (PERLU DIPINDAH KE ATAS) ---
       $timeline_items = [
    // Tambahkan 'title' dan 'long_desc'
    ['date' => 'AUG 2024', 'desc' => 'Ide Awal Fundraising', 'title' => 'Riset dan Perencanaan', 'long_desc' => 'Pada tahap ini, kami melakukan riset pasar dan membuat perencanaan awal untuk konsep penggalangan dana Expo Campus.'],
    
    // Contoh item lain:
    ['date' => 'NOV 2024', 'desc' => 'Validasi Konsep Produk', 'title' => 'Pengembangan Konsep Awal', 'long_desc' => 'Kami mulai memvalidasi resep-resep cemilan andalan kami dan memastikan logistik untuk produksi massal.'],
    
    ['date' => 'FEB 2025', 'desc' => 'Pembentukan Tim Inti', 'title' => 'Tim dan Sumber Daya', 'long_desc' => 'Pembentukan tim inti yang solid, meliputi divisi produksi, pemasaran, dan keuangan.'],
    
    ['date' => 'MAR 2025', 'desc' => 'Dapoer Funraise Beroperasi', 'title' => 'Peluncuran Resmi', 'long_desc' => 'Dapoer Funraise resmi beroperasi, menerima pesanan untuk pertama kalinya.'],
    
    ['date' => 'MAY 2025', 'desc' => 'Laporan Keuntungan', 'title' => 'Laporan dan Donasi', 'long_desc' => 'Kami menyusun laporan keuangan dan menyerahkan donasi guna mendukung acara expo campus MAN 2 untuk pertama kalinya dan terus kami lakukan hingga sekarang.'],
];
        // --- AKHIR Data Timeline Sementara ---
        ?>

        <section id="tentang-kami" class="fade-in">
            <h2 class="section-title"><?= htmlspecialchars($tentang['title']) ?></h2>
            <p class="section-subtitle"><?= htmlspecialchars($tentang['subtitle']) ?></p>
            <div class="about-content-wrapper">
                
                <div class="about-left-column">
                    
                    <div class="timeline-container">
                        <?php 
                        $i = 0; // Tambahkan counter untuk ID
                        foreach ($timeline_items as $item): ?>
                            <div class="timeline-dot-item clickable" data-timeline-id="item-<?= $i ?>">
                                <div class="timeline-dot"></div>
                                <span class="timeline-date"><?= htmlspecialchars($item['date']) ?></span>
                                <p><?= htmlspecialchars($item['desc']) ?></p>
                            </div>
                        <?php 
                        $i++;
                        endforeach; ?>
                    </div>

                    <div class="timeline-detail-box-wrapper">
    <?php 
    $j = 0; // Index untuk ID unik
    foreach ($timeline_items as $item): ?>
        <div class="timeline-detail-box hidden" id="detail-item-<?= $j ?>">
            <h4><?= htmlspecialchars($item['title']) ?></h4>
            <p><?= htmlspecialchars($item['long_desc']) ?></p> </div>
    <?php 
    $j++;
    endforeach; ?>
</div>
                    
                    <div class="about-content">
                        <p>
                            <?= $tentang['content'] ?>
                        </p>
                    </div>
                </div>
                <div class="photo-carousel">
                    <div class="carousel-wrapper" id="carouselWrapper">
                        <?php foreach ($photos as $p): ?>
                            <div class="carousel-slide">
                                <div class="photo-grid">
                                    <div class="photo-item">
                                        <img src="<?= htmlspecialchars($p['image_path']) ?>" 
                                            alt="<?= htmlspecialchars($p['alt_text']) ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="carousel-nav">
                        <button class="carousel-btn" id="prevBtn" aria-label="Foto sebelumnya">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button class="carousel-btn" id="nextBtn" aria-label="Foto berikutnya">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                </div>
        </section>
        <section id="testimoni-section" class="fade-in">
            <h2 class="section-title">Testimoni & Kirim Pesan</h2>
            <p class="section-subtitle">Dengar dari pelanggan kami dan bagikan pengalaman Anda!</p>
            <div class="testimoni-combined">
                <div>
                    <h3>Testimoni Pelanggan</h3>
                    <p class="subtitle">3 terbaru — jujur & hangat</p>
                    <div class="testimoni-list">
                        <?php if ($testimoni_terbaru): ?>
                            <?php foreach ($testimoni_terbaru as $t): ?>
                                <div class="testimoni-accordion">
                                    <div class="accordion-header">
                                        <div class="header-content">
                                            <cite><?= htmlspecialchars($t['nama']) ?></cite>
                                            <div class="testimoni-date"><?= date('d M Y', strtotime($t['dikirim_pada'])) ?></div>
                                        </div>
                                        <div class="chevron">
                                            <i class="fas fa-chevron-down"></i>
                                        </div>
                                    </div>
                                    <div class="accordion-body">
                                        <blockquote>
                                            <p>"<?= nl2br(htmlspecialchars($t['komentar'])) ?>"</p>
                                            <?php if (!empty($t['nama_produk'])): ?>
                                                <div class="testimoni-product">Produk: <strong><?= htmlspecialchars($t['nama_produk']) ?></strong></div>
                                            <?php endif; ?>
                                        </blockquote>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-testimoni">
                                <i class="fas fa-comment"></i>
                                <p>Belum ada testimoni. Jadilah yang pertama!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <h3>Kirim Testimoni Anda</h3>
                    <p class="subtitle">Bagikan pengalaman Anda!</p>
                    <?php if (isset($_SESSION['pesan_sukses'])): ?>
                        <div class="alert alert-sukses">
                            <?= htmlspecialchars($_SESSION['pesan_sukses']); ?>
                        </div>
                        <?php unset($_SESSION['pesan_sukses']); ?>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['pesan_error'])): ?>
                        <div class="alert alert-error">
                            <?= htmlspecialchars($_SESSION['pesan_error']); ?>
                        </div>
                        <?php unset($_SESSION['pesan_error']); ?>
                    <?php endif; ?>
                    <form action="kirim_testimoni.php" method="POST" class="form-testimoni">
                        <div class="form-row">
                            <label for="nama">Nama Anda</label>
                            <input type="text" id="nama" name="nama" placeholder="Contoh: Budi Santoso" required>
                        </div>
                        <div class="form-row">
                            <label for="nama_produk">Nama Produk (Opsional)</label>
                            <input type="text" id="nama_produk" name="nama_produk" placeholder="Contoh: Tahu Crispy">
                        </div>
                        <div class="form-row">
                            <label for="komentar">Testimoni Anda</label>
                            <textarea id="komentar" name="komentar" rows="5" placeholder="Ceritakan pengalaman Anda..." required></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-paper-plane"></i> Kirim Testimoni
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <?php
        // --- AMBIL DATA KONTAK SECTION (title & subtitle) ---
        $stmtKontakSec = $pdo->prepare("SELECT title, subtitle FROM kontak_section WHERE id = 1");
        $stmtKontakSec->execute();
        $kontak_section = $stmtKontakSec->fetch(PDO::FETCH_ASSOC);
        if (!$kontak_section) {
            $kontak_section = [
                'title'    => 'Hubungi Kami',
                'subtitle' => 'Siap melayani pesanan Anda dengan senang hati'
            ];
        }

        // --- AMBIL CARD KONTAK AKTIF, DIURUTKAN ---
        $stmtCards = $pdo->prepare("
            SELECT icon_class, title, label, href 
            FROM contact_cards 
            WHERE is_active = 1 
            ORDER BY sort_order ASC, id ASC
        ");
        $stmtCards->execute();
        $contact_cards = $stmtCards->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <section id="kontak" class="fade-in">
            <h2 class="section-title"><?= htmlspecialchars($kontak_section['title']) ?></h2>
            <p class="section-subtitle"><?= htmlspecialchars($kontak_section['subtitle']) ?></p>
            <div class="contact-cards">
                <?php if ($contact_cards): ?>
                    <?php foreach ($contact_cards as $card): ?>
                        <?php
                        $isWhatsApp = stripos(strtolower($card['icon_class']), 'whatsapp') !== false;
                        $linkClass = $isWhatsApp ? 'whatsapp-btn' : '';
                        ?>
                        <div class="contact-card">
                            <div class="card-icon">
                                <i class="fa <?= htmlspecialchars($card['icon_class']) ?>"></i>
                            </div>
                            <div class="card-title"><?= htmlspecialchars($card['title']) ?></div>
                            <a href="<?= htmlspecialchars($card['href']) ?>"
                            class="contact-link <?= $linkClass ?>"
                            target="_blank"
                            rel="noopener noreferrer">
                                <?= htmlspecialchars($card['label']) ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-testimoni" style="grid-column: 1 / -1;">
                        <i class="fas fa-exclamation-circle"></i>
                        <p>Belum ada kontak yang aktif. Silakan tambahkan via halaman admin.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <button id="btnBackToTop" 
                class="back-to-top" 
                aria-label="Kembali ke atas"
                title="Kembali ke atas">
            <i class="fa-solid fa-arrow-up"></i>
        </button>
    </main>

    <footer>
        <p>
            <?= $footerData['copyright_text'] ?> — 
            <?= htmlspecialchars($footerData['main_text']) ?>
        </p>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // ▼▼▼ 1–8. Fade-in, smooth scroll, carousel, WA, etc. (tidak diubah) ▼▼▼
        if ('scrollRestoration' in window.history) window.history.scrollRestoration = 'manual';
        window.scrollTo(0, 0);

        const fadeElements = document.querySelectorAll('.fade-in');
        const fadeObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('appear');
                    fadeObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        fadeElements.forEach(el => fadeObserver.observe(el));

        const header = document.querySelector('.app-header');
        const navLinks = document.querySelectorAll('a[href^="#"]');
        function getHeaderHeight() { return header ? header.offsetHeight : 80; }
        navLinks.forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (!targetId || targetId === '#') return;
                const target = document.querySelector(targetId);
                if (!target) return;
                const offset = getHeaderHeight() + 20;
                const targetPosition = target.getBoundingClientRect().top + window.scrollY;
                const scrollPosition = targetPosition - offset;
                window.scrollTo({ top: scrollPosition, behavior: 'smooth' });
            });
        });

        const carousel = document.getElementById('carouselWrapper');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const slides = document.querySelectorAll('.carousel-slide');
        let currentIndex = 0;
        let isAnimating = false;
        if (carousel && prevBtn && nextBtn && slides.length > 0) {
            function updateCarousel() {
                if (isAnimating) return;
                isAnimating = true;
                carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
                carousel.style.transition = 'transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                prevBtn.disabled = currentIndex === 0;
                nextBtn.disabled = currentIndex === slides.length - 1;
                setTimeout(() => isAnimating = false, 500);
            }
            function goToSlide(index) {
                if (index >= 0 && index < slides.length && !isAnimating) {
                    currentIndex = index;
                    updateCarousel();
                }
            }
            prevBtn.addEventListener('click', () => goToSlide(currentIndex - 1));
            nextBtn.addEventListener('click', () => goToSlide(currentIndex + 1));
            updateCarousel();

            document.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') goToSlide(currentIndex - 1);
                else if (e.key === 'ArrowRight') goToSlide(currentIndex + 1);
            });

            let touchStartX = 0;
            carousel.addEventListener('touchstart', (e) => { touchStartX = e.touches[0].clientX; }, { passive: true });
            carousel.addEventListener('touchend', (e) => {
                if (!touchStartX) return;
                const touchEndX = e.changedTouches[0].clientX;
                const diff = touchStartX - touchEndX;
                if (Math.abs(diff) > 50) {
                    if (diff > 0) goToSlide(currentIndex + 1);
                    else goToSlide(currentIndex - 1);
                }
                touchStartX = 0;
            }, { passive: true });
        }

        const namaInput = document.getElementById('nama');
        if (namaInput && window.location.hash === '#testimoni-section') {
            setTimeout(() => {
                namaInput.focus();
                namaInput.style.borderColor = '#B64B62';
                setTimeout(() => { namaInput.style.borderColor = ''; }, 2000);
            }, 400);
        }

        const whatsappLinks = document.querySelectorAll('a[href^="https://wa.me/6283129704643"], a[href^="http://wa.me/6283129704643"]');
        whatsappLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                const webUrl = url.replace('wa.me/6283129704643', 'web.whatsapp.com/send?phone=6283129704643');
                window.location.href = url;
                const fallbackTimer = setTimeout(() => {
                    if (!document.hidden) {
                        if (confirm('WhatsApp tidak terdeteksi. Buka di browser?')) {
                            window.open(webUrl, '_blank');
                        }
                    }
                }, 1500);
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) clearTimeout(fallbackTimer);
                }, { once: true });
            });
        });

        const alertEl = document.querySelector('.alert');
        if (alertEl) setTimeout(() => window.scrollTo({ top: 0, behavior: 'smooth' }), 300);

        const btnBackToTop = document.getElementById('btnBackToTop');
        if (btnBackToTop) {
            function updateScrollButton() {
                const threshold = Math.min(window.innerHeight, 400);
                btnBackToTop.classList.toggle('show', window.scrollY > threshold);
            }
            window.addEventListener('scroll', updateScrollButton);
            window.addEventListener('resize', updateScrollButton);
            updateScrollButton();
            btnBackToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        }

        

        // ▼▼▼ 9. ACCORDION TESTIMONI (3 item, rapi & interaktif) ▼▼▼
        document.querySelectorAll('.testimoni-accordion').forEach(acc => {
            const header = acc.querySelector('.accordion-header');
            const body = acc.querySelector('.accordion-body');
            
            header.addEventListener('click', () => {
                const isActive = acc.classList.contains('active');
                
                // Tutup semua selain yang diklik (single-open)
                document.querySelectorAll('.testimoni-accordion').forEach(el => {
                    el.classList.remove('active');
                    el.querySelector('.accordion-body').style.maxHeight = '0';
                });
                
                // Buka yang diklik
                if (!isActive) {
                    acc.classList.add('active');
                    body.style.maxHeight = body.scrollHeight + 'px';
                }
            });
        });


        // ▼▼▼ 10. TIMELINE INTERAKTIF (DOT KE BOX) - KOREKSI FINAL: MENGHILANGKAN JARAK BESAR ▼▼▼
        const timelineItems = document.querySelectorAll('.timeline-dot-item.clickable');
        const wrapper = document.querySelector('.timeline-detail-box-wrapper');
        
        timelineItems.forEach((item) => {
            item.addEventListener('click', function() {
                const targetId = this.getAttribute('data-timeline-id');
                const targetBox = document.getElementById(`detail-${targetId}`);

                if (!targetBox || !wrapper) return;

                const isActive = targetBox.classList.contains('active');
                
                // 1. Tutup/Reset semua (Kotak Detail & Wrapper)
                document.querySelectorAll('.timeline-detail-box').forEach(box => {
                    box.classList.remove('active');
                    box.classList.add('hidden');
                });
                wrapper.classList.remove('active'); 
                
                // 2. Hapus status aktif dari semua dot
                timelineItems.forEach(dot => dot.classList.remove('dot-active'));
                
                if (!isActive) {
                    // 3. Buka yang diklik
                    targetBox.classList.remove('hidden');
                    // Tambahkan timeout untuk transisi opacity (opsional)
                    setTimeout(() => targetBox.classList.add('active'), 10); 
                    
                    // 4. Tandai dot sebagai aktif dan buka wrapper
                    this.classList.add('dot-active');
                    wrapper.classList.add('active'); 
                }
            });
        });
    });
    </script>
</body>
</html>