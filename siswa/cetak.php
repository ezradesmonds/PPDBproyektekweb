<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

$user_id = $_SESSION['user_id'];

// Fetch registration data along with user's NISN
$sql = "SELECT p.*, u.nisn FROM pendaftaran p JOIN users u ON p.user_id = u.id WHERE p.user_id = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pendaftaran = $result->fetch_assoc();

if (!$pendaftaran) {
    echo "<!DOCTYPE html><html lang='id'><head><title>Error</title><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'></head><body class='bg-light'><div class='container text-center mt-5'><div class='alert alert-danger'>Data pendaftaran tidak ditemukan. Silakan isi formulir terlebih dahulu.</div><a href='formulir.php' class='btn btn-primary'>Isi Formulir</a> <a href='index.php' class='btn btn-secondary'>Kembali ke Dashboard</a></div></body></html>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pendaftaran - <?php echo htmlspecialchars($pendaftaran['nama_lengkap']); ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0f3460;
            --accent-color: #fca311;
            --light-bg: #f4f7fc;
            --text-dark: #1a1a2e;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            -webkit-print-color-adjust: exact;
        }

        h1, h2, h3, h4, h5, .brand-font {
            font-family: 'Poppins', sans-serif;
        }

        .proof-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }

        .proof-header {
            background: linear-gradient(135deg, var(--primary-color), #16213e);
            color: white;
            text-align: center;
            padding: 35px 20px;
            position: relative;
        }

        .proof-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80%;
            height: 5px;
            background-color: var(--accent-color);
            border-radius: 5px;
        }

        .proof-header h3 {
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 5px;
            color: var(--accent-color);
        }

        .proof-header h4 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.1rem;
            font-weight: 400;
            opacity: 0.9;
        }

        .proof-body {
            padding: 50px 50px 30px 50px;
        }

        .student-photo {
            width: 140px;
            height: 180px;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            border-radius: 10px;
        }

        .status-badge {
            font-size: 1rem;
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .bg-menunggu { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .bg-lolos { background-color: #cff4fc; color: #055160; border: 1px solid #b6effb; }
        .bg-diterima { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .bg-ditolak { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }

        .section-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: var(--primary-color);
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 8px;
            margin-top: 30px;
            margin-bottom: 20px;
            text-transform: uppercase;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }
        
        .section-title i { margin-right: 10px; }

        .table-data {
            width: 100%;
            margin-bottom: 1rem;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-data th, .table-data td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }

        .table-data th {
            width: 35%;
            font-weight: 600;
            color: #555;
            background-color: #fdfdfd;
            text-align: left;
        }

        .table-data tr:last-child th, .table-data tr:last-child td {
            border-bottom: none;
        }

        .footer-note {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 5px solid var(--primary-color);
            font-size: 0.9rem;
            color: #555;
        }

        /* Tombol Cetak */
        .btn-print {
            background-color: var(--primary-color);
            color: var(--accent-color);
            border: none;
            border-radius: 50px;
            padding: 10px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-print:hover {
            background-color: #16213e;
            color: white;
            transform: translateY(-2px);
        }

        @media print {
            .no-print { display: none !important; }
            body { background-color: white; }
            .proof-container {
                box-shadow: none;
                border: 2px solid #000;
                margin: 0;
                width: 100%;
                max-width: 100%;
                border-radius: 0;
            }
<<<<<<< HEAD
            .proof-header {
                background: white !important;
                color: black !important;
                border-bottom: 2px solid black;
                padding-bottom: 10px;
            }
            .proof-header::after { display: none; }
            .proof-header h3 { color: black !important; }
            .status-badge { border: 1px solid black !important; color: black !important; background: white !important; }
            .section-title { color: black !important; border-bottom: 1px solid black !important; }
            .table-data th { background-color: #f0f0f0 !important; font-weight: bold; }
            .footer-note { border: 1px solid #000; border-left: 5px solid #000; }
=======
>>>>>>> 039b87f87e5da6dda804e9ef72378a3555c80362
        }
    </style>
</head>
<body>

<div class="proof-container">
    <div class="proof-header">
        <i class="fas fa-graduation-cap fa-3x mb-3 text-warning"></i>
        <h3>BUKTI PENDAFTARAN</h3>
        <h4>SMA Kristen Petra 2 TAHUN AJARAN 2026/2027</h4>
    </div>

    <div class="proof-body">
        <div class="row align-items-center mb-5">
            <div class="col-9">
                <p class="text-muted mb-1 text-uppercase small ls-1">Nomor Induk Siswa Nasional (NISN)</p>
                <h1 class="display-5 fw-bold" style="color: var(--primary-color);"><?php echo htmlspecialchars($pendaftaran['nisn']); ?></h1>
                
                <div class="mt-4">
                    <p class="text-muted mb-2 small text-uppercase">Status Pendaftaran</p>
                    <?php
                        $status_map = [
                            'menunggu' => ['class' => 'bg-menunggu', 'text' => 'Menunggu Verifikasi'],
                            'lolos' => ['class' => 'bg-lolos', 'text' => 'Lolos Seleksi Administrasi'],
                            'diterima' => ['class' => 'bg-diterima', 'text' => 'Diterima'],
                            'ditolak' => ['class' => 'bg-ditolak', 'text' => 'Ditolak']
                        ];
                        $status_info = $status_map[$pendaftaran['status']];
                    ?>
                    <span class="status-badge <?php echo $status_info['class']; ?>">
                        <i class="fas fa-info-circle me-2"></i><?php echo $status_info['text']; ?>
                    </span>
                </div>
            </div>
            <div class="col-3 text-end">
                <?php if(!empty($pendaftaran['foto'])): ?>
                    <img src="../assets/uploads/<?php echo htmlspecialchars($pendaftaran['foto']); ?>" class="student-photo" alt="Foto Siswa">
                <?php else: ?>
                    <img src="https://placehold.co/140x180/ced4da/6c757d?text=No+Photo" class="student-photo" alt="Foto Siswa">
                <?php endif; ?>
            </div>
        </div>

        <h6 class="section-title"><i class="fas fa-user-circle"></i> DATA CALON SISWA</h6>
        <table class="table-data">
            <tbody>
                <tr><th>Nama Lengkap</th><td><strong class="text-dark"><?php echo htmlspecialchars($pendaftaran['nama_lengkap']); ?></strong></td></tr>
                <tr><th>Tempat, Tanggal Lahir</th><td><?php echo htmlspecialchars($pendaftaran['tempat_lahir']) . ', ' . date('d F Y', strtotime($pendaftaran['tanggal_lahir'])); ?></td></tr>
                <tr><th>Jenis Kelamin</th><td><?php echo $pendaftaran['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></td></tr>
                <tr><th>Agama</th><td><?php echo htmlspecialchars($pendaftaran['agama']); ?></td></tr>
                <tr><th>Alamat Lengkap</th><td><?php echo htmlspecialchars($pendaftaran['alamat']); ?></td></tr>
                <tr><th>Asal Sekolah</th><td><?php echo htmlspecialchars($pendaftaran['sekolah_asal']); ?></td></tr>
                <tr><th>Jurusan Pilihan</th><td><span class="badge bg-primary rounded-pill"><?php echo htmlspecialchars($pendaftaran['jurusan_pilihan']); ?></span></td></tr>
            </tbody>
        </table>

        <h6 class="section-title"><i class="fas fa-users"></i> DATA ORANG TUA / WALI</h6>
        <table class="table-data">
            <tbody>
                 <tr><th>Nama Ayah</th><td><?php echo htmlspecialchars($pendaftaran['nama_ayah']); ?></td></tr>
                 <tr><th>Pekerjaan Ayah</th><td><?php echo htmlspecialchars($pendaftaran['pekerjaan_ayah']); ?></td></tr>
                 <tr><th>Nama Ibu</th><td><?php echo htmlspecialchars($pendaftaran['nama_ibu']); ?></td></tr>
                 <tr><th>Pekerjaan Ibu</th><td><?php echo htmlspecialchars($pendaftaran['pekerjaan_ibu']); ?></td></tr>
            </tbody>
        </table>

        <div class="mt-5 footer-note">
            <p class="fw-bold mb-2 text-dark"><i class="fas fa-exclamation-triangle text-warning me-2"></i> Perhatian:</p>
            <ul class="mb-0 ps-3">
                <li>Dokumen ini adalah bukti pendaftaran sah yang diterbitkan sistem SMA Kristen Petra 2.</li>
                <li>Simpan dokumen ini untuk keperluan verifikasi ulang dan daftar ulang.</li>
                <li>Data yang sudah difinalisasi tidak dapat diubah tanpa persetujuan panitia.</li>
            </ul>
        </div>
        
        <div class="mt-4 text-center">
            <p class="text-muted small fst-italic">Dicetak otomatis oleh sistem pada: <?php echo date('d F Y, H:i:s'); ?> WIB</p>
        </div>
    </div>
</div>

<div class="container text-center py-4 no-print mb-5">
    <button onclick="window.print()" class="btn btn-print shadow-sm me-2"><i class="fas fa-print me-2"></i> Cetak Dokumen</button>
    <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard</a>
</div>

</body>
</html>