<?php

include 'koneksi.php';

header('Content-Type: application/json');

// Koneksi ke database

// Query untuk mengambil data barang
$result = $conn->query("SELECT tanggal, kode AS kode_barang, nama_barang, masuk AS total_masuk, keluar AS total_keluar, jumlah AS stock FROM barang");

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Kembalikan data sebagai JSON
echo json_encode($data);

$conn->close();
?>
