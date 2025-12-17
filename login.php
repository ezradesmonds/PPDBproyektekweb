<?php
session_start();
include 'koneksi.php';

$error = '';
// ... (Logic PHP sama, tidak berubah) ...
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') { header("Location: admin/index.php"); } 
    else { header("Location: siswa/index.php"); }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $koneksi->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                if ($user['role'] == 'admin') { header("Location: admin/index.php"); } 
                else { header("Location: siswa/index.php"); }
                exit();
            } else { $error = "Password salah."; }
        } else { $error = "Email tidak ditemukan."; }
    } else { $error = "Email dan password tidak boleh kosong."; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PPDB Sekolah Impian</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body class="login-page">

    <div class="login-container">
        <div class="login-image-container">
            <i class="fas fa-graduation-cap fa-3x mb-3 text-warning"></i>
            <h1>Petra 2</h1>
            <p class="mb-0">Selamat Datang di Portal PPDB.<br>Membangun Generasi Cerdas & Berkarakter.</p>
        </div>
        
        <div class="login-form-container">
            <div class="login-header">
                <h2>Login Akun</h2>
                <p>Silakan masuk untuk melanjutkan</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger rounded-4 border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label text-muted small fw-bold ms-1">EMAIL</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-4 ps-3"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0 rounded-end-4" id="email" name="email" placeholder="nama@email.com" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label text-muted small fw-bold ms-1">PASSWORD</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-4 ps-3"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" class="form-control border-start-0 rounded-end-4" id="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-login shadow-sm">MASUK SEKARANG <i class="fas fa-arrow-right ms-2"></i></button>
                </div>
            </form>

            <div class="login-footer">
                <p class="mb-1">Belum memiliki akun?</p>
                <a href="register.php" class="fw-bold">Daftar Akun Baru</a>
                <div class="mt-4 pt-3 border-top">
                    <a href="index.php" class="text-muted small"><i class="fas fa-arrow-left me-1"></i> Kembali ke Beranda</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>