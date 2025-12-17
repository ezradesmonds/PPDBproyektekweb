<?php
session_start();
include 'koneksi.php';

$error = '';
$success = '';

if (isset($_SESSION['user_id'])) {
    header("Location: siswa/index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... (Logic PHP Register sama persis, tidak berubah) ...
    $nama = $_POST['nama'];
    $nisn = $_POST['nisn'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($password !== $password_confirm) {
        $error = "Konfirmasi password tidak cocok.";
    } elseif (empty($nama) || empty($nisn) || empty($email) || empty($password)) {
        $error = "Semua field wajib diisi.";
    } else {
        $sql_check = "SELECT * FROM users WHERE email = ? OR nisn = ?";
        $stmt_check = $koneksi->prepare($sql_check);
        $stmt_check->bind_param("ss", $email, $nisn);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $existing = $result_check->fetch_assoc();
            $error = ($existing['email'] === $email) ? "Email sudah terdaftar." : "NISN sudah terdaftar.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'siswa';
            $sql_insert = "INSERT INTO users (nama, nisn, email, password, role) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $koneksi->prepare($sql_insert);
            $stmt_insert->bind_param("sssss", $nama, $nisn, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                $success = "Pendaftaran berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan sistem.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - PPDB Sekolah Impian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body class="register-page">

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <i class="fas fa-user-plus fa-2x mb-3 text-warning"></i>
                <h2>Buat Akun Baru</h2>
                <p>Bergabunglah dengan SMA Kristen Petra 2</p>
            </div>
            
            <div class="register-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="text-center py-4">
                        <div class="mb-3 text-success"><i class="fas fa-check-circle fa-4x"></i></div>
                        <h4 class="fw-bold text-success mb-3">Pendaftaran Berhasil!</h4>
                        <p class="text-muted mb-4">Akun Anda telah berhasil dibuat. Silakan login untuk melanjutkan.</p>
                        <a href="login.php" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-sm">Ke Halaman Login</a>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama sesuai ijazah" required value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="nisn" class="form-label">NISN</label>
                            <input type="text" class="form-control" id="nisn" name="nisn" placeholder="Nomor Induk Siswa Nasional" required value="<?php echo isset($_POST['nisn']) ? htmlspecialchars($_POST['nisn']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Aktif</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="contoh@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Min. 6 karakter" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Ulangi password" required>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-register shadow-sm">DAFTAR SEKARANG</button>
                        </div>
                    </form>
                    
                    <div class="register-footer">
                        <p class="mb-1">Sudah punya akun?</p>
                        <a href="login.php" class="fw-bold">Login di sini</a>
                        <div class="mt-4 border-top pt-3">
                            <a href="index.php" class="text-muted small"><i class="fas fa-home me-1"></i> Halaman Utama</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>