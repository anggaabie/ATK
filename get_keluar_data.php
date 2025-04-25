<?php

ob_start(); // Hindari output sebelum header JSON
header('Content-Type: application/json');

include 'koneksi.php'; // Pastikan koneksi ke database sudah benar

try {
    // Query untuk menghitung total keluar berdasarkan bulan dan nama barang
    $sql = "SELECT MONTH(tanggal) AS bulan, YEAR(tanggal) AS tahun, 
                   nama_barang, 
                   SUM(CAST(keluar AS UNSIGNED)) AS total_keluar
            FROM barang
            GROUP BY tahun, bulan, nama_barang
            ORDER BY tahun, bulan, nama_barang";

    $result = $conn->query($sql);

    // Periksa apakah hasil query valid
    if (!$result) {
        throw new Exception("Query error: " . $conn->error);
    }

    $data = [];

    // Ambil hasil query
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'bulan' => (int)$row['bulan'], // Konversi ke integer
            'tahun' => (int)$row['tahun'], // Konversi ke integer
            'nama_barang' => $row['nama_barang'], // Nama barang
            'keluar' => (int)$row['total_keluar'] // Total barang keluar (SUM)
        ];
    }

    // Return data dalam format JSON
    echo json_encode($data, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    // Tangkap kesalahan dan tampilkan pesan error dalam JSON
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

// Bersihkan buffer output
ob_end_flush();
?>
