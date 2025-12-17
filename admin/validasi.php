<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

$pendaftaran_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
$success = '';

if ($pendaftaran_id <= 0) {
    header("Location: index.php");
    exit();
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $allowed_statuses = ['menunggu', 'lolos', 'diterima', 'ditolak'];

    if (in_array($new_status, $allowed_statuses)) {
        $sql_update = "UPDATE pendaftaran SET status = ? WHERE id = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        $stmt_update->bind_param("si", $new_status, $pendaftaran_id);
        if ($stmt_update->execute()) {
            $success = "Status pendaftaran berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui status.";
        }
    } else {
        $error = "Status tidak valid.";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Pendaftar - PPDB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
         <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="btn btn-danger" href="../logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detail Calon Siswa</h4>
                    <span class="badge bg-<?php echo ['menunggu' => 'warning', 'lolos' => 'info', 'diterima' => 'success', 'ditolak' => 'danger'][$pendaftar['status']]; ?> fs-6"><?php echo ucfirst($pendaftar['status']); ?></span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <?php if(!empty($pendaftar['foto'])): ?>
                                <img src="../assets/uploads/<?php echo htmlspecialchars($pendaftar['foto']); ?>" class="img-fluid rounded border" alt="Foto Siswa">
                            <?php else: ?>
                                <img src="https://placehold.co/150x200/ced4da/6c757d?text=Foto" class="img-fluid rounded" alt="Foto Siswa">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h5><?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></h5>
                            <ul class="list-unstyled">
                                <li><strong>NISN:</strong> <?php echo htmlspecialchars($pendaftar['nisn']); ?></li>
                                <li><strong>Email:</strong> <?php echo htmlspecialchars($pendaftar['email']); ?></li>
                                <li><strong>Jurusan Pilihan:</strong> <?php echo htmlspecialchars($pendaftar['jurusan_pilihan']); ?></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                      <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="data-diri-tab" data-bs-toggle="tab" data-bs-target="#data-diri" type="button" role="tab">Data Diri</button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="data-ortu-tab" data-bs-toggle="tab" data-bs-target="#data-ortu" type="button" role="tab">Data Orang Tua</button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="dokumen-tab" data-bs-toggle="tab" data-bs-target="#dokumen" type="button" role="tab">Dokumen</button>
                      </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content pt-3">
                      <div class="tab-pane active" id="data-diri" role="tabpanel">
                          <table class="table table-sm table-bordered">
                              <tr><th>Tempat, Tgl Lahir</th><td><?php echo htmlspecialchars($pendaftar['tempat_lahir']) . ', ' . date('d F Y', strtotime($pendaftar['tanggal_lahir'])); ?></td></tr>
                              <tr><th>Jenis Kelamin</th><td><?php echo $pendaftar['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></td></tr>
                              <tr><th>Agama</th><td><?php echo htmlspecialchars($pendaftar['agama']); ?></td></tr>
                              <tr><th>Alamat</th><td><?php echo htmlspecialchars($pendaftar['alamat']); ?></td></tr>
                              <tr><th>Sekolah Asal</th><td><?php echo htmlspecialchars($pendaftar['sekolah_asal']); ?></td></tr>
                          </table>
                      </div>
                      <div class="tab-pane" id="data-ortu" role="tabpanel">
                          <table class="table table-sm table-bordered">
                               <tr><th>Nama Ayah</th><td><?php echo htmlspecialchars($pendaftar['nama_ayah']); ?></td></tr>
                               <tr><th>Pekerjaan Ayah</th><td><?php echo htmlspecialchars($pendaftar['pekerjaan_ayah']); ?></td></tr>
                               <tr><th>Nama Ibu</th><td><?php echo htmlspecialchars($pendaftar['nama_ibu']); ?></td></tr>
                               <tr><th>Pekerjaan Ibu</th><td><?php echo htmlspecialchars($pendaftar['pekerjaan_ibu']); ?></td></tr>
                          </table>
                      </div>
                      <div class="tab-pane" id="dokumen" role="tabpanel">
                          <ul class="list-group">
                              <?php
                                $dokumen_fields = ['kk' => 'Kartu Keluarga', 'akta' => 'Akta Kelahiran', 'sertifikat' => 'Sertifikat Prestasi'];
                                foreach ($dokumen_fields as $field => $label) {
                                    if (!empty($pendaftar[$field])) {
                                        echo '<li class="list-group-item"><a href="../assets/uploads/' . htmlspecialchars($pendaftar[$field]) . '" target="_blank">' . $label . '</a></li>';
                                    } else {
                                        echo '<li class="list-group-item text-muted">' . $label . ' (Tidak diupload)</li>';
                                    }
                                }
                              ?>
                          </ul>
                      </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
             <div class="card">
                 <div class="card-header">
                     <h5 class="mb-0">Validasi Pendaftaran</h5>
                 </div>
                 <div class="card-body">
                    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

                     <form method="POST">
                         <div class="mb-3">
                             <label for="status" class="form-label">Ubah Status Pendaftaran</label>
                             <select name="status" id="status" class="form-select">
                                 <option value="menunggu" <?php echo $pendaftar['status'] == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                 <option value="lolos" <?php echo $pendaftar['status'] == 'lolos' ? 'selected' : ''; ?>>Lolos Administrasi</option>
                                 <option value="diterima" <?php echo $pendaftar['status'] == 'diterima' ? 'selected' : ''; ?>>Diterima</option>
                                 <option value="ditolak" <?php echo $pendaftar['status'] == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                             </select>
                         </div>
                         <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Status</button>
                         </div>
                     </form>
                 </div>
             </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
