<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';
$user_id = $_SESSION['user_id'];
$feedback = ['error' => '', 'success' => ''];

// ... (Function upload_file SAMA SEPERTI SEBELUMNYA, tidak berubah) ...
function upload_file($file_input_name, $pendaftaran_id, &$current_filename) {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
        $target_dir = "../assets/uploads/";
        if (!empty($current_filename) && file_exists($target_dir . $current_filename)) { unlink($target_dir . $current_filename); }
        $file = $_FILES[$file_input_name];
        $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $safe_name = $file_input_name . '_' . $pendaftaran_id . '_' . time() . '.' . $extension;
        $target_file = $target_dir . $safe_name;
        
        $allowed = ["jpg", "jpeg", "png", "pdf"];
        if (!in_array($extension, $allowed)) return ['error' => "Format file tidak diizinkan."];
        if ($file["size"] > 2097152) return ['error' => "Ukuran file terlalu besar (maks 2MB)."];

        if (move_uploaded_file($file["tmp_name"], $target_file)) return ['success' => $safe_name];
        return ['error' => "Gagal upload file."];
    }
    return [];
}
// ... (Logic POST dan Database Fetch SAMA SEPERTI SEBELUMNYA) ...
// Fetch existing data
$stmt_fetch = $koneksi->prepare("SELECT * FROM pendaftaran WHERE user_id = ?");
$stmt_fetch->bind_param("i", $user_id);
$stmt_fetch->execute();
$pendaftaran = $stmt_fetch->get_result()->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = [
        'nama_lengkap' => $_POST['nama_lengkap'], 'tempat_lahir' => $_POST['tempat_lahir'], 'tanggal_lahir' => $_POST['tanggal_lahir'],
        'jenis_kelamin' => $_POST['jenis_kelamin'], 'agama' => $_POST['agama'], 'alamat' => $_POST['alamat'], 'nama_ayah' => $_POST['nama_ayah'],
        'pekerjaan_ayah' => $_POST['pekerjaan_ayah'], 'nama_ibu' => $_POST['nama_ibu'], 'pekerjaan_ibu' => $_POST['pekerjaan_ibu'],
        'sekolah_asal' => $_POST['sekolah_asal'], 'jurusan_pilihan' => $_POST['jurusan_pilihan']
    ];

    if ($pendaftaran) { // UPDATE
        $pendaftaran_id = $pendaftaran['id'];
        $sql = "UPDATE pendaftaran SET nama_lengkap=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?, agama=?, alamat=?, nama_ayah=?, pekerjaan_ayah=?, nama_ibu=?, pekerjaan_ibu=?, sekolah_asal=?, jurusan_pilihan=?, status='menunggu' WHERE id=?";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("ssssssssssssi", $data['nama_lengkap'], $data['tempat_lahir'], $data['tanggal_lahir'], $data['jenis_kelamin'], $data['agama'], $data['alamat'], $data['nama_ayah'], $data['pekerjaan_ayah'], $data['nama_ibu'], $data['pekerjaan_ibu'], $data['sekolah_asal'], $data['jurusan_pilihan'], $pendaftaran_id);
    } else { // CREATE
        $sql = "INSERT INTO pendaftaran (user_id, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, alamat, nama_ayah, pekerjaan_ayah, nama_ibu, pekerjaan_ibu, sekolah_asal, jurusan_pilihan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("issssssssssss", $user_id, $data['nama_lengkap'], $data['tempat_lahir'], $data['tanggal_lahir'], $data['jenis_kelamin'], $data['agama'], $data['alamat'], $data['nama_ayah'], $data['pekerjaan_ayah'], $data['nama_ibu'], $data['pekerjaan_ibu'], $data['sekolah_asal'], $data['jurusan_pilihan']);
    }

    if ($stmt->execute()) {
        $pendaftaran_id = $pendaftaran ? $pendaftaran['id'] : $koneksi->insert_id;
        
        $stmt_refetch = $koneksi->prepare("SELECT foto, kk, akta, sertifikat FROM pendaftaran WHERE id = ?");
        $stmt_refetch->bind_param("i", $pendaftaran_id);
        $stmt_refetch->execute();
        $current_files = $stmt_refetch->get_result()->fetch_assoc();

        foreach (['foto', 'kk', 'akta', 'sertifikat'] as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
                $current = $current_files[$field] ?? null;
                $res = upload_file($field, $pendaftaran_id, $current);
                
                if (isset($res['success'])) {
                    $stmt_upd = $koneksi->prepare("UPDATE pendaftaran SET $field = ? WHERE id = ?");
                    $stmt_upd->bind_param("si", $res['success'], $pendaftaran_id);
                    $stmt_upd->execute();
                } elseif (isset($res['error'])) { 
                    $feedback['error'] .= "Gagal upload $field: " . $res['error'] . "<br>"; 
                }
            }
        }

        if (empty($feedback['error'])) {
            header("Location: index.php?status=submitted");
            exit();
        } else {
            $feedback['error'] = "Data profil disimpan, NAMUN ada kendala dokumen:<br>" . $feedback['error'];
        }
    } else { 
        $feedback['error'] = "Gagal menyimpan data ke database."; 
    }

    $stmt_fetch->execute();
    $pendaftaran = $stmt_fetch->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pendaftaran - PPDB Sekolah Impian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
    
    <nav class="navbar navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fas fa-arrow-left me-3"></i>
                <span class="brand-font fw-bold">Kembali ke Dashboard</span>
            </a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-header bg-white pt-4 pb-3">
                        <h2 class="text-primary text-center fw-bold mb-0">Formulir Pendaftaran</h2>
                        <p class="text-center text-muted">Mohon isi data dengan benar dan jujur.</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        
                        <?php if ($feedback['error']): ?><div class="alert alert-danger rounded-3"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $feedback['error']; ?></div><?php endif; ?>
                        <?php if ($feedback['success']): ?><div class="alert alert-success rounded-3"><i class="fas fa-check-circle me-2"></i><?php echo $feedback['success']; ?></div><?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            
                            <div class="mb-5">
                                <h4 class="text-primary border-bottom pb-2 mb-4"><i class="fas fa-user me-2"></i> Data Diri</h4>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold small">Nama Lengkap</label>
                                        <input type="text" class="form-control" name="nama_lengkap" value="<?php echo htmlspecialchars($pendaftaran['nama_lengkap'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Jenis Kelamin</label>
                                        <select class="form-select" name="jenis_kelamin" required>
                                            <option value="L" <?php echo (isset($pendaftaran['jenis_kelamin']) && $pendaftaran['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                            <option value="P" <?php echo (isset($pendaftaran['jenis_kelamin']) && $pendaftaran['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Tempat Lahir</label>
                                        <input type="text" class="form-control" name="tempat_lahir" value="<?php echo htmlspecialchars($pendaftaran['tempat_lahir'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Tanggal Lahir</label>
                                        <input type="date" class="form-control" name="tanggal_lahir" value="<?php echo htmlspecialchars($pendaftaran['tanggal_lahir'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Agama</label>
                                        <input type="text" class="form-control" name="agama" value="<?php echo htmlspecialchars($pendaftaran['agama'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small">Alamat Lengkap</label>
                                        <textarea class="form-control" name="alamat" rows="2" required><?php echo htmlspecialchars($pendaftaran['alamat'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-5">
                                <h4 class="text-primary border-bottom pb-2 mb-4"><i class="fas fa-users me-2"></i> Data Orang Tua</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Nama Ayah</label>
                                        <input type="text" class="form-control" name="nama_ayah" value="<?php echo htmlspecialchars($pendaftaran['nama_ayah'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Pekerjaan Ayah</label>
                                        <input type="text" class="form-control" name="pekerjaan_ayah" value="<?php echo htmlspecialchars($pendaftaran['pekerjaan_ayah'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Nama Ibu</label>
                                        <input type="text" class="form-control" name="nama_ibu" value="<?php echo htmlspecialchars($pendaftaran['nama_ibu'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Pekerjaan Ibu</label>
                                        <input type="text" class="form-control" name="pekerjaan_ibu" value="<?php echo htmlspecialchars($pendaftaran['pekerjaan_ibu'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-5">
                                <h4 class="text-primary border-bottom pb-2 mb-4"><i class="fas fa-university me-2"></i> Akademik & Jurusan</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Sekolah Asal</label>
                                        <input type="text" class="form-control" name="sekolah_asal" value="<?php echo htmlspecialchars($pendaftaran['sekolah_asal'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Pilihan Jurusan</label>
                                        <select class="form-select" name="jurusan_pilihan" required>
                                            <option value="" disabled <?php echo empty($pendaftaran['jurusan_pilihan']) ? 'selected' : ''; ?>>-- Pilih Jurusan --</option>
                                            <option value="IPA" <?php echo (isset($pendaftaran['jurusan_pilihan']) && $pendaftaran['jurusan_pilihan'] == 'IPA') ? 'selected' : ''; ?>>IPA</option>
                                            <option value="IPS" <?php echo (isset($pendaftaran['jurusan_pilihan']) && $pendaftaran['jurusan_pilihan'] == 'IPS') ? 'selected' : ''; ?>>IPS</option>
                                            <option value="Bahasa" <?php echo (isset($pendaftaran['jurusan_pilihan']) && $pendaftaran['jurusan_pilihan'] == 'Bahasa') ? 'selected' : ''; ?>>Bahasa</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-5">
                                <h4 class="text-primary border-bottom pb-2 mb-4"><i class="fas fa-cloud-upload-alt me-2"></i> Dokumen</h4>
                                <div class="alert alert-warning small d-flex align-items-center">
                                    <i class="fas fa-info-circle fa-2x me-3"></i>
                                    <div>Format: <strong>JPG, PNG, PDF</strong>. Maks <strong>2MB</strong>.</div>
                                </div>
                                <div class="row g-4">
                                    <?php 
                                        $files = ['foto' => 'Pas Foto 3x4', 'kk' => 'Kartu Keluarga', 'akta' => 'Akta Kelahiran', 'sertifikat' => 'Sertifikat (Opsional)'];
                                        foreach($files as $name => $label): 
                                    ?>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small"><?php echo $label; ?></label>
                                        <input type="file" class="form-control" name="<?php echo $name; ?>">
                                        <?php if(!empty($pendaftaran[$name])): ?>
                                            <div class="mt-1 small text-success"><i class="fas fa-check-circle"></i> File terupload</div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="d-grid mt-5">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm py-3">Simpan Pendaftaran</button>
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