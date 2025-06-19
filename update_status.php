<?php
$conn = new mysqli("localhost", "tkbmyid_zaidan", "#Us3r_A1r_2025#", "tkbmyid_feeder");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$status = isset($_GET['status']) ? $_GET['status'] : '';
if ($status) {
    $conn->query("UPDATE feeder_data SET status_feeder='$status' ORDER BY id DESC LIMIT 1");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
