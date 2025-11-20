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
