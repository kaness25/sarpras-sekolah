<?php
include 'config.php';

if(isset($_POST['update'])){
    $id = $_POST['id'];
    $stok = (int)$_POST['stok'];
    $nama_alat = mysqli_real_escape_string($conn, $_POST['nama_alat']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);

    // Logika Status
    $status = ($stok > 0) ? 'ready' : 'borrowed';

    // Ambil data file foto yang diunggah
    $foto = $_FILES['foto']['name'];
    $tmp_name = $_FILES['foto']['tmp_name'];

    // Cek apakah user mengunggah foto baru
    if(!empty($foto)){
        // 1. Beri nama unik agar tidak bentrok (contoh: 11022026_kabel.jpg)
        $foto_baru = date('dmYHis') . '_' . $foto;
        $path = "images/" . $foto_baru;

        // 2. Upload file ke folder images
        if(move_uploaded_file($tmp_name, $path)){
            // 3. Ambil nama foto lama untuk dihapus dari folder (agar storage tidak penuh)
            $query_lama = mysqli_query($conn, "SELECT foto FROM alat WHERE id = '$id'");
            $data_lama = mysqli_fetch_assoc($query_lama);
            if($data_lama['foto'] != 'default.png' && file_exists("images/".$data_lama['foto'])){
                unlink("images/".$data_lama['foto']);
            }

            // 4. Update Database (Termasuk Nama Foto Baru)
            $query = "UPDATE alat SET 
                        nama_alat = '$nama_alat', 
                        kategori = '$kategori', 
                        stok = '$stok', 
                        status = '$status', 
                        foto = '$foto_baru' 
                      WHERE id = '$id'";
        }
    } else {
        // Jika TIDAK upload foto baru, hanya update data teks saja
        $query = "UPDATE alat SET 
                    nama_alat = '$nama_alat', 
                    kategori = '$kategori', 
                    stok = '$stok', 
                    status = '$status' 
                  WHERE id = '$id'";
    }

    // Eksekusi Query
    if(mysqli_query($conn, $query)){
        header("Location: stok_barang.php?pesan=update_berhasil");
    } else {
        echo "Gagal: " . mysqli_error($conn);
    }
}
?>