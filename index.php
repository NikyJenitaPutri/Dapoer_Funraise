<?php
session_start();
require 'config.php';
$user = $_SESSION['username'] ?? null;

// Ambil 3 testimoni terbaru berdasarkan dikirim_pada
$stmtTesti = $pdo->query("
    SELECT id, nama, nama_produk, komentar, dikirim_pada 
    FROM testimoni 
    ORDER BY dikirim_pada DESC 
    LIMIT 3
");
$testimoni_terbaru = $stmtTesti->fetchAll();

// Path background hero
$hero_bg = file_exists(__DIR__ . '/assets/bg.jpg') 
    ? 'assets/bg.jpg' 
    : 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80';
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
            min-height: 100vh;
            padding: 100px 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            scroll-snap-align: start;
        }
        .section-title {
            font-size: 3.2rem;
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
            padding: 60px 20px;
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
            align-items: center;
        }
        .about-content {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
        }
        .about-content p {
            font-size: 1.15rem;
            line-height: 1.8;
            color: #444;
            margin-bottom: 1.5rem;
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
            aspect-ratio: 4 / 3;
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
                <img src="assets/logo.png" alt="Logo Dapoer Funraise" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <div class="logo-text">
                <span class="logo-main">Dapoer Funraise</span>
                <span class="logo-sub">Cemilan rumahan yang bikin nagih!</span>
            </div>
        </div>
        <ul class="nav-links">
            <li><a href="#beranda">Beranda</a></li>
            <li><a href="#cara-pesan">Cara Pesan</a></li>
            <li><a href="#tentang-kami">Tentang Kami</a></li>
            <li><a href="#testimoni-section">Testimoni</a></li>
            <li><a href="#kontak">Kontak</a></li>
        </ul>
        <div class="button-area">
            <?php if ($user): ?>
                <a href="logout.php" class="nav-menu">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar</span>
                </a>
                <a href="./admin/index.php" class="nav-menu">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            <?php else: ?>
                <a href="login.php" class="nav-menu">
                    <i class="fas fa-user"></i>
                    <span>Masuk</span>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <section id="beranda" class="fade-in">
            <div class="hero-content">
                <h2 class="section-title">Dapoer <span class="highlight">Funraise</span></h2>
                <p class="section-subtitle">Cemilan rumahan lezat, bergizi, dan mendukung kegiatan sosial sekolah.</p>
                <a href="produk.php" class="btn btn-primary">
                    <i class="fa-solid fa-cookie-bite"></i> Lihat Produk
                </a>
            </div>
        </section>

        <section id="cara-pesan" class="fade-in">
            <h2 class="section-title">Cara Pesan</h2>
            <p class="section-subtitle">Mudah dan cepat, hanya dalam 4 langkah</p>
            <div class="order-container">
                <div class="order-card">
                    <i class="fa-solid fa-cookie-bite"></i>
                    <h3>1. Lihat Produk</h3>
                    <p>Jelajahi semua produk di halaman produk & pilih favorit Anda.</p>
                </div>
                <div class="order-card">
                    <i class="fa-solid fa-cart-plus"></i>
                    <h3>2. Tambah ke Keranjang</h3>
                    <p>Klik “Tambah ke Keranjang” untuk simpan sementara pesanan.</p>
                </div>
                <div class="order-card">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <h3>3. Lihat & Atur Keranjang</h3>
                    <p>Periksa isi keranjang, ubah jumlah, atau hapus produk dan isi detail pesanan.</p>
                </div>
                <div class="order-card">
                    <i class="fa-brands fa-whatsapp"></i>
                    <h3>4. Checkout ke WhatsApp</h3>
                    <p>Klik “buat pesanan”, lalu pesan otomatis terkirim ke WhatsApp kami</p>
                </div>
            </div>
        </section>

        <section id="tentang-kami" class="fade-in">
            <h2 class="section-title">Tentang Kami</h2>
            <p class="section-subtitle">Dapur kecil, dampak besar untuk pendidikan</p>
            <div class="about-content-wrapper">
                <div class="about-content">
                    <p>
                        Dapoer Funraise adalah wujud kepedulian alumni MAN 2 Samarinda dalam mendukung 
                        <strong>Expo Campus MAN 2 Samarinda</strong> — acara tahunan untuk memperkenalkan perguruan tinggi kepada siswa.
                        Seluruh keuntungan penjualan cemilan digunakan untuk kebutuhan acara: konsumsi, dekorasi, dan logistik.
                        Kami percaya: bisnis kecil bisa berdampak besar!
                    </p>
                </div>
                <div class="photo-carousel">
                    <div class="carousel-wrapper" id="carouselWrapper">
                        <div class="carousel-slide">
                            <div class="photo-grid">
                                <div class="photo-item"><img src="assets/kegiatan1.jpg" alt="Foto Kegiatan 1"></div>
                            </div>
                        </div>
                        <div class="carousel-slide">
                            <div class="photo-grid">
                                <div class="photo-item"><img src="assets/kegiatan2.jpg" alt="Foto Kegiatan 2"></div>
                            </div>
                        </div>
                        <div class="carousel-slide">
                            <div class="photo-grid">
                                <div class="photo-item"><img src="assets/kegiatan3.jpg" alt="Foto Kegiatan 3"></div>
                            </div>
                        </div>
                        <div class="carousel-slide">
                            <div class="photo-grid">
                                <div class="photo-item"><img src="assets/kegiatan4.jpg" alt="Foto Kegiatan 4"></div>
                            </div>
                        </div>
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

        <section id="kontak" class="fade-in">
            <h2 class="section-title">Hubungi Kami</h2>
            <p class="section-subtitle">Siap melayani pesanan Anda dengan senang hati</p>
            <div class="contact-cards">
                <div class="contact-card">
                    <div class="card-icon"><i class="fa-brands fa-whatsapp"></i></div>
                    <div class="card-title">WhatsApp</div>
                    <a href="https://wa.me/6283129704643" class="contact-link whatsapp-btn" target="_blank" rel="noopener noreferrer">Yunisa</a>
                </div>
                <div class="contact-card">
                    <div class="card-icon"><i class="fa-brands fa-instagram"></i></div>
                    <div class="card-title">Instagram</div>
                    <a href="https://instagram.com/dapoerfunraise" class="contact-link whatsapp-btn" target="_blank">@dapoerfunraise</a>
                </div>
                <div class="contact-card">
                    <div class="card-icon"><i class="fa-solid fa-location-dot"></i></div>
                    <div class="card-title">Alamat</div>
                    <a href="https://maps.google.com/?q=Jl.+Harmonika+No.+98,+Samarinda,+Kalimantan+Timur" 
                        target="_blank" 
                        class="contact-link whatsapp-btn">
                        <i class="fa-solid fa-map-location-dot"></i> Buka di Google Maps
                    </a>
                </div>
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
        <p>© 2025 <strong>Dapoer Funraise</strong> — Mendukung Expo Campus MAN 2 Samarinda</p>
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
    });
    </script>
</body>
</html>