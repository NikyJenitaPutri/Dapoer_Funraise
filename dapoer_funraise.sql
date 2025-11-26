CREATE DATABASE dapoer_funraise;
USE dapoer_funraise;

CREATE TABLE produk (
  ID INT NOT NULL AUTO_INCREMENT,
  Nama VARCHAR(100) NOT NULL,
  Harga DECIMAL(15,2) NOT NULL,
  Varian VARCHAR(255) DEFAULT NULL,
  Deskripsi_Produk TEXT,
  Foto_Produk VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (ID)
);

CREATE TABLE pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_pelanggan VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    produk TEXT NOT NULL,   
    total DECIMAL(15,2) NOT NULL,
    pengambilan ENUM('ambil','antar') NOT NULL,
    metode_bayar ENUM('cash','tf') NOT NULL,
    status ENUM('baru','diproses','selesai','batal') DEFAULT 'baru',
    whatsapp_link TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE testimoni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nama_produk VARCHAR(100),
    komentar TEXT NOT NULL,
    dikirim_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE header (
    id INT PRIMARY KEY AUTO_INCREMENT,
    logo_path VARCHAR(255) NOT NULL DEFAULT 'assets/logo.png',
    business_name VARCHAR(100) NOT NULL DEFAULT 'Dapoer Funraise',
    tagline VARCHAR(150) NOT NULL DEFAULT 'Cemilan rumahan yang bikin nagih!',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE navigation_links (
    id INT PRIMARY KEY AUTO_INCREMENT,
    label VARCHAR(50) NOT NULL,
    href VARCHAR(100) NOT NULL, -- e.g., "#beranda"
    sort_order INT NOT NULL DEFAULT 0
);
-- Insert default:
INSERT INTO navigation_links (label, href, sort_order) VALUES
('Beranda', '#beranda', 1),
('Cara Pesan', '#cara-pesan', 2),
('Tentang Kami', '#tentang-kami', 3),
('Testimoni', '#testimoni-section', 4),
('Kontak', '#kontak', 5);

CREATE TABLE hero_section (
    id INT PRIMARY KEY AUTO_INCREMENT,
    background_path VARCHAR(255) NOT NULL DEFAULT 'assets/bg.jpg',
    cta_button_text VARCHAR(100) NOT NULL DEFAULT 'Lihat Produk',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE cara_pesan_section (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL DEFAULT 'Cara Pesan',
    subtitle VARCHAR(255) NOT NULL DEFAULT 'Mudah dan cepat, hanya dalam 4 langkah',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE cara_pesan_steps (
    id INT PRIMARY KEY AUTO_INCREMENT,
    icon_class VARCHAR(100) NOT NULL DEFAULT 'fa-cookie-bite', -- from Font Awesome
    step_number TINYINT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE TABLE tentang_kami_section (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL DEFAULT 'Tentang Kami',
    subtitle VARCHAR(255) NOT NULL DEFAULT 'Dapur kecil, dampak besar untuk pendidikan',
    content TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE carousel_photos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(150),
    sort_order INT NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE kontak_section (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL DEFAULT 'Kontak',
    subtitle VARCHAR(255) NOT NULL DEFAULT 'Siap melayani pesanan Anda dengan senang hati',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE contact_cards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    icon_class VARCHAR(100) NOT NULL, -- e.g., 'fa-brands fa-whatsapp'
    title VARCHAR(100) NOT NULL,
    label VARCHAR(100) NOT NULL,  -- e.g., "Yunisa", "@dapoerfunraise"
    href VARCHAR(255) NOT NULL,   -- full URL
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT 1
);

CREATE TABLE footer_section (
    id INT PRIMARY KEY CHECK (id = 1),
    main_text TEXT NOT NULL,
    copyright_text VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT 1
);
drop table footer_section;