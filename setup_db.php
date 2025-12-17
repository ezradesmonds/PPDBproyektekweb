<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ppdb_sekolah";

// Create connection
$conn = new mysqli($servername, $username, $password);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
  echo "Database created successfully\n";
} else {
  echo "Error creating database: " . $conn->error . "\n";
}

$conn->close();

// Connect to the new database
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql_file = 'init.sql';
$sql_contents = file_get_contents($sql_file);

// Remove the database creation part from the sql file
$sql_contents = preg_replace('/CREATE DATABASE IF NOT EXISTS ppdb_sekolah;/', '', $sql_contents);
$sql_contents = preg_replace('/USE ppdb_sekolah;/', '', $sql_contents);


if ($conn->multi_query($sql_contents)) {
    echo "SQL script executed successfully!\n";
} else {
    echo "Error executing SQL script: " . $conn->error . "\n";
}

while ($conn->next_result()) {
  if (!$conn->more_results()) break;
}


$conn->close();

echo "Database setup complete.\n";

?>
