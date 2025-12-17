<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

$user_id = $_SESSION['user_id'];
$nama_siswa = $_SESSION['nama'];

// Check if student has filled the form
$sql = "SELECT * FROM pendaftaran WHERE user_id = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pendaftaran = $result->fetch_assoc();

$status_pendaftaran = $pendaftaran ? $pendaftaran['status'] : 'belum_mendaftar';
$status_map = [
    'menunggu' => ['class' => 'warning', 'text' => 'Menunggu Verifikasi'],
    'lolos' => ['class' => 'info', 'text' => 'Lolos Seleksi Administrasi'],
    'diterima' => ['class' => 'success', 'text' => 'Diterima'],
    'ditolak' => ['class' => 'danger', 'text' => 'Ditolak'],
    'belum_mendaftar' => ['class' => 'secondary', 'text' => 'Belum Mendaftar'],
];
$status_info = $status_map[$status_pendaftaran];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - PPDB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-user-graduate"></i> Dashboard Siswa
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            Selamat datang, <?php echo htmlspecialchars($nama_siswa); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-danger" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card text-center">
                    <div class="card-header">
                        Status Pendaftaran Anda
                    </div>
                    <div class="card-body">
                        <h1 class="card-title text-<?php echo $status_info['class']; ?>">
                            <?php echo $status_info['text']; ?>
                        </h1>
                        <p class="card-text">
                            <?php if ($status_pendaftaran === 'belum_mendaftar'): ?>
                                Anda belum mengisi formulir pendaftaran. Silakan isi formulir untuk melanjutkan.
                            <?php elseif ($status_pendaftaran === 'menunggu'): ?>
                                Data Anda sedang dalam proses verifikasi oleh panitia. Mohon ditunggu.
                            <?php elseif ($status_pendaftaran === 'lolos'): ?>
                                Selamat! Anda lolos tahap seleksi administrasi. Informasi selanjutnya akan diumumkan.
                            <?php elseif ($status_pendaftaran === 'diterima'): ?>
                                Selamat! Anda diterima sebagai siswa baru di Sekolah Impian.
                            <?php elseif ($status_pendaftaran === 'ditolak'): ?>
                                Mohon maaf, pendaftaran Anda ditolak. Tetap semangat!
                            <?php endif; ?>
                        </p>
                        
                        <?php if ($status_pendaftaran === 'belum_mendaftar'): ?>
                            <a href="formulir.php" class="btn btn-primary"><i class="fas fa-file-alt"></i> Isi Formulir Pendaftaran</a>
                        <?php elseif ($status_pendaftaran === 'menunggu'): ?>
                             <a href="formulir.php" class="btn btn-warning"><i class="fas fa-edit"></i> Edit Formulir</a>
                             <a href="cetak.php" class="btn btn-info" target="_blank"><i class="fas fa-print"></i> Cetak Bukti Pendaftaran</a>
                        <?php else: ?>
                            <a href="cetak.php" class="btn btn-info" target="_blank"><i class="fas fa-print"></i> Cetak Bukti Pendaftaran</a>
                        <?php endif; ?>

                    </div>
                    <div class="card-footer text-muted">
                        PPDB Sekolah Impian <?php echo date('Y'); ?>
                    </div>
                </div>

                <?php if($pendaftaran): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Detail Data Anda</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th>Nama Lengkap</th>
                                            <td><?php echo htmlspecialchars($pendaftaran['nama_lengkap']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tempat, Tanggal Lahir</th>
                                            <td><?php echo htmlspecialchars($pendaftaran['tempat_lahir']) . ', ' . date('d F Y', strtotime($pendaftaran['tanggal_lahir'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Jenis Kelamin</th>
                                            <td><?php echo $pendaftaran['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Agama</th>
                                            <td><?php echo htmlspecialchars($pendaftaran['agama']); ?></td>
                                        </tr>
                                         <tr>
                                            <th>Asal Sekolah</th>
                                            <td><?php echo htmlspecialchars($pendaftaran['sekolah_asal']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Jurusan Pilihan</th>
                                            <td><?php echo htmlspecialchars($pendaftaran['jurusan_pilihan']); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                             <div class="col-md-4 text-center">
                                <p><strong>Foto Siswa</strong></p>
                                <?php if(!empty($pendaftaran['foto'])): ?>
                                    <img src="../assets/uploads/<?php echo htmlspecialchars($pendaftaran['foto']); ?>" class="img-fluid rounded" alt="Foto Siswa">
                                <?php else: ?>
                                    <img src="https://placehold.co/150x200/ced4da/6c757d?text=Foto" class="img-fluid rounded" alt="Foto Siswa">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
