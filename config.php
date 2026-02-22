<?php
$host = "localhost";
$user = "root";
$pass = ""; // Laragon defaultnya kosong
$db   = "peminjaman_sekolah"; // Tetap pakai nama database yang kamu buat di HeidiSQL/phpMyAdmin

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>