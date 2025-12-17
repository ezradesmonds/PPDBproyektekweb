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
    // A simple page for user without data
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Poppins', sans-serif;
        }
        .proof-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .proof-header {
            background-color: #0d6efd;
            color: white;
            text-align: center;
            padding: 25px;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .proof-header h3, .proof-header h4 {
            margin: 0;
        }
        .proof-header h3 {
            font-weight: 700;
        }
        .proof-body {
            padding: 40px;
        }
        .student-photo {
            width: 140px;
            height: 180px;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        .table-data th {
            width: 35%;
            font-weight: 600;
            background-color: #f8f9fa;
        }
        .section-title {
            text-align: center;
            font-weight: 600;
            color: #fff;
            background-color: #6c757d;
            padding: 8px;
            margin-top: 20px;
            margin-bottom: 0;
            border-radius: 5px 5px 0 0;
        }
        .status-badge {
            font-size: 1.5rem;
            font-weight: 700;
            padding: 0.8rem 1rem;
        }
        .footer-note {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px dashed #ced4da;
        }
        @media print {
            .no-print { display: none !important; }
            body { background-color: white; }
            .proof-container {
                box-shadow: none;
                border: none;
                margin: 0;
                max-width: 100%;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

<div class="proof-container">
    <div class="proof-header">
        <h3 class="mb-1">BUKTI PENDAFTARAN</h3>
        <h4>PPDB SEKOLAH IMPIAN TAHUN AJARAN 2026/2027</h4>
    </div>

    <div class="proof-body">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h5 class="mb-1">Nomor Pendaftaran</h5>
                <h2 class="text-primary fw-bold"><?php echo htmlspecialchars($pendaftaran['nisn']); ?></h2>
                <h5 class="mt-3 mb-1">Status Pendaftaran</h5>
                 <?php
                    $status_map = [
                        'menunggu' => ['class' => 'warning', 'text' => 'Menunggu Verifikasi'],
                        'lolos' => ['class' => 'info', 'text' => 'Lolos Seleksi Administrasi'],
                        'diterima' => ['class' => 'success', 'text' => 'Diterima'],
                        'ditolak' => ['class' => 'danger', 'text' => 'Ditolak']
                    ];
                    $status_info = $status_map[$pendaftaran['status']];
                 ?>
                <span class="badge status-badge text-dark-emphasis bg-<?php echo $status_info['class']; ?>-subtle border border-<?php echo $status_info['class']; ?>-subtle">
                    <?php echo $status_info['text']; ?>
                </span>
            </div>
            <div class="col-md-4 text-center text-md-end">
                <?php if(!empty($pendaftaran['foto'])): ?>
                    <img src="../assets/uploads/<?php echo htmlspecialchars($pendaftaran['foto']); ?>" class="student-photo" alt="Foto Siswa">
                <?php else: ?>
                    <img src="https://placehold.co/140x180/ced4da/6c757d?text=Foto+Siswa" class="student-photo" alt="Foto Siswa">
                <?php endif; ?>
            </div>
        </div>

        <h6 class="section-title">DATA CALON SISWA</h6>
        <table class="table table-bordered table-data">
            <tbody>
                <tr><th>Nama Lengkap</th><td><?php echo htmlspecialchars($pendaftaran['nama_lengkap']); ?></td></tr>
                <tr><th>Tempat, Tanggal Lahir</th><td><?php echo htmlspecialchars($pendaftaran['tempat_lahir']) . ', ' . date('d F Y', strtotime($pendaftaran['tanggal_lahir'])); ?></td></tr>
                <tr><th>Jenis Kelamin</th><td><?php echo $pendaftaran['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></td></tr>
                <tr><th>Agama</th><td><?php echo htmlspecialchars($pendaftaran['agama']); ?></td></tr>
                <tr><th>Alamat</th><td><?php echo htmlspecialchars($pendaftaran['alamat']); ?></td></tr>
                <tr><th>Asal Sekolah</th><td><?php echo htmlspecialchars($pendaftaran['sekolah_asal']); ?></td></tr>
                <tr><th>Jurusan Pilihan</th><td class="fw-bold"><?php echo htmlspecialchars($pendaftaran['jurusan_pilihan']); ?></td></tr>
            </tbody>
        </table>

        <h6 class="section-title">DATA ORANG TUA / WALI</h6>
        <table class="table table-bordered table-data">
            <tbody>
                 <tr><th>Nama Ayah</th><td><?php echo htmlspecialchars($pendaftaran['nama_ayah']); ?></td></tr>
                 <tr><th>Pekerjaan Ayah</th><td><?php echo htmlspecialchars($pendaftaran['pekerjaan_ayah']); ?></td></tr>
                 <tr><th>Nama Ibu</th><td><?php echo htmlspecialchars($pendaftaran['nama_ibu']); ?></td></tr>
                 <tr><th>Pekerjaan Ibu</th><td><?php echo htmlspecialchars($pendaftaran['pekerjaan_ibu']); ?></td></tr>
            </tbody>
        </table>

        <div class="mt-4 footer-note">
            <p class="fw-bold mb-2">Perhatian:</p>
            <ul class="small ps-3 mb-0">
                <li>Ini adalah bukti pendaftaran yang sah yang diterbitkan oleh sistem PPDB Sekolah Impian.</li>
                <li>Harap simpan bukti ini dengan baik untuk keperluan verifikasi dan daftar ulang.</li>
                <li>Perubahan data setelah finalisasi hanya dapat dilakukan dengan menghubungi panitia PPDB.</li>
                <li>Selalu pantau status pendaftaran dan pengumuman terbaru melalui dashboard siswa Anda.</li>
            </ul>
        </div>
        
        <div class="mt-4 text-end">
            <p class="mb-0 small">Dicetak oleh sistem pada: <?php echo date('d F Y, H:i:s'); ?></p>
        </div>
    </div>
</div>

<div class="container text-center py-4 no-print">
    <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-2"></i> Cetak Halaman Ini</button>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard</a>
</div>

</body>
</html>
