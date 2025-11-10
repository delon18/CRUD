<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "apiphp2"; // ganti sesuai database kamu

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(["message" => "Koneksi gagal: " . $conn->connect_error]));
}

header("Content-Type: application/json");
?>
