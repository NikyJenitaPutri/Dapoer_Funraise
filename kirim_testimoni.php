<?php
session_start();
require 'config.php'; // Pastikan file ini berisi koneksi PDO Anda ($pdo)

// Cek apakah metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil dan bersihkan data dari form
    $nama = trim($_POST['nama'] ?? '');
    $nama_produk = trim($_POST['nama_produk'] ?? ''); // Field baru
    $komentar = trim($_POST['komentar'] ?? ''); // Ganti dari 'pesan'

    // Validasi: pastikan nama dan komentar tidak kosong
    if (empty($nama) || empty($komentar)) {
        $_SESSION['pesan_error'] = 'Nama dan Testimoni tidak boleh kosong.';
        header('Location: index.php#form-testimoni'); // Arahkan ke ID baru
        exit;
    }

    // Jika validasi lolos, masukkan ke database
    try {
        $stmt = $pdo->prepare("INSERT INTO testimoni (nama, nama_produk, komentar) VALUES (:nama, :nama_produk, :komentar)");
        $stmt->execute([
            'nama' => $nama,
            'nama_produk' => $nama_produk,
            'komentar' => $komentar
        ]);

        $_SESSION['pesan_sukses'] = 'Terima kasih! Testimoni Anda telah berhasil kami terima.';
        header('Location: index.php#form-testimoni'); // Arahkan ke ID baru
        exit;
    } catch (PDOException $e) {
        // Jika terjadi error saat menyimpan
        // error_log($e->getMessage()); // Untuk debugging di server log
        $_SESSION['pesan_error'] = 'Maaf, terjadi kesalahan pada sistem. Silakan coba lagi nanti.';
        header('Location: index.php#form-testimoni'); // Arahkan ke ID baru
        exit;
    }

} else {
    // Jika file diakses langsung tanpa metode POST, alihkan ke halaman utama
    header('Location: index.php');
    exit;
}
?>