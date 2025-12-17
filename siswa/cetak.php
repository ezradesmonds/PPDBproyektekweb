<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

$user_id = $_SESSION['user_id'];

// Fetch registration data along with user's NISN
$sql = "SELECT p.*, u.nisn FROM pendaftaran p JOIN users u ON p.user_id = u.id WHERE p.user_id = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pendaftaran = $result->fetch_assoc();

if (!$pendaftaran) {
    echo "Data pendaftaran tidak ditemukan. Silakan isi formulir terlebih dahulu.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pendaftaran - <?php echo htmlspecialchars($pendaftaran['nama_lengkap']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                -webkit-print-color-adjust: exact; /* Chrome, Safari */
                color-adjust: exact; /* Firefox */
            }
        }
        body {
            background-color: #f4f4f4;
        }
        .proof-container {
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 40px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        .header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .header h3, .header h4 {
            margin: 0;
            font-weight: bold;
        }
        .table-data th {
            width: 35%;
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

<div class="proof-container">
    <div class="header">
        <h3>BUKTI PENDAFTARAN PESERTA DIDIK BARU (PPDB)</h3>
        <h4>SEKOLAH IMPIAN - TAHUN AJARAN 2026/2027</h4>
    </div>

    <div class="text-center mb-4">
        <?php if(!empty($pendaftaran['foto'])): ?>
            <img src="../assets/uploads/<?php echo htmlspecialchars($pendaftaran['foto']); ?>" class="img-thumbnail" alt="Foto Siswa" style="max-width: 150px;">
        <?php else: ?>
            <img src="https://placehold.co/150x200/ced4da/6c757d?text=Foto" class="img-thumbnail" alt="Foto Siswa" style="max-width: 150px;">
        <?php endif; ?>
    </div>

    <table class="table table-bordered table-striped table-data">
        <tbody>
            <tr>
                <th colspan="2" class="text-center bg-primary text-white">DATA SISWA</th>
            </tr>
            <tr>
                <th>Nomor Pendaftaran / NISN</th>
                <td><?php echo htmlspecialchars($pendaftaran['nisn']); ?></td>
            </tr>
            <tr>
                <th>Nama Lengkap</th>
                <td><?php echo htmlspecialchars($pendaftaran['nama_lengkap']); ?></td>
            </tr>
            <tr>
                <th>Tempat, Tanggal Lahir</th>
                <td><?php echo htmlspecialchars($pendaftaran['tempat_lahir']) . ', ' . date('d F Y', strtotime($pendaftaran['tanggal_lahir'])); ?></td>
            </tr>
            <tr>
                <th>Jenis Kelamin</th>
                <td><?php echo $pendaftaran['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
            </tr>
             <tr>
                <th>Agama</th>
                <td><?php echo htmlspecialchars($pendaftaran['agama']); ?></td>
            </tr>
            <tr>
                <th>Alamat</th>
                <td><?php echo htmlspecialchars($pendaftaran['alamat']); ?></td>
            </tr>
             <tr>
                <th>Asal Sekolah</th>
                <td><?php echo htmlspecialchars($pendaftaran['sekolah_asal']); ?></td>
            </tr>
             <tr>
                <th>Jurusan Pilihan</th>
                <td><?php echo htmlspecialchars($pendaftaran['jurusan_pilihan']); ?></td>
            </tr>
             <tr>
                <th colspan="2" class="text-center bg-primary text-white">DATA ORANG TUA</th>
            </tr>
             <tr>
                <th>Nama Ayah</th>
                <td><?php echo htmlspecialchars($pendaftaran['nama_ayah']); ?></td>
            </tr>
              <tr>
                <th>Pekerjaan Ayah</th>
                <td><?php echo htmlspecialchars($pendaftaran['pekerjaan_ayah']); ?></td>
            </tr>
             <tr>
                <th>Nama Ibu</th>
                <td><?php echo htmlspecialchars($pendaftaran['nama_ibu']); ?></td>
            </tr>
             <tr>
                <th>Pekerjaan Ibu</th>
                <td><?php echo htmlspecialchars($pendaftaran['pekerjaan_ibu']); ?></td>
            </tr>
             <tr>
                <th colspan="2" class="text-center bg-info text-dark">STATUS PENDAFTARAN</th>
            </tr>
             <tr>
                <th>Status</th>
                <td><strong><?php echo strtoupper(htmlspecialchars($pendaftaran['status'])); ?></strong></td>
            </tr>
        </tbody>
    </table>

    <div class="mt-4">
        <p><strong>Perhatian:</strong></p>
        <ul>
            <li>Ini adalah bukti pendaftaran yang sah.</li>
            <li>Harap simpan bukti ini untuk keperluan verifikasi dan pengumuman selanjutnya.</li>
            <li>Selalu cek status pendaftaran Anda melalui dashboard siswa.</li>
        </ul>
    </div>
    
    <div class="mt-5 text-end">
        <p>Dicetak pada: <?php echo date('d F Y, H:i:s'); ?></p>
    </div>

</div>

<div class="container text-center mb-4 no-print">
    <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Cetak Halaman Ini</button>
    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
</div>

</body>
</html>
