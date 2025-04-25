<?php
include 'koneksi.php'; // Pastikan file koneksi database di-include

// Query untuk mengambil data dari riwayat_keluar dan nama_barang dari tabel barang
$query = "SELECT r.id_barang, r.nama_pengambil, r.jumlah, r.tanggal_pengambilan, b.nama_barang
          FROM riwayat_keluar r
          JOIN barang b ON r.id_barang = b.id
          ORDER BY r.tanggal_pengambilan DESC"; // Menambahkan JOIN dengan tabel barang

$result = $conn->query($query);

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Menambahkan data ke dalam array $data
        $data[] = $row;
    }
}

// Mengembalikan data dalam format JSON
header('Content-Type: application/json');
echo json_encode($data);
?>

