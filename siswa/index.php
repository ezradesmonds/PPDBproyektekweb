<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit();
}
include '../koneksi.php';

$user_id = $_SESSION['user_id'];
$nama_siswa = $_SESSION['nama'];

// Fetch data
$sql = "SELECT * FROM pendaftaran WHERE user_id = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pendaftaran = $stmt->get_result()->fetch_assoc();

$status_pendaftaran = $pendaftaran ? $pendaftaran['status'] : 'belum_mendaftar';

// Menggunakan icon yang lebih representatif
$status_levels = [
    'belum_mendaftar' => ['text' => 'Belum Mendaftar', 'icon' => 'fa-file-pen', 'level' => 0],
    'menunggu' => ['text' => 'Menunggu Verifikasi', 'icon' => 'fa-clock', 'level' => 1],
    'lolos' => ['text' => 'Lolos Administrasi', 'icon' => 'fa-check-circle', 'level' => 2],
    'diterima' => ['text' => 'Diterima', 'icon' => 'fa-user-graduate', 'level' => 3],
    'ditolak' => ['text' => 'Ditolak', 'icon' => 'fa-times-circle', 'level' => -1],
];
$current_level = $status_levels[$status_pendaftaran]['level'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - PPDB Sekolah Impian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <i class="fas fa-graduation-cap fa-lg"></i>
                <span class="brand-font">Petra 2</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-3">
                        <span class="navbar-text text-white">
                            Halo, <strong><?php echo htmlspecialchars($nama_siswa); ?></strong>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm rounded-pill px-3" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card status-card text-center mb-4">
                     <div class="card-header bg-primary">
                        Status Pendaftaran
                    </div>
                    <div class="card-body py-5">
                        <div class="mb-3">
                            <i class="fas <?php echo $status_levels[$status_pendaftaran]['icon']; ?> fa-4x text-warning"></i>
                        </div>
                        <h2 class="card-title">
                            <?php echo $status_levels[$status_pendaftaran]['text']; ?>
                        </h2>
                        <p class="card-text text-muted mb-4 px-lg-5">
                            <?php if ($status_pendaftaran === 'belum_mendaftar'): ?>
                                Segera lengkapi data diri Anda untuk mengikuti proses seleksi Penerimaan Peserta Didik Baru.
                            <?php elseif ($status_pendaftaran === 'menunggu'): ?>
                                Data Anda telah tersimpan dan sedang dalam antrean verifikasi oleh panitia PPDB.
                            <?php elseif ($status_pendaftaran === 'lolos'): ?>
                                Selamat! Anda dinyatakan lolos tahap administrasi. Pantau terus jadwal seleksi berikutnya.
                            <?php elseif ($status_pendaftaran === 'diterima'): ?>
                                Selamat bergabung menjadi bagian dari keluarga besar SMA Kristen Petra 2!
                            <?php elseif ($status_pendaftaran === 'ditolak'): ?>
                                Mohon maaf, berdasarkan hasil seleksi Anda belum dapat diterima tahun ini.
                            <?php endif; ?>
                        </p>

                        <div class="d-flex justify-content-center gap-2">
                            <?php if ($status_pendaftaran === 'belum_mendaftar'): ?>
                                <a href="formulir.php" class="btn btn-primary btn-lg"><i class="fas fa-edit me-2"></i> Isi Formulir Sekarang</a>
                            <?php elseif ($pendaftaran): ?>
                                 <?php if ($status_pendaftaran === 'menunggu'): ?>
                                    <a href="formulir.php" class="btn btn-outline-primary"><i class="fas fa-edit me-2"></i> Edit Data</a>
                                 <?php endif; ?>
                                <a href="cetak.php" class="btn btn-primary" target="_blank"><i class="fas fa-print me-2"></i> Cetak Bukti</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if($pendaftaran): ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary">Detail Biodata</h5>
                        <a href="formulir.php" class="text-muted"><i class="fas fa-external-link-alt"></i></a>
                    </div>
                    <div class="card-body">
                         <div class="row align-items-center">
                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                <?php if(!empty($pendaftaran['foto'])): ?>
                                    <img src="../assets/uploads/<?php echo htmlspecialchars($pendaftaran['foto']); ?>" class="img-fluid rounded-4 shadow-sm" alt="Foto Siswa" style="width: 120px; height: 160px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded-4 d-flex align-items-center justify-content-center" style="width: 100%; height: 160px;">
                                        <i class="fas fa-user fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <h3 class="fw-bold mb-1"><?php echo htmlspecialchars($pendaftaran['nama_lengkap']); ?></h3>
                                <div class="badge bg-warning text-dark mb-3"><?php echo htmlspecialchars($pendaftaran['jurusan_pilihan']); ?></div>
                                <div class="row">
                                    <div class="col-sm-6 mb-2"><small class="text-muted d-block">NISN</small> <strong><?php echo htmlspecialchars($pendaftaran['nisn']); ?></strong></div>
                                    <div class="col-sm-6 mb-2"><small class="text-muted d-block">Asal Sekolah</small> <strong><?php echo htmlspecialchars($pendaftaran['sekolah_asal']); ?></strong></div>
                                    <div class="col-sm-6 mb-2"><small class="text-muted d-block">Tempat Lahir</small> <strong><?php echo htmlspecialchars($pendaftaran['tempat_lahir']); ?></strong></div>
                                    <div class="col-sm-6 mb-2"><small class="text-muted d-block">Tanggal Lahir</small> <strong><?php echo date('d M Y', strtotime($pendaftaran['tanggal_lahir'])); ?></strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                 <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0 text-primary">Alur Pendaftaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div class="timeline-item <?php echo $current_level >= 0 ? 'active' : ''; ?>">
                                <div class="icon"><i class="fas fa-file-pen"></i></div>
                                <div class="content">
                                    <strong class="text-primary">Isi Formulir</strong>
                                    <p class="small text-muted mb-0">Lengkapi biodata & upload berkas.</p>
                                </div>
                            </div>
                            <div class="timeline-item <?php echo $current_level >= 1 ? 'active' : ''; ?>">
                                <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                                <div class="content">
                                    <strong class="text-primary">Verifikasi</strong>
                                    <p class="small text-muted mb-0">Pengecekan data oleh panitia.</p>
                                </div>
                            </div>
                             <div class="timeline-item <?php echo $current_level >= 2 ? 'active' : ''; ?>">
                                <div class="icon"><i class="fas fa-user-check"></i></div>
                                <div class="content">
                                    <strong class="text-primary">Seleksi Administrasi</strong>
                                    <p class="small text-muted mb-0">Pengumuman kelolosan berkas.</p>
                                </div>
                            </div>
                            <div class="timeline-item <?php echo $current_level >= 3 ? 'success' : ''; ?>">
                                <div class="icon"><i class="fas fa-user-graduate"></i></div>
                                <div class="content">
                                    <strong class="text-primary">Diterima</strong>
                                    <p class="small text-muted mb-0">Registrasi ulang siswa baru.</p>
                                </div>
                            </div>
                             <?php if($status_pendaftaran == 'ditolak'): ?>
                             <div class="timeline-item active">
                                <div class="icon bg-danger text-white border-danger"><i class="fas fa-times"></i></div>
                                <div class="content border-danger bg-danger bg-opacity-10">
                                    <strong class="text-danger">Tidak Lolos</strong>
                                    <p class="small text-danger mb-0">Mohon maaf, Anda belum diterima.</p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="text-center py-4 mt-5">
        <div class="container border-top pt-3">
            <p class="mb-0 text-muted small">© <?php echo date('Y'); ?> SMA Kristen Petra 2. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>