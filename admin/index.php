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
    // Logic delete sama seperti sebelumnya...
    $sql_get_user = "SELECT user_id, foto FROM pendaftaran WHERE id = ?";
    $stmt_get_user = $koneksi->prepare($sql_get_user);
    $stmt_get_user->bind_param("i", $id_to_delete);
    $stmt_get_user->execute();
    $result_get_user = $stmt_get_user->get_result();

    if($result_get_user->num_rows > 0) {
        $row = $result_get_user->fetch_assoc();
        $user_id_to_delete = $row['user_id'];
        $foto_to_delete = $row['foto'];

        $stmt_del_pen = $koneksi->prepare("DELETE FROM pendaftaran WHERE id = ?");
        $stmt_del_pen->bind_param("i", $id_to_delete);
        $stmt_del_pen->execute();

        $stmt_del_usr = $koneksi->prepare("DELETE FROM users WHERE id = ?");
        $stmt_del_usr->bind_param("i", $user_id_to_delete);
        $stmt_del_usr->execute();
        
        if (!empty($foto_to_delete) && file_exists('../assets/uploads/' . $foto_to_delete)) {
            unlink('../assets/uploads/' . $foto_to_delete);
        }
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
$stats = $koneksi->query($stats_query)->fetch_assoc();

// --- SEARCH & FILTER (Logika sama) ---
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
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$pendaftar_list = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PPDB Sekolah Impian</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="d-flex">
    <div class="sidebar d-flex flex-column p-3">
        <div class="sidebar-header">
            <a href="index.php" class="d-flex align-items-center text-white text-decoration-none justify-content-center">
                <i class="fas fa-graduation-cap fa-2x text-warning me-2"></i>
                <span class="fs-4 brand-font fw-bold">Petra 2</span>
            </a>
        </div>
        <ul class="nav nav-pills flex-column mb-auto mt-4">
            <li class="nav-item">
                <a href="index.php" class="nav-link active" aria-current="page">
                    <i class="fas fa-tachometer-alt fa-fw me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="info_crud.php" class="nav-link">
                    <i class="fas fa-info-circle fa-fw me-2"></i> Manajemen Info
                </a>
            </li>
             <li>
                <a href="export.php" class="nav-link">
                    <i class="fas fa-file-excel fa-fw me-2"></i> Export Data
                </a>
            </li>
        </ul>
        <div class="dropdown mt-auto p-2 border-top border-light border-opacity-10 pt-3">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-2" style="width:32px; height:32px;"><i class="fas fa-user"></i></div>
                <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark shadow">
                <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt fa-fw me-2"></i> Sign out</a></li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <h2 class="mb-4 fw-bold" style="color: var(--primary-color);">Dashboard Overview</h2>

        <div class="row mb-4 g-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-primary">
                    <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
                    <div class="stat-text">Total Pendaftar</div>
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-warning">
                     <div class="stat-number text-dark"><?php echo $stats['menunggu'] ?? 0; ?></div>
                    <div class="stat-text text-dark">Menunggu Verifikasi</div>
                    <div class="stat-icon text-dark"><i class="fas fa-clock"></i></div>
                </div>
            </div>
             <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-success">
                     <div class="stat-number"><?php echo $stats['diterima'] ?? 0; ?></div>
                    <div class="stat-text">Diterima</div>
                    <div class="stat-icon"><i class="fas fa-user-check"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card bg-danger">
                    <div class="stat-number"><?php echo $stats['ditolak'] ?? 0; ?></div>
                    <div class="stat-text">Ditolak</div>
                    <div class="stat-icon"><i class="fas fa-user-times"></i></div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i> Data Calon Siswa Terbaru</h5>
                <a href="export.php" class="btn btn-sm btn-outline-success rounded-pill"><i class="fas fa-file-excel me-2"></i> Export CSV</a>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Cari Nama atau NISN..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                         <select name="status" class="form-select">
                            <option value="">-- Semua Status --</option>
                            <option value="menunggu" <?php echo $filter_status == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                            <option value="lolos" <?php echo $filter_status == 'lolos' ? 'selected' : ''; ?>>Lolos</option>
                            <option value="diterima" <?php echo $filter_status == 'diterima' ? 'selected' : ''; ?>>Diterima</option>
                            <option value="ditolak" <?php echo $filter_status == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                        </select>
                    </div>
                     <div class="col-md-3">
                        <select name="jurusan" class="form-select">
                            <option value="">-- Semua Jurusan --</option>
                            <option value="IPA" <?php echo $filter_jurusan == 'IPA' ? 'selected' : ''; ?>>IPA</option>
                            <option value="IPS" <?php echo $filter_jurusan == 'IPS' ? 'selected' : ''; ?>>IPS</option>
                            <option value="Bahasa" <?php echo $filter_jurusan == 'Bahasa' ? 'selected' : ''; ?>>Bahasa</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Lengkap</th>
                                <th>NISN</th>
                                <th>Jurusan</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendaftar_list)): ?>
                                <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada data pendaftar.</td></tr>
                            <?php else: ?>
                                <?php 
                                    $status_map = [
                                        'menunggu' => ['class' => 'warning', 'text' => 'Menunggu'],
                                        'lolos' => ['class' => 'info', 'text' => 'Lolos'],
                                        'diterima' => ['class' => 'success', 'text' => 'Diterima'],
                                        'ditolak' => ['class' => 'danger', 'text' => 'Ditolak']
                                    ];
                                ?>
                                <?php foreach ($pendaftar_list as $index => $pendaftar): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($pendaftar['nama_lengkap']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($pendaftar['nisn']); ?></td>
                                    <td><span class="badge bg-secondary rounded-pill"><?php echo htmlspecialchars($pendaftar['jurusan_pilihan']); ?></span></td>
                                    <td>
                                        <?php $status_info = $status_map[$pendaftar['status']]; ?>
                                        <span class="badge bg-<?php echo $status_info['class']; ?> rounded-pill">
                                            <?php echo $status_info['text']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="validasi.php?id=<?php echo $pendaftar['id']; ?>" class="btn btn-sm btn-primary btn-action"><i class="fas fa-eye"></i></a>
                                        <a href="?action=delete&id=<?php echo $pendaftar['id']; ?>" class="btn btn-sm btn-danger btn-action" onclick="return confirm('Hapus data ini?')"><i class="fas fa-trash"></i></a>
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