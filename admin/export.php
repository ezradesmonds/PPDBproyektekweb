<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login if not authenticated as admin
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

// 1. Set Headers
$filename = "data_pendaftar_ppdb_" . date('Y-m-d') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// 2. Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// 3. Write the CSV Header
fputcsv($output, [
    'NISN',
    'Nama Lengkap',
    'Email',
    'Tempat Lahir',
    'Tanggal Lahir',
    'Jenis Kelamin',
    'Agama',
    'Alamat',
    'Asal Sekolah',
    'Jurusan Pilihan',
    'Nama Ayah',
    'Pekerjaan Ayah',
    'Nama Ibu',
    'Pekerjaan Ibu',
    'Status Pendaftaran',
    'Tanggal Daftar'
]);

// 4. Fetch data from the database
$sql = "SELECT 
            u.nisn,
            p.nama_lengkap,
            u.email,
            p.tempat_lahir,
            p.tanggal_lahir,
            p.jenis_kelamin,
            p.agama,
            p.alamat,
            p.sekolah_asal,
            p.jurusan_pilihan,
            p.nama_ayah,
            p.pekerjaan_ayah,
            p.nama_ibu,
            p.pekerjaan_ibu,
            p.status,
            p.created_at
        FROM pendaftaran p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at ASC";

$result = $koneksi->query($sql);

// 5. Write data rows to the CSV file
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Process data for CSV
        $csv_row = [
            $row['nisn'],
            $row['nama_lengkap'],
            $row['email'],
            $row['tempat_lahir'],
            $row['tanggal_lahir'],
            $row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan',
            $row['agama'],
            $row['alamat'],
            $row['sekolah_asal'],
            $row['jurusan_pilihan'],
            $row['nama_ayah'],
            $row['pekerjaan_ayah'],
            $row['nama_ibu'],
            $row['pekerjaan_ibu'],
            ucfirst($row['status']),
            $row['created_at']
        ];
        fputcsv($output, $csv_row);
    }
}

// 6. Close the file pointer
fclose($output);
exit();
?>
