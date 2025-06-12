<?php
$host = "localhost";
$user = "tkbmyid_zaidan"; // Ganti sesuai yang kamu buat
$pass = "#Us3r_A1r_2025#";
$db = "tkbmyid_feeder";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}
?>
