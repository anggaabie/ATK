<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traffic barang</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            margin: 0;
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
            transition: all 0.3s ease-in-out; /* Smooth transition for width change */
        }

        /* Sidebar Logo */
        .sidebar-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .sidebar-logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
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

        /* Main Content */
        .main-content {
            margin-left: 270px;
            padding: 20px;
            flex-grow: 1;
            transition: margin-left 0.3s ease; /* Smooth transition for main content resizing */
        }

        .container-fluid {
            display: flex;
            padding-top: 20px;
        }

        .container {
            width: 100%;
            margin-left: 270px;
            padding-top: 20px;
        }

        /* Canvas (Chart) Adjustment */
        canvas {
            width: 100%; /* Ensure chart takes full width */
            height: 400px; /* You can adjust height as needed */
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

            .main-content {
                margin-left: 0;
            }
        }

        @media (max-width: 1200px) {
            .sidebar {
                width: 200px; /* Decrease sidebar width on larger screens */
            }

            .main-content {
                margin-left: 220px;
            }
        }

        /* Canvas (Chart) Adjustment */


.container {
            margin-top: 20px;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card-header {
            font-size: 18px;
            font-weight: bold;
            color: #343a40;
            margin-bottom: 15px;
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 10px;
        }

        canvas {
            width: 30%;
            height: 300px;
        }

        .updated-time {
            font-size: 12px;
            color: #6c757d;
            text-align: right;
            margin-top: 10px;
        }

        header {
            width: 100%; /* Lebar penuh */
            padding: 20px 0; /* Padding atas dan bawah 100px, kiri dan kanan default */
            background-color:rgb(92, 98, 104); /* Menambahkan warna latar belakang biru */
            color: white; /* Teks berwarna putih */
            text-align: center; /* Menjaga teks di tengah */
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
    
 
            </div>
        </div>
    </div>
</header>
        <!-- Main Content (Traffic Chart) -->
        <div class="mai-content">
        <div class="container">
            <div class="card">
                <div class="card-header">
                    Traffic barang /bulan
                </div>
                <canvas id="chartKeluar"></canvas>
                <div class="updated-time">
                    Updated dynamically from database
                </div>
            </div>
        </div>
    </div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Ambil data dari server
    fetch('get_keluar_data.php') // Ganti dengan path file PHP Anda
    .then(response => response.json())
    .then(data => {
        const labels = data.map(item => `Bulan ${item.bulan}-${item.tahun}`);
        const values = data.map(item => item.keluar);

        // Membuat grafik
        const ctx = document.getElementById('chartKeluar').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Traffic Barang Keluar',
                    data: values,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Warna biru transparan untuk area bawah garis
                    borderColor: 'rgba(75, 192, 192, 1)', // Warna garis biru
                    borderWidth: 3, // Membuat garis lebih tebal
                    pointBackgroundColor: 'rgba(75, 192, 192, 1)', // Warna titik data
                    pointBorderColor: '#fff', // Warna border titik
                    tension: 0.4, // Membuat garis lebih halus
                    fill: true // Mengisi area di bawah garis
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    })
    .catch(error => console.error('Error:', error));

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