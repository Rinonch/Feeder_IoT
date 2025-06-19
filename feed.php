<?php
error_reporting(0);
ini_set('display_errors', 0);

$conn = new mysqli("localhost", "tkbmyid_zaidan", "#Us3r_A1r_2025#", "tkbmyid_feeder");
header('Content-Type: application/json');
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['success'=>false, 'message'=>'Koneksi gagal']);
    exit;
}
date_default_timezone_set('Asia/Jakarta');
$now = date('Y-m-d H:i:s');
$update = $conn->query("UPDATE feeder_status SET last_feed='$now'");

if ($update) {
    // Fetch the updated last_feed from database to ensure accuracy
    $result = $conn->query("SELECT last_feed FROM feeder_status LIMIT 1");
    if ($result) {
        $row = $result->fetch_assoc();
        $last_feed = $row['last_feed'];
        echo json_encode(['success' => true, 'last_feed' => date('d/m/Y H:i:s', strtotime($last_feed))]);
    } else {
        echo json_encode(['success' => true, 'last_feed' => date('d/m/Y H:i:s', strtotime($now))]);
    }
} else {
    error_log("Database update failed");
    echo json_encode(['success' => false, 'message' => 'Gagal update']);
}
?>
