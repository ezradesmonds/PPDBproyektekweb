<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

$pendaftaran_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($pendaftaran_id <= 0) {
    header("Location: index.php");
    exit();
}

$feedback = ['error' => '', 'success' => ''];

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $allowed_statuses = ['menunggu', 'lolos', 'diterima', 'ditolak'];

    if (in_array($new_status, $allowed_statuses)) {
        $sql_update = "UPDATE pendaftaran SET status = ? WHERE id = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        $stmt_update->bind_param("si", $new_status, $pendaftaran_id);
        if ($stmt_update->execute()) {
            $feedback['success'] = "Status pendaftaran berhasil diperbarui!";
        } else {
            $feedback['error'] = "Gagal memperbarui status.";
        }
    } else {
        $feedback['error'] = "Status tidak valid.";
    }
}

// Fetch registration data
$sql = "SELECT p.*, u.nisn, u.email FROM pendaftaran p JOIN users u ON p.user_id = u.id WHERE p.id = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $pendaftaran_id);
$stmt->execute();
$result = $stmt->get_result();
$pendaftar = $result->fetch_assoc();

if (!$pendaftar) {
    header("Location: index.php?error=notfound");
    exit();
}

$status_map = [
    'menunggu' => ['class' => 'warning', 'text' => 'Menunggu'],
    'lolos' => ['class' => 'info', 'text' => 'Lolos'],
    'diterima' => ['class' => 'success', 'text' => 'Diterima'],
    'ditolak' => ['class' => 'danger', 'text' => 'Ditolak']
];
$current_status_info = $status_map[$pendaftar['status']];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Pendaftar - <?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column p-3">
        <div class="sidebar-header"><a href="index.php" class="d-flex align-items-center text-white text-decoration-none"><i class="fas fa-school me-2"></i><span class="fs-4">Admin PPDB</span></a></div>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="index.php" class="nav-link active" aria-current="page"><i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard</a></li>
            <li><a href="info_crud.php" class="nav-link"><i class="fas fa-info-circle fa-fw me-2"></i> Manajemen Info</a></li>
            <li><a href="export.php" class="nav-link"><i class="fas fa-file-excel fa-fw me-2"></i> Export Data</a></li>
        </ul>
        <div class="dropdown mt-auto">
             <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle p-2" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle fa-fw me-2"></i><strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt fa-fw me-2"></i> Sign out</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Validasi Pendaftar</h1>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard</a>
        </div>

        <?php if ($feedback['error']): ?><div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $feedback['error']; ?></div><?php endif; ?>
        <?php if ($feedback['success']): ?><div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $feedback['success']; ?></div><?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i> Detail Calon Siswa</h5>
                        <span class="badge text-dark-emphasis bg-<?php echo $current_status_info['class']; ?>-subtle border border-<?php echo $current_status_info['class']; ?>-subtle fs-6"><?php echo $current_status_info['text']; ?></span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4 align-items-center">
                            <div class="col-md-3 text-center">
                                <?php if(!empty($pendaftar['foto'])): ?>
                                    <img src="../assets/uploads/<?php echo htmlspecialchars($pendaftar['foto']); ?>" class="img-fluid rounded-circle border p-1" style="width: 120px; height: 120px; object-fit: cover;" alt="Foto Siswa">
                                <?php else: ?>
                                    <img src="https://placehold.co/120x120/ced4da/6c757d?text=Foto" class="img-fluid rounded-circle" alt="Foto Siswa">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <h4 class="mb-1"><?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></h4>
                                <p class="text-muted mb-2">Pilihan Jurusan: <strong><?php echo htmlspecialchars($pendaftar['jurusan_pilihan']); ?></strong></p>
                                <ul class="list-unstyled d-flex flex-wrap">
                                    <li class="me-4"><i class="fas fa-id-card fa-fw me-2 text-muted"></i><?php echo htmlspecialchars($pendaftar['nisn']); ?></li>
                                    <li><i class="fas fa-envelope fa-fw me-2 text-muted"></i><?php echo htmlspecialchars($pendaftar['email']); ?></li>
                                </ul>
                            </div>
                        </div>
                        
                        <ul class="nav nav-tabs nav-fill" id="myTab" role="tablist">
                          <li class="nav-item" role="presentation"><button class="nav-link active" id="data-diri-tab" data-bs-toggle="tab" data-bs-target="#data-diri" type="button" role="tab"><i class="fas fa-user me-2"></i>Data Diri</button></li>
                          <li class="nav-item" role="presentation"><button class="nav-link" id="data-ortu-tab" data-bs-toggle="tab" data-bs-target="#data-ortu" type="button" role="tab"><i class="fas fa-users me-2"></i>Data Orang Tua</button></li>
                          <li class="nav-item" role="presentation"><button class="nav-link" id="dokumen-tab" data-bs-toggle="tab" data-bs-target="#dokumen" type="button" role="tab"><i class="fas fa-folder-open me-2"></i>Dokumen</button></li>
                        </ul>

                        <div class="tab-content p-3 border border-top-0 rounded-bottom">
                          <div class="tab-pane active" id="data-diri" role="tabpanel">
                              <table class="table table-sm table-borderless">
                                  <tr><th width="30%">Tempat, Tgl Lahir</th><td>: <?php echo htmlspecialchars($pendaftar['tempat_lahir']) . ', ' . date('d F Y', strtotime($pendaftar['tanggal_lahir'])); ?></td></tr>
                                  <tr><th>Jenis Kelamin</th><td>: <?php echo $pendaftar['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></td></tr>
                                  <tr><th>Agama</th><td>: <?php echo htmlspecialchars($pendaftar['agama']); ?></td></tr>
                                  <tr><th>Alamat</th><td>: <?php echo htmlspecialchars($pendaftar['alamat']); ?></td></tr>
                                  <tr><th>Sekolah Asal</th><td>: <?php echo htmlspecialchars($pendaftar['sekolah_asal']); ?></td></tr>
                              </table>
                          </div>
                          <div class="tab-pane" id="data-ortu" role="tabpanel">
                              <table class="table table-sm table-borderless">
                                   <tr><th width="30%">Nama Ayah</th><td>: <?php echo htmlspecialchars($pendaftar['nama_ayah']); ?></td></tr>
                                   <tr><th>Pekerjaan Ayah</th><td>: <?php echo htmlspecialchars($pendaftar['pekerjaan_ayah']); ?></td></tr>
                                   <tr><th>Nama Ibu</th><td>: <?php echo htmlspecialchars($pendaftar['nama_ibu']); ?></td></tr>
                                   <tr><th>Pekerjaan Ibu</th><td>: <?php echo htmlspecialchars($pendaftar['pekerjaan_ibu']); ?></td></tr>
                              </table>
                          </div>
                          <div class="tab-pane" id="dokumen" role="tabpanel">
                              <div class="list-group">
                                  <?php
                                    $dokumen_fields = ['kk' => 'Kartu Keluarga', 'akta' => 'Akta Kelahiran', 'sertifikat' => 'Sertifikat Prestasi'];
                                    foreach ($dokumen_fields as $field => $label) {
                                        if (!empty($pendaftar[$field])) {
                                            echo '<a href="../assets/uploads/' . htmlspecialchars($pendaftar[$field]) . '" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">' . $label . '<i class="fas fa-external-link-alt"></i></a>';
                                        } else {
                                            echo '<li class="list-group-item text-muted">' . $label . ' (Tidak diupload)</li>';
                                        }
                                    }
                                  ?>
                              </div>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                 <div class="card sticky-top" style="top: 20px;">
                     <div class="card-header"><h5 class="mb-0"><i class="fas fa-check-double me-2"></i>Validasi Pendaftaran</h5></div>
                     <div class="card-body">
                         <form method="POST">
                             <div class="mb-3">
                                 <label for="status" class="form-label">Ubah Status Pendaftaran</label>
                                 <select name="status" id="status" class="form-select form-select-lg">
                                     <option value="menunggu" <?php echo $pendaftar['status'] == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                     <option value="lolos" <?php echo $pendaftar['status'] == 'lolos' ? 'selected' : ''; ?>>Lolos Administrasi</option>
                                     <option value="diterima" <?php echo $pendaftar['status'] == 'diterima' ? 'selected' : ''; ?>>Diterima</option>
                                     <option value="ditolak" <?php echo $pendaftar['status'] == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                                 </select>
                             </div>
                             <div class="d-grid">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Status</button>
                             </div>
                         </form>
                     </div>
                 </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
