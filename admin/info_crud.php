<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

// --- LOGIC ---
// Handle Delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql_delete = "DELETE FROM info WHERE id = ?";
    $stmt_delete = $koneksi->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id);
    $stmt_delete->execute();
    header("Location: info_crud.php?status=deleted");
    exit();
}

// Handle Create/Update
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $tipe = $_POST['tipe'];
    $judul = $_POST['judul'];
    $konten = $_POST['konten'];

    if (empty($tipe) || empty($judul) || empty($konten)) {
        $error = 'Semua field wajib diisi.';
    } else {
        if ($id > 0) { // Update
            $sql = "UPDATE info SET tipe=?, judul=?, konten=? WHERE id=?";
            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param("sssi", $tipe, $judul, $konten, $id);
            $success = 'Data berhasil diperbarui!';
        } else { // Create
            $sql = "INSERT INTO info (tipe, judul, konten) VALUES (?, ?, ?)";
            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param("sss", $tipe, $judul, $konten);
            $success = 'Data berhasil ditambahkan!';
        }

        if (!$stmt->execute()) {
            $error = 'Gagal menyimpan data. ' . $stmt->error;
            $success = '';
        }
    }
}


// --- DATA ---
// Fetch item for editing
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Informasi - PPDB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #343a40; }
        .sidebar .nav-link { color: #adb5bd; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { color: #fff; }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar p-3 d-flex flex-column">
        <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <i class="fas fa-school me-2"></i><span class="fs-4">Admin PPDB</span>
        </a>
        <hr class="text-white">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
            <li><a href="info_crud.php" class="nav-link active"><i class="fas fa-info-circle me-2"></i> Manajemen Info</a></li>
            <li><a href="export.php" class="nav-link"><i class="fas fa-file-excel me-2"></i> Export Data</a></li>
        </ul>
        <hr class="text-white">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle me-2"></i><strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="../logout.php">Sign out</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 p-4">
        <h1 class="mb-4">Manajemen Informasi</h1>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Daftar Informasi</div>
                    <div class="card-body">
                         <form method="GET" class="row g-3 mb-3">
                            <div class="col-md-9">
                                <select name="tipe" class="form-select">
                                    <option value="">Semua Tipe</option>
                                    <?php foreach ($info_types as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php echo ($filter_tipe == $type) ? 'selected' : ''; ?>><?php echo ucfirst($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
                        </form>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead><tr><th>Tipe</th><th>Judul</th><th>Aksi</th></tr></thead>
                                <tbody>
                                    <?php while($item = $info_list->fetch_assoc()): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?php echo $item['tipe']; ?></span></td>
                                        <td><?php echo htmlspecialchars($item['judul']); ?></td>
                                        <td>
                                            <a href="?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="?action=delete&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin?')"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header"><?php echo $edit_item ? 'Edit' : 'Tambah'; ?> Informasi</div>
                    <div class="card-body">
                         <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                         <?php if ($success && !$error): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $edit_item['id'] ?? 0; ?>">
                            <div class="mb-3">
                                <label class="form-label">Tipe</label>
                                <select name="tipe" class="form-select" required>
                                    <option value="">--Pilih Tipe--</option>
                                     <?php foreach ($info_types as $type): ?>
                                    <option value="<?php echo $type; ?>" <?php echo (isset($edit_item['tipe']) && $edit_item['tipe'] == $type) ? 'selected' : ''; ?>><?php echo ucfirst($type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Judul</label>
                                <input type="text" name="judul" class="form-control" value="<?php echo htmlspecialchars($edit_item['judul'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Konten</label>
                                <textarea name="konten" class="form-control" rows="5" required><?php echo htmlspecialchars($edit_item['konten'] ?? ''); ?></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><?php echo $edit_item ? 'Update' : 'Simpan'; ?></button>
                                <?php if ($edit_item): ?>
                                <a href="info_crud.php" class="btn btn-secondary">Batal</a>
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
