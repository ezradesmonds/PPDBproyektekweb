<?php
// Suppress errors to provide custom messages
error_reporting(0);
ini_set('display_errors', 0);

$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is empty
$dbname = "ppdb_sekolah";

$messages = [];

// Step 1: Connect to MySQL server (without selecting a database)
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    $messages[] = ['type' => 'danger', 'text' => "Connection to MySQL failed: " . htmlspecialchars($conn->connect_error)];
} else {
    $messages[] = ['type' => 'success', 'text' => "Successfully connected to MySQL server."];
    
    // Step 2: Create the database if it doesn't exist
    $db_exists_query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMAS WHERE SCHEMA_NAME = '$dbname'";
    $db_exists_result = $conn->query($db_exists_query);

    if ($db_exists_result->num_rows > 0) {
        $messages[] = ['type' => 'info', 'text' => "Database '{$dbname}' already exists. No need to create it."];
    } else {
        $sql_create_db = "CREATE DATABASE $dbname";
        if ($conn->query($sql_create_db) === TRUE) {
            $messages[] = ['type' => 'success', 'text' => "Database '{$dbname}' created successfully."];
        } else {
            $messages[] = ['type' => 'danger', 'text' => "Error creating database: " . htmlspecialchars($conn->error)];
        }
    }
    
    // Step 3: Select the database
    $conn->select_db($dbname);
    
    // Step 4: Define and execute table creation queries
    $sql_queries = [
        "CREATE TABLE IF NOT EXISTS `users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `nama` varchar(255) NOT NULL,
          `email` varchar(255) NOT NULL,
          `password` varchar(255) NOT NULL,
          `role` enum('admin','siswa') NOT NULL,
          `nisn` varchar(20) DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `email` (`email`),
          UNIQUE KEY `nisn` (`nisn`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `pendaftaran` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `nama_lengkap` varchar(255) NOT NULL,
          `tempat_lahir` varchar(100) NOT NULL,
          `tanggal_lahir` date NOT NULL,
          `jenis_kelamin` enum('L','P') NOT NULL,
          `agama` varchar(50) NOT NULL,
          `alamat` text NOT NULL,
          `nama_ayah` varchar(255) NOT NULL,
          `pekerjaan_ayah` varchar(100) NOT NULL,
          `nama_ibu` varchar(255) NOT NULL,
          `pekerjaan_ibu` varchar(100) NOT NULL,
          `sekolah_asal` varchar(255) NOT NULL,
          `jurusan_pilihan` varchar(100) NOT NULL,
          `foto` varchar(255) DEFAULT NULL,
          `kk` varchar(255) DEFAULT NULL,
          `akta` varchar(255) DEFAULT NULL,
          `sertifikat` varchar(255) DEFAULT NULL,
          `status` enum('menunggu','lolos','ditolak','diterima') NOT NULL DEFAULT 'menunggu',
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`),
          CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        "CREATE TABLE IF NOT EXISTS `info` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `tipe` enum('beasiswa','pendaftaran','pengumuman','faq','profil') NOT NULL,
          `judul` varchar(255) NOT NULL,
          `konten` text NOT NULL,
          `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // Insert default admin only if the users table is empty
        "INSERT INTO `users` (`nama`, `email`, `password`, `role`) 
         SELECT 'Admin', 'admin@sekolah.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin' 
         FROM (SELECT 1) AS `dummy` WHERE NOT EXISTS (SELECT * FROM `users`);"
    ];
    
    $all_queries_ok = true;
    foreach ($sql_queries as $query) {
        if ($conn->query($query) === TRUE) {
            $table_name = [];
            preg_match('/CREATE TABLE IF NOT EXISTS `(.*?)`/', $query, $table_name);
            if(isset($table_name[1])){
                 $messages[] = ['type' => 'success', 'text' => "Table '{$table_name[1]}' is OK."];
            }
        } else {
            // Check if the error is "Table already exists"
            if ($conn->errno != 1050) {
                 $messages[] = ['type' => 'danger', 'text' => "Error executing query: " . htmlspecialchars($conn->error)];
                 $all_queries_ok = false;
            }
        }
    }

    if ($all_queries_ok) {
        $messages[] = ['type' => 'success', 'text' => 'Default admin user is OK.'];
        $messages[] = ['type' => 'success', 'text' => '<strong>Database setup is complete!</strong> You can now go to the main page.'];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2>Database Setup Progress</h2>
            </div>
            <div class="card-body">
                <?php foreach ($messages as $message): ?>
                    <div class="alert alert-<?php echo $message['type']; ?>" role="alert">
                        <?php echo $message['text']; ?>
                    </div>
                <?php endforeach; ?>

                <?php if ($all_queries_ok): ?>
                <div class="d-grid gap-2">
                    <a href="index.php" class="btn btn-primary btn-lg">Go to Home Page</a>
                    <a href="login.php" class="btn btn-secondary">Go to Login Page</a>
                </div>
                <?php else: ?>
                <div class="alert alert-danger">
                    There were errors during setup. Please check your MySQL server settings (e.g., in XAMPP Control Panel) and try again.
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                 <p class="mb-0"><strong>Note:</strong> You can safely run this script multiple times. It will not duplicate data or tables.</p>
            </div>
        </div>
    </div>
</body>
</html>
