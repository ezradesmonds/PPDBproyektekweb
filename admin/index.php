<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

// --- DELETE ACTION ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_to_delete = intval($_GET['id']);
    // We should also delete the associated user account to keep the database clean
    // First, get the user_id from the pendaftaran table
    $sql_get_user = "SELECT user_id FROM pendaftaran WHERE id = ?";
    $stmt_get_user = $koneksi->prepare($sql_get_user);
    $stmt_get_user->bind_param("i", $id_to_delete);
    $stmt_get_user->execute();
    $result_get_user = $stmt_get_user->get_result();
    if($result_get_user->num_rows > 0) {
        $user_id_to_delete = $result_get_user->fetch_assoc()['user_id'];

        // Delete from pendaftaran table
        $sql_delete_pendaftaran = "DELETE FROM pendaftaran WHERE id = ?";
        $stmt_delete_pendaftaran = $koneksi->prepare($sql_delete_pendaftaran);
        $stmt_delete_pendaftaran->bind_param("i", $id_to_delete);
        $stmt_delete_pendaftaran->execute();

        // Delete from users table
        $sql_delete_user = "DELETE FROM users WHERE id = ?";
        $stmt_delete_user = $koneksi->prepare($sql_delete_user);
        $stmt_delete_user->bind_param("i", $user_id_to_delete);
        $stmt_delete_user->execute();
    }
    
    header("Location: index.php?status=deleted");
    exit();
}

// --- STATS ---
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
    SUM(CASE WHEN status = 'lolos' THEN 1 ELSE 0 END) as lolos,
    SUM(CASE WHEN status = 'diterima' THEN 1 ELSE 0 END) as diterima,
    SUM(CASE WHEN status = 'ditolak' THEN 1 ELSE 0 END) as ditolak
    FROM pendaftaran";
$stats_result = $koneksi->query($stats_query);
$stats = $stats_result->fetch_assoc();

// --- SEARCH & FILTER ---
$search = $_GET['search'] ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_jurusan = $_GET['jurusan'] ?? '';

$sql = "SELECT p.id, p.nama_lengkap, u.nisn, p.jurusan_pilihan, p.status 
        FROM pendaftaran p 
        JOIN users u ON p.user_id = u.id 
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($search)) {
    $sql .= " AND (p.nama_lengkap LIKE ? OR u.nisn LIKE ?)";
    $search_param = "%" . $search . "%";
    array_push($params, $search_param, $search_param);
    $types .= 'ss';
}
if (!empty($filter_status)) {
    $sql .= " AND p.status = ?";
    array_push($params, $filter_status);
    $types .= 's';
}
if (!empty($filter_jurusan)) {
    $sql .= " AND p.jurusan_pilihan = ?";
    array_push($params, $filter_jurusan);
    $types .= 's';
}

$sql .= " ORDER BY p.created_at DESC";
$stmt = $koneksi->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$pendaftar_list = $result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PPDB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #343a40; }
        .sidebar .nav-link { color: #adb5bd; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { color: #fff; }
        .stat-card { text-align: center; }
        .stat-card .stat-number { font-size: 2.5rem; font-weight: 700; }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar p-3 d-flex flex-column">
        <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <i class="fas fa-school me-2"></i>
            <span class="fs-4">Admin PPDB</span>
        </a>
        <hr class="text-white">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="index.php" class="nav-link active" aria-current="page">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="info_crud.php" class="nav-link">
                    <i class="fas fa-info-circle me-2"></i> Manajemen Info
                </a>
            </li>
             <li>
                <a href="export.php" class="nav-link">
                    <i class="fas fa-file-excel me-2"></i> Export Data
                </a>
            </li>
        </ul>
        <hr class="text-white">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-user-circle me-2"></i>
                <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="../logout.php">Sign out</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 p-4">
        <h1 class="mb-4">Dashboard Admin</h1>

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body stat-card">
                        <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
                        <div class="stat-text">Total Pendaftar</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body stat-card">
                         <div class="stat-number"><?php echo $stats['menunggu'] ?? 0; ?></div>
                        <div class="stat-text">Menunggu Verifikasi</div>
                    </div>
                </div>
            </div>
             <div class="col-md-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body stat-card">
                         <div class="stat-number"><?php echo $stats['diterima'] ?? 0; ?></div>
                        <div class="stat-text">Diterima</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body stat-card">
                        <div class="stat-number"><?php echo $stats['ditolak'] ?? 0; ?></div>
                        <div class="stat-text">Ditolak</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pendaftar List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users"></i> Daftar Calon Siswa</h5>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari Nama atau NISN..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                         <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="menunggu" <?php echo $filter_status == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                            <option value="lolos" <?php echo $filter_status == 'lolos' ? 'selected' : ''; ?>>Lolos</option>
                            <option value="diterima" <?php echo $filter_status == 'diterima' ? 'selected' : ''; ?>>Diterima</option>
                            <option value="ditolak" <?php echo $filter_status == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>
                     <div class="col-md-3">
                        <select name="jurusan" class="form-select">
                            <option value="">Semua Jurusan</option>
                            <option value="IPA" <?php echo $filter_jurusan == 'IPA' ? 'selected' : ''; ?>>IPA</option>
                            <option value="IPS" <?php echo $filter_jurusan == 'IPS' ? 'selected' : ''; ?>>IPS</option>
                            <option value="Bahasa" <?php echo $filter_jurusan == 'Bahasa' ? 'selected' : ''; ?>>Bahasa</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Nama Lengkap</th>
                                <th>NISN</th>
                                <th>Jurusan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendaftar_list)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data pendaftar.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendaftar_list as $index => $pendaftar): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($pendaftar['nisn']); ?></td>
                                    <td><?php echo htmlspecialchars($pendaftar['jurusan_pilihan']); ?></td>
                                    <td><span class="badge bg-<?php echo ['menunggu' => 'warning', 'lolos' => 'info', 'diterima' => 'success', 'ditolak' => 'danger'][$pendaftar['status']]; ?>"><?php echo ucfirst($pendaftar['status']); ?></span></td>
                                    <td>
                                        <a href="validasi.php?id=<?php echo $pendaftar['id']; ?>" class="btn btn-sm btn-info" title="Lihat/Validasi"><i class="fas fa-eye"></i></a>
                                        <a href="?action=delete&id=<?php echo $pendaftar['id']; ?>" class="btn btn-sm btn-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus data pendaftar ini?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
