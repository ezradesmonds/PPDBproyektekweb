<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

$feedback = ['error' => '', 'success' => ''];

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql_delete = "DELETE FROM info WHERE id = ?";
    $stmt_delete = $koneksi->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id);
    if ($stmt_delete->execute()) {
        $_SESSION['feedback'] = ['success' => 'Informasi berhasil dihapus.'];
    } else {
        $_SESSION['feedback'] = ['error' => 'Gagal menghapus informasi.'];
    }
    header("Location: info_crud.php");
    exit();
}

// Handle Create/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $tipe = $_POST['tipe'];
    $judul = $_POST['judul'];
    $konten = $_POST['konten'];

    if (empty($tipe) || empty($judul) || empty($konten)) {
        $feedback['error'] = 'Semua field wajib diisi.';
    } else {
        if ($id > 0) { // Update
            $sql = "UPDATE info SET tipe=?, judul=?, konten=? WHERE id=?";
            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param("sssi", $tipe, $judul, $konten, $id);
            $feedback['success'] = 'Informasi berhasil diperbarui!';
        } else { // Create
            $sql = "INSERT INTO info (tipe, judul, konten) VALUES (?, ?, ?)";
            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param("sss", $tipe, $judul, $konten);
            $feedback['success'] = 'Informasi baru berhasil ditambahkan!';
        }

        if ($stmt->execute()) {
            $_SESSION['feedback'] = ['success' => $feedback['success']];
            header("Location: info_crud.php");
            exit();
        } else {
            $feedback['error'] = 'Gagal menyimpan data: ' . $stmt->error;
        }
    }
}

if (isset($_SESSION['feedback'])) {
    $feedback = array_merge($feedback, $_SESSION['feedback']);
    unset($_SESSION['feedback']);
}

// --- DATA ---
$edit_item = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql_edit = "SELECT * FROM info WHERE id = ?";
    $stmt_edit = $koneksi->prepare($sql_edit);
    $stmt_edit->bind_param("i", $id);
    $stmt_edit->execute();
    $edit_item = $stmt_edit->get_result()->fetch_assoc();
}

// Fetch all info items
$filter_tipe = $_GET['tipe'] ?? '';
$sql_list = "SELECT * FROM info WHERE 1=1";
if(!empty($filter_tipe)) {
    $sql_list .= " AND tipe = '" . $koneksi->real_escape_string($filter_tipe) . "'";
}
$sql_list .= " ORDER BY created_at DESC";
$info_list = $koneksi->query($sql_list);

$info_types = ['pendaftaran', 'beasiswa', 'pengumuman', 'faq', 'profil'];
$type_colors = ['pendaftaran' => 'primary', 'beasiswa' => 'success', 'pengumuman' => 'warning', 'faq' => 'info', 'profil' => 'secondary'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Informasi - Admin PPDB</title>
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
            <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard</a></li>
            <li><a href="info_crud.php" class="nav-link active" aria-current="page"><i class="fas fa-info-circle fa-fw me-2"></i> Manajemen Info</a></li>
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
        <h1 class="mb-4">Manajemen Informasi</h1>

        <?php if ($feedback['error']): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $feedback['error']; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>
        <?php if ($feedback['success']): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fas fa-check-circle me-2"></i><?php echo $feedback['success']; ?><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div><?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header"><i class="fas fa-list me-2"></i> Daftar Informasi</div>
                    <div class="card-body">
                         <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-9">
                                <select name="tipe" class="form-select">
                                    <option value="">Filter berdasarkan tipe...</option>
                                    <?php foreach ($info_types as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php echo ($filter_tipe == $type) ? 'selected' : ''; ?>><?php echo ucfirst($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 d-grid"><button type="submit" class="btn btn-primary">Filter</button></div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light"><tr><th>Tipe</th><th>Judul</th><th class="text-center">Aksi</th></tr></thead>
                                <tbody>
                                    <?php if ($info_list->num_rows > 0): ?>
                                        <?php while($item = $info_list->fetch_assoc()): ?>
                                        <tr>
                                            <td><span class="badge text-dark-emphasis bg-<?php echo $type_colors[$item['tipe']]; ?>-subtle border border-<?php echo $type_colors[$item['tipe']]; ?>-subtle"><?php echo ucfirst($item['tipe']); ?></span></td>
                                            <td><?php echo htmlspecialchars($item['judul']); ?></td>
                                            <td class="text-center">
                                                <a href="?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary btn-action" title="Edit"><i class="fas fa-edit"></i></a>
                                                <a href="?action=delete&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger btn-action" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus informasi ini?')"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="3" class="text-center py-4">Tidak ada data untuk ditampilkan.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header"><i class="fas <?php echo $edit_item ? 'fa-edit' : 'fa-plus-circle'; ?> me-2"></i> <?php echo $edit_item ? 'Edit' : 'Tambah'; ?> Informasi</div>
                    <div class="card-body">
                        <form method="POST" action="info_crud.php">
                            <input type="hidden" name="id" value="<?php echo $edit_item['id'] ?? 0; ?>">
                            <div class="mb-3">
                                <label for="tipe" class="form-label">Tipe</label>
                                <select id="tipe" name="tipe" class="form-select" required>
                                    <option value="">--Pilih Tipe--</option>
                                     <?php foreach ($info_types as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php echo (isset($edit_item['tipe']) && $edit_item['tipe'] == $type) ? 'selected' : ''; ?>><?php echo ucfirst($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="judul" class="form-label">Judul</label>
                                <input type="text" id="judul" name="judul" class="form-control" value="<?php echo htmlspecialchars($edit_item['judul'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="konten" class="form-label">Konten</label>
                                <textarea id="konten" name="konten" class="form-control" rows="8" required><?php echo htmlspecialchars($edit_item['konten'] ?? ''); ?></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> <?php echo $edit_item ? 'Update Informasi' : 'Simpan Informasi'; ?></button>
                                <?php if ($edit_item): ?>
                                <a href="info_crud.php" class="btn btn-secondary">Batal Edit</a>
                                <?php endif; ?>
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
