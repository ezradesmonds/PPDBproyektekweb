<?php
session_start();
include 'koneksi.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: siswa/index.php");
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: siswa/index.php");
                }
                exit();
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Email tidak ditemukan.";
        }
    } else {
        $error = "Email dan password tidak boleh kosong.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PPDB Sekolah Impian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="login-page">

    <div class="login-container">
        <div class="login-image-container">
            <h1>Selamat Datang!</h1>
            <p>Sistem Penerimaan Peserta Didik Baru Sekolah Impian. Silakan login untuk melanjutkan.</p>
        </div>
        <div class="login-form-container">
            <div class="login-header">
                <h2><i class="fas fa-user-circle"></i> Login Akun</h2>
                <p>Gunakan email dan password Anda</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="contoh@email.com" required>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-login">Login</button>
                </div>
            </form>

            <div class="login-footer">
                <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                <p><a href="index.php">Kembali ke Halaman Utama</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
