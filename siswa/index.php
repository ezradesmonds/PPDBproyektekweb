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

// Define status hierarchy and icons
$status_levels = [
    'belum_mendaftar' => ['text' => 'Belum Mendaftar', 'icon' => 'fa-file-alt', 'level' => 0],
    'menunggu' => ['text' => 'Menunggu Verifikasi', 'icon' => 'fa-clock', 'level' => 1],
    'lolos' => ['text' => 'Lolos Administrasi', 'icon' => 'fa-check', 'level' => 2],
    'diterima' => ['text' => 'Diterima', 'icon' => 'fa-user-graduate', 'level' => 3],
    'ditolak' => ['text' => 'Ditolak', 'icon' => 'fa-times-circle', 'level' => -1],
];

$current_level = $status_levels[$status_pendaftaran]['level'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - PPDB Sekolah Impian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-user-graduate me-2"></i>
                <strong>PPDB</strong> Sekolah Impian
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            Selamat datang, <strong><?php echo htmlspecialchars($nama_siswa); ?></strong>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-danger btn-sm" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Status Card -->
                <div class="card status-card text-center mb-4">
                     <div class="card-header bg-primary text-white">
                        Status Pendaftaran Anda
                    </div>
                    <div class="card-body">
                        <h1 class="card-title text-primary">
                             <i class="fas <?php echo $status_levels[$status_pendaftaran]['icon']; ?> me-2"></i>
                            <?php echo $status_levels[$status_pendaftaran]['text']; ?>
                        </h1>
                        <p class="card-text text-muted">
                            <?php if ($status_pendaftaran === 'belum_mendaftar'): ?>
                                Anda belum mengisi formulir pendaftaran. Silakan isi formulir untuk melanjutkan.
                            <?php elseif ($status_pendaftaran === 'menunggu'): ?>
                                Data Anda sedang dalam proses verifikasi oleh panitia. Mohon ditunggu.
                            <?php elseif ($status_pendaftaran === 'lolos'): ?>
                                Selamat! Anda lolos tahap seleksi administrasi. Informasi selanjutnya akan diumumkan.
                            <?php elseif ($status_pendaftaran === 'diterima'): ?>
                                Selamat! Anda diterima sebagai siswa baru di Sekolah Impian.
                            <?php elseif ($status_pendaftaran === 'ditolak'): ?>
                                Mohon maaf, pendaftaran Anda ditolak. Tetap semangat dan jangan berkecil hati.
                            <?php endif; ?>
                        </p>

                        <?php if ($status_pendaftaran === 'belum_mendaftar'): ?>
                            <a href="formulir.php" class="btn btn-primary"><i class="fas fa-file-alt me-2"></i> Isi Formulir Pendaftaran</a>
                        <?php elseif ($pendaftaran): ?>
                             <?php if ($status_pendaftaran === 'menunggu'): ?>
                                <a href="formulir.php" class="btn btn-warning"><i class="fas fa-edit me-2"></i> Edit Formulir</a>
                             <?php endif; ?>
                            <a href="cetak.php" class="btn btn-info" target="_blank"><i class="fas fa-print me-2"></i> Cetak Bukti Pendaftaran</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Student Data -->
                <?php if($pendaftaran): ?>
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Detail Data Anda</h5>
                         <a href="formulir.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                    </div>
                    <div class="card-body">
                         <div class="row align-items-center">
                            <div class="col-md-4 text-center">
                                <?php if(!empty($pendaftaran['foto'])): ?>
                                    <img src="../assets/uploads/<?php echo htmlspecialchars($pendaftaran['foto']); ?>" class="img-fluid rounded-circle mb-3" alt="Foto Siswa" style="width: 150px; height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="https://placehold.co/150x150/ced4da/6c757d?text=Foto" class="img-fluid rounded-circle mb-3" alt="Foto Siswa">
                                <?php endif; ?>
                                <h5 class="mb-1"><?php echo htmlspecialchars($pendaftaran['nama_lengkap']); ?></h5>
                                <p class="text-muted small"><?php echo htmlspecialchars($pendaftaran['jurusan_pilihan']); ?></p>
                            </div>
                            <div class="col-md-8">
                                <table class="table table-borderless table-sm">
                                    <tbody>
                                        <tr>
                                            <th width="30%">NISN</th>
                                            <td>: <?php echo htmlspecialchars($pendaftaran['nisn']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Asal Sekolah</th>
                                            <td>: <?php echo htmlspecialchars($pendaftaran['sekolah_asal']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tempat, Tgl Lahir</th>
                                            <td>: <?php echo htmlspecialchars($pendaftaran['tempat_lahir']) . ', ' . date('d F Y', strtotime($pendaftaran['tanggal_lahir'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Jenis Kelamin</th>
                                            <td>: <?php echo $pendaftaran['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Agama</th>
                                            <td>: <?php echo htmlspecialchars($pendaftaran['agama']); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                 <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Alur Pendaftaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item <?php echo $current_level >= 0 ? 'active' : ''; ?>">
                                <div class="icon"><i class="fas fa-file-alt"></i></div>
                                <div class="content">
                                    <strong>Isi Formulir</strong>
                                    <p class="small text-muted mb-0">Lengkapi data diri Anda.</p>
                                </div>
                            </div>
                            <div class="timeline-item <?php echo $current_level >= 1 ? 'active' : ''; ?>">
                                <div class="icon"><i class="fas fa-clock"></i></div>
                                <div class="content">
                                    <strong>Verifikasi Panitia</strong>
                                    <p class="small text-muted mb-0">Data Anda akan dicek oleh panitia.</p>
                                </div>
                            </div>
                             <div class="timeline-item <?php echo $current_level >= 2 ? 'active' : ''; ?>">
                                <div class="icon"><i class="fas fa-check"></i></div>
                                <div class="content">
                                    <strong>Seleksi Administrasi</strong>
                                    <p class="small text-muted mb-0">Hasil seleksi administrasi.</p>
                                </div>
                            </div>
                            <div class="timeline-item <?php echo $current_level >= 3 ? 'success' : ''; ?>">
                                <div class="icon"><i class="fas fa-user-graduate"></i></div>
                                <div class="content">
                                    <strong>Pengumuman Final</strong>
                                    <p class="small text-muted mb-0">Hasil akhir penerimaan siswa baru.</p>
                                </div>
                            </div>
                             <?php if($status_pendaftaran == 'ditolak'): ?>
                             <div class="timeline-item active">
                                <div class="icon bg-danger text-white"><i class="fas fa-times-circle"></i></div>
                                <div class="content">
                                    <strong>Ditolak</strong>
                                    <p class="small text-muted mb-0">Maaf, pendaftaran Anda ditolak.</p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="bg-white text-center py-3 mt-5 border-top">
        <div class="container">
            <p class="mb-0 text-muted small">Copyright &copy; <?php echo date('Y'); ?> PPDB Sekolah Impian. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
