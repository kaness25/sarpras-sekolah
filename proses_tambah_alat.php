<?php
session_start();
include 'config.php';

if ($_SESSION['role'] != 'admin') { exit(); }

$nama_alat = mysqli_real_escape_string($conn, $_POST['nama_alat']);
$kategori  = mysqli_real_escape_string($conn, $_POST['kategori']);
$stok      = (int)$_POST['stok'];

// Logika status otomatis: jika stok ada, maka available
$status = ($stok > 0) ? 'available' : 'borrowed';

$query = "INSERT INTO alat (nama_alat, kategori, stok, status) 
          VALUES ('$nama_alat', '$kategori', '$stok', '$status')";

if (mysqli_query($conn, $query)) {
    // Redirect ke halaman stok_barang.php (file yang kita buat di awal tadi)
    header("Location: stok_barang.php?pesan=berhasil");
} else {
    echo "Gagal menambahkan data: " . mysqli_error($conn);
}
?>