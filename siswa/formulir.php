<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

$user_id = $_SESSION['user_id'];
$feedback = ['error' => '', 'success' => ''];

// Function to handle file uploads
function upload_file($file_input_name, $pendaftaran_id, &$current_filename) {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
        $target_dir = "../assets/uploads/";
        
        // Delete old file if it exists
        if (!empty($current_filename) && file_exists($target_dir . $current_filename)) {
            unlink($target_dir . $current_filename);
        }

        $file = $_FILES[$file_input_name];
        $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $safe_name = $file_input_name . '_' . $pendaftaran_id . '_' . time() . '.' . $extension;
        $target_file = $target_dir . $safe_name;

        // --- VALIDATION ---

        // 1. Check file size
        if ($file["size"] > 2097152) { // 2MB
            return ['error' => "Ukuran file '{$file['name']}' terlalu besar (maks 2MB)."];
        }

        // 2. Check allowed extensions
        $allowed_formats = ["jpg", "jpeg", "png", "pdf"];
        if (!in_array($extension, $allowed_formats)) {
            return ['error' => "Format file '{$file['name']}' tidak diizinkan (hanya JPG, JPEG, PNG, PDF)."];
        }

        // 3. Check MIME type and content
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file["tmp_name"]);
        finfo_close($finfo);

        $allowed_mime_types = [
            'image/jpeg',
            'image/png',
            'application/pdf'
        ];

        if (!in_array($mime_type, $allowed_mime_types)) {
             return ['error' => "Tipe file '{$file['name']}' tidak valid."];
        }

        // For images, double-check if it's a valid image
        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            if (getimagesize($file["tmp_name"]) === false) {
                 return ['error' => "File '{$file['name']}' bukan gambar yang valid."];
            }
        }
        
        // --- END VALIDATION ---

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return ['success' => $safe_name];
        } else {
            return ['error' => "Gagal mengupload file '{$file['name']}'."];
        }
    }
    return []; // No file uploaded, not an error
}

// Fetch existing data
$sql_fetch = "SELECT * FROM pendaftaran WHERE user_id = ?";
$stmt_fetch = $koneksi->prepare($sql_fetch);
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
        $stmt->bind_param("ssssssssssssi", 
            $data['nama_lengkap'], $data['tempat_lahir'], $data['tanggal_lahir'], 
            $data['jenis_kelamin'], $data['agama'], $data['alamat'], 
            $data['nama_ayah'], $data['pekerjaan_ayah'], $data['nama_ibu'], 
            $data['pekerjaan_ibu'], $data['sekolah_asal'], $data['jurusan_pilihan'], 
            $pendaftaran_id
        );
    } else { // CREATE
        $sql = "INSERT INTO pendaftaran (user_id, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, alamat, nama_ayah, pekerjaan_ayah, nama_ibu, pekerjaan_ibu, sekolah_asal, jurusan_pilihan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("issssssssssss", 
            $user_id, $data['nama_lengkap'], $data['tempat_lahir'], $data['tanggal_lahir'], 
            $data['jenis_kelamin'], $data['agama'], $data['alamat'], 
            $data['nama_ayah'], $data['pekerjaan_ayah'], $data['nama_ibu'], 
            $data['pekerjaan_ibu'], $data['sekolah_asal'], $data['jurusan_pilihan']
        );
    }

    if ($stmt->execute()) {
        $pendaftaran_id = $pendaftaran ? $pendaftaran['id'] : $koneksi->insert_id;
        $feedback['success'] = "Data berhasil disimpan! Status pendaftaran Anda telah diperbarui menjadi 'Menunggu Verifikasi'.";

        // Refresh data to get current filenames
        $stmt_refetch = $koneksi->prepare("SELECT foto, kk, akta, sertifikat FROM pendaftaran WHERE id = ?");
        $stmt_refetch->bind_param("i", $pendaftaran_id);
        $stmt_refetch->execute();
        $current_files = $stmt_refetch->get_result()->fetch_assoc();

        $file_fields = ['foto', 'kk', 'akta', 'sertifikat'];
        foreach ($file_fields as $field) {
            $current_filename = $current_files[$field] ?? null;
            $upload_result = upload_file($field, $pendaftaran_id, $current_filename);
            if (isset($upload_result['success'])) {
                $sql_update_file = "UPDATE pendaftaran SET $field = ? WHERE id = ?";
                $stmt_update_file = $koneksi->prepare($sql_update_file);
                $stmt_update_file->bind_param("si", $upload_result['success'], $pendaftaran_id);
                $stmt_update_file->execute();
            } elseif (isset($upload_result['error'])) {
                $feedback['error'] .= $upload_result['error'] . "<br>";
            }
        }
        
        if(empty($feedback['error'])) {
            header("Location: index.php?status=submitted");
            exit();
        }
    } else {
        $feedback['error'] = "Gagal menyimpan data: " . $stmt->error;
    }
    
    // Refresh data on error to show updated fields
    $stmt_fetch->execute();
    $pendaftaran = $stmt_fetch->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pendaftaran - PPDB Sekolah Impian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="main-content" style="margin-left: 0;">
    <div class="container py-5">
         <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">Formulir Pendaftaran</h1>
            <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Kembali ke Dashboard</a>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-file-alt me-2"></i> Lengkapi Data Pendaftaran Anda</h4>
            </div>
            <div class="card-body p-4">
                <?php if ($feedback['error']): ?><div class="alert alert-danger"><?php echo $feedback['error']; ?></div><?php endif; ?>
                <?php if ($feedback['success']): ?><div class="alert alert-success"><?php echo $feedback['success']; ?></div><?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="mb-5">
                        <h5 class="card-title text-primary"><i class="fas fa-user-alt me-2"></i> Data Diri Calon Siswa</h5>
                        <hr class="mt-2">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($pendaftaran['nama_lengkap'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jenis Kelamin</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="jenis_kelamin" id="lk" value="L" <?php echo (isset($pendaftaran['jenis_kelamin']) && $pendaftaran['jenis_kelamin'] == 'L') ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="lk">Laki-laki</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="jenis_kelamin" id="pr" value="P" <?php echo (isset($pendaftaran['jenis_kelamin']) && $pendaftaran['jenis_kelamin'] == 'P') ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="pr">Perempuan</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                         <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="<?php echo htmlspecialchars($pendaftaran['tempat_lahir'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo htmlspecialchars($pendaftaran['tanggal_lahir'] ?? ''); ?>" required>
                            </div>
                             <div class="col-md-4 mb-3">
                                 <label for="agama" class="form-label">Agama</label>
                                 <input type="text" class="form-control" id="agama" name="agama" value="<?php echo htmlspecialchars($pendaftaran['agama'] ?? ''); ?>" required>
                             </div>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($pendaftaran['alamat'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h5 class="card-title text-primary"><i class="fas fa-users me-2"></i> Data Orang Tua / Wali</h5>
                        <hr class="mt-2">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama_ayah" class="form-label">Nama Ayah</label>
                                <input type="text" class="form-control" id="nama_ayah" name="nama_ayah" value="<?php echo htmlspecialchars($pendaftaran['nama_ayah'] ?? ''); ?>" required>
                            </div>
                             <div class="col-md-6 mb-3">
                                <label for="pekerjaan_ayah" class="form-label">Pekerjaan Ayah</label>
                                <input type="text" class="form-control" id="pekerjaan_ayah" name="pekerjaan_ayah" value="<?php echo htmlspecialchars($pendaftaran['pekerjaan_ayah'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nama_ibu" class="form-label">Nama Ibu</label>
                                <input type="text" class="form-control" id="nama_ibu" name="nama_ibu" value="<?php echo htmlspecialchars($pendaftaran['nama_ibu'] ?? ''); ?>" required>
                            </div>
                             <div class="col-md-6 mb-3">
                                <label for="pekerjaan_ibu" class="form-label">Pekerjaan Ibu</label>
                                <input type="text" class="form-control" id="pekerjaan_ibu" name="pekerjaan_ibu" value="<?php echo htmlspecialchars($pendaftaran['pekerjaan_ibu'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h5 class="card-title text-primary"><i class="fas fa-school me-2"></i> Data Akademik & Pilihan Jurusan</h5>
                        <hr class="mt-2">
                        <div class="row">
                             <div class="col-md-6 mb-3">
                                <label for="sekolah_asal" class="form-label">Sekolah Asal</label>
                                <input type="text" class="form-control" id="sekolah_asal" name="sekolah_asal" value="<?php echo htmlspecialchars($pendaftaran['sekolah_asal'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="jurusan_pilihan" class="form-label">Pilihan Jurusan</label>
                                <select class="form-select" id="jurusan_pilihan" name="jurusan_pilihan" required>
                                    <option value="" disabled <?php echo empty($pendaftaran['jurusan_pilihan']) ? 'selected' : ''; ?>>-- Pilih Jurusan --</option>
                                    <option value="IPA" <?php echo (isset($pendaftaran['jurusan_pilihan']) && $pendaftaran['jurusan_pilihan'] == 'IPA') ? 'selected' : ''; ?>>IPA</option>
                                    <option value="IPS" <?php echo (isset($pendaftaran['jurusan_pilihan']) && $pendaftaran['jurusan_pilihan'] == 'IPS') ? 'selected' : ''; ?>>IPS</option>
                                    <option value="Bahasa" <?php echo (isset($pendaftaran['jurusan_pilihan']) && $pendaftaran['jurusan_pilihan'] == 'Bahasa') ? 'selected' : ''; ?>>Bahasa</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h5 class="card-title text-primary"><i class="fas fa-cloud-upload-alt me-2"></i> Upload Dokumen</h5>
                        <hr class="mt-2">
                         <div class="alert alert-warning small">
                            <strong><i class="fas fa-exclamation-triangle me-2"></i>Perhatian!</strong>
                            <ul class="mb-0 mt-2">
                                <li>Tipe file yang diizinkan: <strong>JPG, JPEG, PNG, PDF</strong>.</li>
                                <li>Ukuran file maksimal: <strong>2 MB</strong> per file.</li>
                                <li>Jika Anda ingin mengganti file yang sudah diunggah, cukup unggah file yang baru.</li>
                            </ul>
                        </div>
                        <div class="row">
                            <?php 
                                $file_inputs = [
                                    'foto' => 'Pas Foto (3x4)',
                                    'kk' => 'Scan Kartu Keluarga (KK)',
                                    'akta' => 'Scan Akta Kelahiran',
                                    'sertifikat' => 'Scan Sertifikat Prestasi (Opsional)'
                                ];
                                foreach($file_inputs as $name => $label):
                            ?>
                            <div class="col-md-6 mb-3">
                                <label for="<?php echo $name; ?>" class="form-label"><?php echo $label; ?></label>
                                <input class="form-control" type="file" id="<?php echo $name; ?>" name="<?php echo $name; ?>">
                                <?php if(!empty($pendaftaran[$name])): ?>
                                <div class="mt-2">
                                    <small class="text-muted">File saat ini: </small>
                                    <a href="../assets/uploads/<?php echo htmlspecialchars($pendaftaran[$name]); ?>" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-file-alt"></i> <?php echo htmlspecialchars(substr($pendaftaran[$name], 0, 30)) . (strlen($pendaftaran[$name]) > 30 ? '...' : ''); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                         <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Simpan Data Pendaftaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
