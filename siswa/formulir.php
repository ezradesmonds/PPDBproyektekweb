<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Function to handle file uploads
function upload_file($file_input_name, $pendaftaran_id) {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
        $target_dir = "../assets/uploads/";
        $extension = pathinfo($_FILES[$file_input_name]["name"], PATHINFO_EXTENSION);
        $safe_name = $file_input_name . '_' . $pendaftaran_id . '_' . time() . '.' . $extension;
        $target_file = $target_dir . $safe_name;

        // Check if file is a real image or fake image
        $check = getimagesize($_FILES[$file_input_name]["tmp_name"]);
        if($check === false) {
            return ['error' => "File is not an image."];
        }

        // Check file size
        if ($_FILES[$file_input_name]["size"] > 2000000) { // 2MB
            return ['error' => "Sorry, your file is too large."];
        }

        // Allow certain file formats
        $allowed_formats = ["jpg", "jpeg", "png", "pdf"];
        if (!in_array(strtolower($extension), $allowed_formats)) {
            return ['error' => "Sorry, only JPG, JPEG, PNG & PDF files are allowed."];
        }

        if (move_uploaded_file($_FILES[$file_input_name]["tmp_name"], $target_file)) {
            return ['success' => $safe_name];
        } else {
            return ['error' => "Sorry, there was an error uploading your file."];
        }
    }
    return ['error' => 'No file uploaded or an error occurred.'];
}


// Fetch existing data
$sql_fetch = "SELECT * FROM pendaftaran WHERE user_id = ?";
$stmt_fetch = $koneksi->prepare($sql_fetch);
$stmt_fetch->bind_param("i", $user_id);
$stmt_fetch->execute();
$result = $stmt_fetch->get_result();
$pendaftaran = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect data from form
    $nama_lengkap = $_POST['nama_lengkap'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $agama = $_POST['agama'];
    $alamat = $_POST['alamat'];
    $nama_ayah = $_POST['nama_ayah'];
    $pekerjaan_ayah = $_POST['pekerjaan_ayah'];
    $nama_ibu = $_POST['nama_ibu'];
    $pekerjaan_ibu = $_POST['pekerjaan_ibu'];
    $sekolah_asal = $_POST['sekolah_asal'];
    $jurusan_pilihan = $_POST['jurusan_pilihan'];
    
    // If data exists, update it (UPDATE)
    if ($pendaftaran) {
        $pendaftaran_id = $pendaftaran['id'];
        $sql = "UPDATE pendaftaran SET nama_lengkap=?, tempat_lahir=?, tanggal_lahir=?, jenis_kelamin=?, agama=?, alamat=?, nama_ayah=?, pekerjaan_ayah=?, nama_ibu=?, pekerjaan_ibu=?, sekolah_asal=?, jurusan_pilihan=?, status='menunggu' WHERE id=?";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("ssssssssssssi", $nama_lengkap, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $agama, $alamat, $nama_ayah, $pekerjaan_ayah, $nama_ibu, $pekerjaan_ibu, $sekolah_asal, $jurusan_pilihan, $pendaftaran_id);
    } 
    // If no data, insert new (CREATE)
    else {
        $sql = "INSERT INTO pendaftaran (user_id, nama_lengkap, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, alamat, nama_ayah, pekerjaan_ayah, nama_ibu, pekerjaan_ibu, sekolah_asal, jurusan_pilihan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("issssssssssss", $user_id, $nama_lengkap, $tempat_lahir, $tanggal_lahir, $jenis_kelamin, $agama, $alamat, $nama_ayah, $pekerjaan_ayah, $nama_ibu, $pekerjaan_ibu, $sekolah_asal, $jurusan_pilihan);
    }

    if ($stmt->execute()) {
        $pendaftaran_id = $pendaftaran ? $pendaftaran['id'] : $koneksi->insert_id;
        $success = "Data berhasil disimpan!";

        // Handle file uploads
        $file_fields = ['foto', 'kk', 'akta', 'sertifikat'];
        foreach ($file_fields as $field) {
            $upload_result = upload_file($field, $pendaftaran_id);
            if(isset($upload_result['success'])) {
                $filename = $upload_result['success'];
                $sql_update_file = "UPDATE pendaftaran SET $field = ? WHERE id = ?";
                $stmt_update_file = $koneksi->prepare($sql_update_file);
                $stmt_update_file->bind_param("si", $filename, $pendaftaran_id);
                $stmt_update_file->execute();
            } elseif(isset($upload_result['error']) && $_FILES[$field]['error'] != UPLOAD_ERR_NO_FILE) {
                $error .= $field . ": " . $upload_result['error'] . "<br>";
            }
        }

        // Refresh data after update
        $stmt_fetch->execute();
        $pendaftaran = $stmt_fetch->get_result()->fetch_assoc();

    } else {
        $error = "Gagal menyimpan data: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pendaftaran - PPDB</title>
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

<div class="container mt-5 mb-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Formulir Pendaftaran Siswa Baru</h4>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <!-- Data Diri -->
                <h5><i class="fas fa-user-alt"></i> Data Diri Calon Siswa</h5>
                <hr>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($pendaftaran['nama_lengkap'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="<?php echo htmlspecialchars($pendaftaran['tempat_lahir'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo htmlspecialchars($pendaftaran['tanggal_lahir'] ?? ''); ?>" required>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis Kelamin</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="lk" value="L" <?php echo (isset($pendaftaran['jenis_kelamin']) && $pendaftaran['jenis_kelamin'] == 'L') ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="lk">Laki-laki</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="pr" value="P" <?php echo (isset($pendaftaran['jenis_kelamin']) && $pendaftaran['jenis_kelamin'] == 'P') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="pr">Perempuan</label>
                        </div>
                    </div>
                     <div class="col-md-6 mb-3">
                         <label for="agama" class="form-label">Agama</label>
                         <input type="text" class="form-control" id="agama" name="agama" value="<?php echo htmlspecialchars($pendaftaran['agama'] ?? ''); ?>" required>
                     </div>
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat Lengkap</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($pendaftaran['alamat'] ?? ''); ?></textarea>
                </div>

                <!-- Data Orang Tua -->
                <h5 class="mt-4"><i class="fas fa-users"></i> Data Orang Tua / Wali</h5>
                <hr>
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

                <!-- Pilihan Sekolah & Jurusan -->
                <h5 class="mt-4"><i class="fas fa-school"></i> Data Akademik & Pilihan Jurusan</h5>
                <hr>
                <div class="row">
                     <div class="col-md-6 mb-3">
                        <label for="sekolah_asal" class="form-label">Sekolah Asal</label>
                        <input type="text" class="form-control" id="sekolah_asal" name="sekolah_asal" value="<?php echo htmlspecialchars($pendaftaran['sekolah_asal'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="jurusan_pilihan" class="form-label">Pilihan Jurusan</label>
                        <select class="form-select" id="jurusan_pilihan" name="jurusan_pilihan" required>
                            <option value="">-- Pilih Jurusan --</option>
                            <option value="IPA" <?php echo (isset($pendaftaran['jurusan_pilihan']) && $pendaftaran['jurusan_pilihan'] == 'IPA') ? 'selected' : ''; ?>>IPA</option>
                            <option value="IPS" <?php echo (isset($pendaftaran['jurusan_pilihan']) && $pendaftaran['jurusan_pilihan'] == 'IPS') ? 'selected' : ''; ?>>IPS</option>
                            <option value="Bahasa" <?php echo (isset($pendaftaran['jurusan_pilihan']) && $pendaftaran['jurusan_pilihan'] == 'Bahasa') ? 'selected' : ''; ?>>Bahasa</option>
                        </select>
                    </div>
                </div>
                
                <!-- Upload Dokumen -->
                <h5 class="mt-4"><i class="fas fa-cloud-upload-alt"></i> Upload Dokumen</h5>
                <hr>
                 <div class="alert alert-info">
                    <strong>Perhatian!</strong>
                    <ul>
                        <li>File harus berupa gambar (JPG, JPEG, PNG) atau PDF.</li>
                        <li>Ukuran file maksimal 2MB.</li>
                        <li>Jika Anda sudah pernah upload, file tidak perlu di-upload ulang kecuali jika ingin diganti.</li>
                    </ul>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="foto" class="form-label">Pas Foto (3x4)</label>
                        <input class="form-control" type="file" id="foto" name="foto">
                        <?php if(isset($pendaftaran['foto']) && $pendaftaran['foto']): ?>
                        <small class="form-text text-muted">File saat ini: <a href="../assets/uploads/<?php echo $pendaftaran['foto']; ?>" target="_blank"><?php echo $pendaftaran['foto']; ?></a></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="kk" class="form-label">Scan Kartu Keluarga (KK)</label>
                        <input class="form-control" type="file" id="kk" name="kk">
                         <?php if(isset($pendaftaran['kk']) && $pendaftaran['kk']): ?>
                        <small class="form-text text-muted">File saat ini: <a href="../assets/uploads/<?php echo $pendaftaran['kk']; ?>" target="_blank"><?php echo $pendaftaran['kk']; ?></a></small>
                        <?php endif; ?>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="akta" class="form-label">Scan Akta Kelahiran</label>
                        <input class="form-control" type="file" id="akta" name="akta">
                         <?php if(isset($pendaftaran['akta']) && $pendaftaran['akta']): ?>
                        <small class="form-text text-muted">File saat ini: <a href="../assets/uploads/<?php echo $pendaftaran['akta']; ?>" target="_blank"><?php echo $pendaftaran['akta']; ?></a></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="sertifikat" class="form-label">Scan Sertifikat Prestasi (Jika Ada)</label>
                        <input class="form-control" type="file" id="sertifikat" name="sertifikat">
                         <?php if(isset($pendaftaran['sertifikat']) && $pendaftaran['sertifikat']): ?>
                        <small class="form-text text-muted">File saat ini: <a href="../assets/uploads/<?php echo $pendaftaran['sertifikat']; ?>" target="_blank"><?php echo $pendaftaran['sertifikat']; ?></a></small>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                     <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Simpan Data Pendaftaran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
