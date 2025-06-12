<?php
$conn = new mysqli("localhost", "tkbmyid_zaidan", "#Us3r_A1r_2025#", "tkbmyid_feeder");
header('Content-Type: application/json');
if ($conn->connect_error) {
    echo json_encode(['success'=>false, 'message'=>'Koneksi gagal']);
    exit;
}
$now = date('Y-m-d H:i:s');
$update = $conn->query("UPDATE feeder_status SET last_feed='$now'");
if ($update) {
    echo json_encode(['success'=>true, 'last_feed'=>date('d/m/Y H:i:s', strtotime($now))]);
} else {
    echo json_encode(['success'=>false, 'message'=>'Gagal update']);
}
?>