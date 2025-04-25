<?php
include 'koneksi.php';

session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['username'])) {
    // Jika belum login, arahkan ke halaman login
    header("Location: login.php");
    exit;
}

// Jika sudah login, tampilkan halaman index


// Menambahkan barang

if (isset($_POST['tambah'])) {
    $nama_barang = $_POST['nama_barang'];
    $jumlah = isset($_POST['jumlah']) ? $_POST['jumlah'] : 0;  // This will be set to the initial quantity (masuk)
    $kode = $_POST['kode'];
    $tanggal = $_POST['tanggal'];
    $masuk = isset($_POST['masuk']) ? $_POST['masuk'] : 0;
    $keluar = isset($_POST['keluar']) ? $_POST['keluar'] : 0;

    // Insert the new item into the database
    $sql = "INSERT INTO barang (kode, tanggal, nama_barang, jumlah, keluar, masuk) 
    VALUES ('$kode','$tanggal', '$nama_barang', $jumlah, $keluar, $masuk)";

    if ($conn->query($sql)) {
        // Set session untuk notifikasi
        $_SESSION['success'] = "Barang berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Gagal menambahkan barang: " . $conn->error;
    }

    // Redirect after adding the item
    header("Location: index.php");
    exit();
}



// Menghapus barang
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $sql = "DELETE FROM barang WHERE id=$id";
    if  ($conn->query($sql)){
        $_SESSION['success'] = "Data berhasil dihapus!";
    }else {
        $_SESSION['error'] = "Gagal menghapus data: ". $conn->error;
    }

    // Redirect after deleting the item
    header("Location: index.php");
    exit();
}

if (isset($_POST['update'])) {
    $id = $_POST['id']; // ID barang
    $jumlah = $_POST['jumlah']; // Jumlah barang
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : 'keluar'; // Default ke 'keluar'

    if ($aksi === 'masuk') {
        // Update jumlah barang dan tambahkan ke kolom 'jumlah' dan 'masuk'
        $sql = "UPDATE barang SET jumlah = jumlah + $jumlah, masuk = masuk + $jumlah WHERE id = $id";
        if ($conn->query($sql)) {
            $_SESSION['success'] = "Berhasil menambahkan jumlah barang masuk!";
        } else {
            $_SESSION['error'] = "Gagal menambahkan barang masuk!";
        }
    } elseif ($aksi === 'keluar') {
        // Data dari modal
        $nama_pengambil = $_POST['nama_pengambil'];
        $tanggal_pengambilan = $_POST['tanggal_pengambilan'];

        // Update tabel barang
        $sql = "UPDATE barang SET jumlah = jumlah - $jumlah, keluar = keluar + $jumlah WHERE id = $id";
        if ($conn->query($sql)) {
            // Simpan data transaksi ke tabel transaksi_keluar
            $sql_transaksi = "INSERT INTO riwayat_keluar (id_barang, nama_pengambil, jumlah, tanggal_pengambilan) 
                              VALUES ($id, '$nama_pengambil', $jumlah, '$tanggal_pengambilan')";
            if ($conn->query($sql_transaksi)) {
                $_SESSION['success'] = "Berhasil mengurangkan stock barang !";
            } else {
                $_SESSION['error'] = "Gagal mencatat transaksi keluar!";
            }
        } else {
            $_SESSION['error'] = "Gagal mengurangi barang!";
        }
    }

    // Redirect setelah update untuk menampilkan pesan flash
    header("Location: index.php");
    exit();
}





// Form untuk menambah barang


// Tentukan jumlah data per halaman
$limit = 20;

// Ambil halaman saat ini dari query string, jika tidak ada setel halaman ke 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Hitung offset
$offset = ($page - 1) * $limit;

// Get search keyword from the query string
$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';

// Modify the query to filter results based on search
if ($searchKeyword) {
    $searchKeyword = "%$searchKeyword%";
    // Ambil data berdasarkan ID yang lebih besar dari ID yang ditampilkan pada halaman sebelumnya
    $sql = "SELECT * FROM barang WHERE nama_barang LIKE ? OR kode LIKE ? ORDER BY id LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $searchKeyword, $searchKeyword, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Default query jika tidak ada pencarian
    $sql = "SELECT * FROM barang ORDER BY id LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}


// Mengambil data barang
$sql = "SELECT * FROM barang";
$result = $conn->query($sql);

// Total masuk dan keluar barang
$sql_total_masuk = "SELECT SUM(masuk) as total_masuk FROM barang";
$result_masuk = $conn->query($sql_total_masuk);
$total_masuk = $result_masuk->fetch_assoc()['total_masuk'];

$sql_total_keluar = "SELECT SUM(keluar) as total_keluar FROM barang";
$result_keluar = $conn->query($sql_total_keluar);
$total_keluar = $result_keluar->fetch_assoc()['total_keluar'];

// Mengambil data barang
$sql = "SELECT * FROM barang";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris Barang</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        /* Sidebar Styling */
.sidebar {
    width: 250px;
    background: #343a40;
    color: #fff;
    min-height: 100vh;
    padding: 15px;
    position: fixed;
    top: 0;
    left: 0;
}

/* Sidebar Logo */
.sidebar-header {
    display: flex;
    align-items: center;
}

.sidebar-logo {
    width: 40px;  /* Set the size of the logo */
    height: 40px; /* Make height equal to width to ensure it stays circular */
    border-radius: 50%; /* This makes the logo circular */
    object-fit: cover; /* Ensures the logo doesn't get distorted */
    margin-right: 10px; /* Space between logo and text */
}

.sidebar h4 {
    color: #fff;
    font-size: 20px;
    margin-bottom: 0;
}


.sidebar a {
    color: #fff;
    text-decoration: none;
    display: block;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
    font-size: 16px;
}

.sidebar a:hover {
    background: #495057;
}

/* Content Section Adjustment */
.container-fluid {
    display: flex;
}

.container {
    margin-left: 270px; /* Adjust for the sidebar width */
    padding-top: 20px;
}

.table {
    margin-top: 20px;
    width: 100%;
}

/* Modal Adjustments */
.modal-dialog {
    max-width: 500px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        margin-left: 0;
    }

    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }
}

        .main-content {
            margin-left: 260px;
            padding: 20px;
        }


        .table-warning {
    background-color: #fffbcc !important; /* Light yellow */
}



.notification-box {
    max-width: 400px; /* Maksimal lebar kotak */
    width: 100%; /* Agar responsif */
    margin: 20px auto; /* Menempatkan notifikasi di tengah halaman */
    margin-left: 370px; /* Geser sedikit ke kanan */
    text-align: center; /* Agar isi teks berada di tengah */
    border-radius: 5px; /* Menambahkan sudut yang lebih lembut */
}



.pagination {
        margin-top: 20px;
        text-align: center;
    }
    .pagination a,
    .pagination .btn {
        margin: 0 5px;
    }
    .pagination .disabled {
        background-color: #f8f9fa;
        border-color: #ddd;
    }

    header {
            width: 100%; /* Lebar penuh */
            padding: 30px 0; /* Padding atas dan bawah 100px, kiri dan kanan default */
            background-color:rgb(92, 98, 104); /* Menambahkan warna latar belakang biru */
            color: white; /* Teks berwarna putih */
            text-align: center; /* Menjaga teks di tengah */
        }



        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            text-decoration: none;
            font-size: 18px;
        }

        .logout-btn:hover {
            color: #f1f1f1;
        }

    </style>
</head>
<body>

<header>
    <!-- Sidebar -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <!-- Logo and Title -->
                <div class="sidebar-header d-flex align-items-center mb-4">
                    <img src="ui.jpg" alt="Logo" class="sidebar-logo mr-2">
                    <h4>Invent</h4>
                </div>
                <br>
                <a href="index.php">Dashboard</a>
                <a href="data_barang.php">Data barang</a>
                <br><br><br><br><br>   
                <br><br><br><br><br><br><br><br><br>
            </div>
        </div>
    </div>

    <!-- Logout Button in Header -->
    <a href="logout.php" class="logout-btn" onclick="return confirm('Apakah Anda yakin ingin logout?');">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</header>
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 container mt-5">
            <h1 class="text-center">ATK RIK</h1>

            <?php
// Tampilkan pesan sukses jika ada
if (isset($_SESSION['success'])) {
    echo '<div class="notification-wrapper">
            <div class="notification-box alert alert-success alert-dismissible fade show" role="alert">
                ' . $_SESSION['success'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          </div>';
    unset($_SESSION['success']); // Hapus pesan setelah ditampilkan
}

// Tampilkan pesan error jika ada
if (isset($_SESSION['error'])) {
    echo '<div class="notification-wrapper">
            <div class="notification-box alert alert-danger alert-dismissible fade show" role="alert">
                ' . $_SESSION['error'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          </div>';
    unset($_SESSION['error']); // Hapus pesan setelah ditampilkan
}
?>

            <button type="button" class="btn mb-4" style="background-color: #343a40; border-color: #343a40; color: white;" data-toggle="modal" data-target="#tambahBarangModal">
    Tambah Barang
</button>


            <!-- Modal for Adding Barang -->
            <div class="modal fade" id="tambahBarangModal" tabindex="-1" aria-labelledby="tambahBarangLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="tambahBarangLabel">Tambah Barang</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" action="index.php">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="nama_barang">Nama Barang</label>
                                    <input type="text" class="form-control" name="nama_barang" id="nama_barang" placeholder="Nama Barang" required>
                                </div>
                                <div class="form-group">
                                    <label for="kode">Kode Barang</label>
                                    <input type="text" class="form-control" name="kode" id="kode" placeholder="Masukan Kode Barang" required>
                                </div>
                                <div class="form-group">
                                    <label for="tanggal">Tanggal</label>
                                    <input type="date" class="form-control" name="tanggal" id="tanggal" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>

            

<!-- Search form -->
<form method="GET" class="mb-3">
    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari Nama Barang atau Kode" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" style="width: 300px; display: inline;">
    <button type="submit" class="btn btn-primary btn-sm">Cari</button>
</form>
<?php
// Hitung total halaman
$sql_count = "SELECT COUNT(*) AS total FROM barang";
$result_count = $conn->query($sql_count);
$total_data = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

// Konfigurasi pagination
$range = 2; // Jumlah halaman di kiri/kanan halaman aktif
$previous_page = $page - 1;
$next_page = $page + 1;

// Menampilkan navigasi
echo '<nav aria-label="Page navigation">';
echo '<ul class="pagination justify-content-center">';

// Tombol Previous
if ($page > 1) {
    echo "<li class='page-item'><a class='page-link' href='?page=$previous_page'>Previous</a></li>";
} else {
    echo "<li class='page-item disabled'><span class='page-link'>Previous</span></li>";
}

// Menampilkan halaman dalam range
for ($i = 1; $i <= $total_pages; $i++) {
    if ($i == 1 || $i == $total_pages || ($i >= $page - $range && $i <= $page + $range)) {
        $active = ($i == $page) ? 'active' : '';
        echo "<li class='page-item $active'><a class='page-link' href='?page=$i'>$i</a></li>";
    } elseif ($i == 2 || $i == $total_pages - 1) {
        echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
    }
}

// Tombol Next
if ($page < $total_pages) {
    echo "<li class='page-item'><a class='page-link' href='?page=$next_page'>Next</a></li>";
} else {
    echo "<li class='page-item disabled'><span class='page-link'>Next</span></li>";
}

echo '</ul>';
echo '</nav>';
?>



<table class="table table-bordered">
    <thead class="thead-light">
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Total Masuk</th>
            <th>Total Keluar</th>
            <th>Stock Barang</th>
            <th>Aksi</th>
        </tr>
    </thead>

    <tbody>
    <?php
    // Tentukan berapa banyak data per halaman
    $limit = 20;
    
    // Tentukan halaman yang sedang aktif
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    // Hitung OFFSET berdasarkan halaman yang aktif
    $offset = ($page - 1) * $limit;
    
    // Get search keyword from the query string
    $searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Query untuk mendapatkan data dengan pagination
    if ($searchKeyword) {
        $searchKeyword = "%$searchKeyword%";
        $sql = "SELECT * FROM barang WHERE nama_barang LIKE ? OR kode LIKE ? ORDER BY id LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $searchKeyword, $searchKeyword, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT * FROM barang ORDER BY id LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    
    // Menampilkan data
    $no = $offset + 1;  // Mulai nomor urut sesuai dengan offset
while ($row = $result->fetch_assoc()) :
?>
    <tr>
        <td><?php echo $no++; ?></td> <!-- Nomor urut tetap berlanjut meski data dihapus -->
        <td><?php echo $row['tanggal']; ?></td>
        <td><?php echo $row['kode']; ?></td>
        <td><?php echo $row['nama_barang']; ?></td>
        <td><?php echo $row['masuk']; ?></td>
        <td>
            <a href="#" class="d-inline-block p-2 bg-light text-dark rounded text-decoration-none" data-bs-toggle="modal" data-bs-target="#modal-keluar-<?php echo $row['id']; ?>" title="Lihat History">
                <?php echo $row['keluar']; ?>
            </a>
        </td>
        <td><?php echo $row['jumlah']; ?></td>
        <td>
            <!-- Tindakan lain seperti tombol untuk menghapus, edit, dll. -->
       

     
    
    

            <form method="POST" style="display:inline;">
    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
    <input type="number" name="jumlah" placeholder="Jumlah" required class="form-control form-control-sm" style="width: 80px; display: inline;">
    <select id="aksi-select-<?php echo $row['id']; ?>" name="aksi" required class="form-control form-control-sm" style="width: 100px; display: inline;" onchange="handleAksiChange(<?php echo $row['id']; ?>);">
        <option value="masuk" selected>Masuk</option>
        <option value="keluar">Keluar</option>
    </select>
    <button type="submit" name="update" class="btn btn-warning btn-sm" onclick="return confirmUpdate();">Update</button>
    <a href="?hapus=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete(event, <?php echo $row['id']; ?>);">
    Hapus
</a>

</form>



<div class="modal fade" id="modal-keluar-<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="modalKeluarLabel-<?php echo $row['id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalKeluarLabel-<?php echo $row['id']; ?>">Detail Barang Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-hover table-bordered" id="dataKeluarTable">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Pengambil</th>
                            <th>Jumlah Keluar</th>
                            <th>Tanggal Pengambilan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Ambil data terkait barang keluar dari tabel barang_keluar
                    $id_barang = $row['id'];

                    // Query untuk mengambil data terbaru terlebih dahulu (urutkan berdasarkan tanggal_pengambilan DESC)
                    $query_keluar = "SELECT jumlah, nama_pengambil, tanggal_pengambilan FROM riwayat_keluar WHERE id_barang = $id_barang ORDER BY tanggal_pengambilan DESC";
                    $result_keluar = $conn->query($query_keluar);
                    if ($result_keluar->num_rows > 0) {
                        $data_array = [];
                        while ($data = $result_keluar->fetch_assoc()) {
                            $data_array[] = $data;
                        }
                    
                        // Variabel penghitung untuk nomor urut
                        $no = 1;
                    
                        // Tampilkan 5 data pertama
                        for ($i = 0; $i < count($data_array); $i++) {
                            if ($i < 5) {
                                echo "<tr>";
                                echo "<td>" . $no++ . "</td>"; // Nomor urut
                                echo "<td>" . $data_array[$i]['nama_pengambil'] . "</td>";
                                echo "<td>" . $data_array[$i]['jumlah'] . "</td>";
                                echo "<td>" . $data_array[$i]['tanggal_pengambilan'] . "</td>";
                                echo "</tr>";
                            } else {
                                // Tambahkan class 'hidden-row' untuk data lebih dari 5
                                echo "<tr class='hidden-row' style='display: none;'>";
                                echo "<td>" . $no++ . "</td>"; // Nomor urut
                                echo "<td>" . $data_array[$i]['nama_pengambil'] . "</td>";
                                echo "<td>" . $data_array[$i]['jumlah'] . "</td>";
                                echo "<td>" . $data_array[$i]['tanggal_pengambilan'] . "</td>";
                                echo "</tr>";
                            }
                        }
                    
                    

                        // Tampilkan tombol selengkapnya jika ada lebih dari 5 data
                        if (count($data_array) > 5) {
                            echo "<tr>";
                            echo "<td colspan='3' class='text-center'><button id='show-more' class='btn btn-info' onclick='showMore()'>Selengkapnya</button></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' class='text-center'>Belum ada data keluar</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal Input Keluar -->
<div class="modal fade" id="modalKeluar-<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="modalKeluarLabel-<?php echo $row['id']; ?>" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalKeluarLabel-<?php echo $row['id']; ?>">Input Transaksi Keluar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <div class="mb-3">
                        <label for="namaPengambil-<?php echo $row['id']; ?>" class="form-label">Nama Pengambil</label>
                        <input type="text" name="nama_pengambil" id="namaPengambil-<?php echo $row['id']; ?>" placeholder="Nama Pengambil" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="jumlahKeluar-<?php echo $row['id']; ?>" class="form-label">Jumlah Keluar</label>
                        <input type="number" name="jumlah" id="jumlahKeluar-<?php echo $row['id']; ?>" placeholder="Jumlah Keluar" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="tanggalPengambilan-<?php echo $row['id']; ?>" class="form-label">Tanggal Pengambilan</label>
                        <input type="date" name="tanggal_pengambilan" id="tanggalPengambilan-<?php echo $row['id']; ?>" required class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>


            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php
// Hitung total halaman
$sql_count = "SELECT COUNT(*) AS total FROM barang";
$result_count = $conn->query($sql_count);
$total_data = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_data / $limit);

// Konfigurasi pagination
$range = 2; // Jumlah halaman di kiri/kanan halaman aktif
$previous_page = $page - 1;
$next_page = $page + 1;

// Menampilkan navigasi
echo '<nav aria-label="Page navigation">';
echo '<ul class="pagination justify-content-center">';

// Tombol Previous
if ($page > 1) {
    echo "<li class='page-item'><a class='page-link' href='?page=$previous_page'>Previous</a></li>";
} else {
    echo "<li class='page-item disabled'><span class='page-link'>Previous</span></li>";
}

// Menampilkan halaman dalam range
for ($i = 1; $i <= $total_pages; $i++) {
    if ($i == 1 || $i == $total_pages || ($i >= $page - $range && $i <= $page + $range)) {
        $active = ($i == $page) ? 'active' : '';
        echo "<li class='page-item $active'><a class='page-link' href='?page=$i'>$i</a></li>";
    } elseif ($i == 2 || $i == $total_pages - 1) {
        echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
    }
}

// Tombol Next
if ($page < $total_pages) {
    echo "<li class='page-item'><a class='page-link' href='?page=$next_page'>Next</a></li>";
} else {
    echo "<li class='page-item disabled'><span class='page-link'>Next</span></li>";
}

echo '</ul>';
echo '</nav>';
?>





<br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <div class="mb-4">
    <button onclick="generateExcel()" class="btn btn-danger">Data Barang to Excel</button>
    <button onclick="DataKeluarExcel()" class="btn btn-danger">Data Keluar To Excel</button>
</div>
</div>


    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>

   window.onload = function(){

    const now = new Date();

    const year = now.getFullYear();
    const month = now.getMonth() + 1;
    const day = now.getDate();
    const hours = now.getHours();
    const minutes = now.getMinutes();

    const formattedDate = '${year}-${month}-${day}T${hours}:${minutes}';

    document.getElmentById('tanggal').value = formattedDate;
   }



   function handleAksiChange(id) {
        const aksiSelect = document.getElementById('aksi-select-' + id);
        if (aksiSelect.value === 'keluar') {
            // Tampilkan modal dengan ID unik
            const modal = new bootstrap.Modal(document.getElementById('modalKeluar-' + id));
            modal.show();

            // Reset dropdown kembali ke "masuk"
            aksiSelect.value = 'masuk';
        }
    }


    function confirmDelete(event, id) {
    event.preventDefault(); // Mencegah link langsung dijalankan

    Swal.fire({
        title: "Konfirmasi Hapus",
        text: "Apakah Anda yakin ingin menghapus data ini?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Ya, Hapus",
        cancelButtonText: "Batal"
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "?hapus=" + id; // Redirect ke URL hapus jika dikonfirmasi
        }
    });

    return false;
}

// Inisialisasi tooltip
var tooltipTriggerList = Array.from(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})


    // Fungsi untuk mengenerate PDF
   // Fungsi untuk mengenerate PDF
   function generateExcel() {
    // Ambil data dari server menggunakan fetch
    fetch("get_all_barang.php")
        .then(response => response.json()) // Konversi respons ke JSON
        .then(data => {
            // Inisialisasi array untuk data Excel
            const rows = [];

            // Header kolom untuk file Excel
            rows.push(["No", "Tanggal", "Kode Barang", "Nama Barang", "Total Masuk", "Total Keluar", "Stock Barang"]);

            // Isi data dari API
            let no = 1;
            data.forEach(row => {
                rows.push([
                    no,
                    row.tanggal,
                    row.kode_barang,
                    row.nama_barang,
                    row.total_masuk,
                    row.total_keluar,
                    row.stock
                ]);
                no++;
            });

            // Gunakan SheetJS untuk membuat file Excel
            const worksheet = XLSX.utils.aoa_to_sheet(rows);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Data Barang");

            // Simpan file Excel
            XLSX.writeFile(workbook, "Laporan_Barang.xlsx");
        })
        .catch(error => {
            console.error("Error fetching data barang:", error);
        });
}





function showNotification(message, type) {
    var notification = document.getElementById('notification');
    notification.classList.remove('alert-success', 'alert-danger');
    notification.classList.add(type);  // Tipe bisa 'alert-success' atau 'alert-danger'
    notification.innerHTML = message;
    notification.style.display = 'block'; // Menampilkan notifikasi

    // Menghilangkan notifikasi setelah 5 detik
    setTimeout(function() {
      notification.style.display = 'none';
    }, 5000); // 5000ms = 5 detik
  }

  // Cek ketika form di-submit
  document.querySelector('form').addEventListener('submitt', function(event) {
    event.preventDefault();  // Mencegah form dikirimkan secara langsung

    // Simulasi AJAX atau proses data (bisa menggunakan PHP untuk memprosesnya)
    var form = this;
    var action = form.querySelector('[name="aksi"]').value;
    var jumlah = form.querySelector('[name="jumlah"]').value;

    // Misalnya jika aksi adalah "masuk"
    if (action === 'masuk') {
      // Simulasikan pengiriman data berhasil (misalnya ke database)
      setTimeout(function() {
        showNotification('Berhasil menambahkan barang masuk!', 'alert-success');
        form.reset(); // Reset form setelah submit
      }, 1000); // Simulasi proses 1 detik
    } else {
      // Simulasikan aksi "keluar"
      setTimeout(function() {
        showNotification('Berhasil Mengurangkan Stock Barang!', 'alert-success');
        form.reset(); // Reset form setelah submit
      }, 1000); // Simulasi proses 1 detik
    }
  });


  function showMore() {
    // Tampilkan semua baris dengan class 'hidden-row'
    const hiddenRows = document.querySelectorAll('.hidden-row');
    hiddenRows.forEach(row => row.style.display = '');
    // Sembunyikan tombol "Selengkapnya"
    document.getElementById('show-more').style.display = 'none';
}



function DataKeluarExcel() {
    // Ambil data dari server menggunakan fetch
    fetch("get_all_data_keluar.php")
        .then(response => response.json()) // Konversi respons ke JSON
        .then(data => {
            // Inisialisasi array untuk data Excel
            const rows = [];
            
            // Header kolom untuk file Excel
            rows.push(["No", "Nama Pengambil", "Nama Barang", "Jumlah Keluar", "Tanggal Pengambilan"]);

            // Isi data dari API
            let no = 1;
            data.forEach(row => {
                rows.push([
                    no, 
                    row.nama_pengambil, 
                    row.nama_barang, 
                    row.jumlah, 
                    row.tanggal_pengambilan
                ]);
                no++;
            });

            // Gunakan SheetJS untuk membuat file Excel
            const worksheet = XLSX.utils.aoa_to_sheet(rows);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, "Data Keluar");

            // Simpan file Excel
            XLSX.writeFile(workbook, "Laporan_Barang_Keluar.xlsx");
        })
        .catch(error => {
            console.error("Error fetching data pengambilan:", error);
        });
}



</script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>



</body>
</html>
