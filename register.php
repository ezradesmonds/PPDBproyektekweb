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
        // Check if email or nisn already exists
        $sql_check = "SELECT * FROM users WHERE email = ? OR nisn = ?";
        $stmt_check = $koneksi->prepare($sql_check);
        $stmt_check->bind_param("ss", $email, $nisn);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $existing_user = $result_check->fetch_assoc();
            if ($existing_user['email'] === $email) {
                $error = "Email sudah terdaftar.";
            } else {
                $error = "NISN sudah terdaftar.";
            }
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'siswa';

            $sql_insert = "INSERT INTO users (nama, nisn, email, password, role) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $koneksi->prepare($sql_insert);
            $stmt_insert->bind_param("sssss", $nama, $nisn, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                $success = "Pendaftaran berhasil! Silakan <a href='login.php' class='alert-link'>login</a> untuk melanjutkan.";
            } else {
                $error = "Terjadi kesalahan saat mendaftar. Silakan coba lagi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - PPDB Sekolah Impian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body class="register-page">

    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h2><i class="fas fa-user-plus"></i> Buat Akun Baru</h2>
                <p>Selamat datang di PPDB Sekolah Impian</p>
            </div>
            <div class="register-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                    <div class="text-center mt-3">
                         <a href="login.php" class="btn btn-primary">Ke Halaman Login</a>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap Anda" required value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="nisn" class="form-label">NISN</label>
                            <input type="text" class="form-control" id="nisn" name="nisn" placeholder="Nomor Induk Siswa Nasional" required value="<?php echo isset($_POST['nisn']) ? htmlspecialchars($_POST['nisn']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Alamat Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="contoh@email.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Ulangi password" required>
                            </div>
                        </div>
                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary btn-register">Daftar Sekarang</button>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="register-footer">
                    <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
                    <p><a href="index.php">Kembali ke Halaman Utama</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
