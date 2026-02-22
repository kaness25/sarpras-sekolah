<?php
include 'config.php';

$id = $_POST['id'];
$nama = $_POST['nama_alat'];
$stok = $_POST['stok'];

// Logika: Jika stok > 0 maka status available, jika 0 maka borrowed/habis
$status = ($stok > 0) ? 'available' : 'borrowed';

$query = "UPDATE alat SET 
          nama_alat = '$nama', 
          stok = '$stok', 
          status = '$status' 
          WHERE id = '$id'";

if (mysqli_query($conn, $query)) {
    header("Location: stok_barang.php"); // Kembali ke halaman daftar stok
} else {
    echo "Gagal update: " . mysqli_error($conn);
}
?>