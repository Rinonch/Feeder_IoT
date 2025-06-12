<?php
$conn = new mysqli("localhost", "tkbmyid_zaidan", "#Us3r_A1r_2025#", "tkbmyid_feeder");
header('Content-Type: application/json');
if ($conn->connect_error) {
    echo json_encode(['success'=>false, 'message'=>'Koneksi gagal']);
    exit;
}
$pagi = $_GET['pagi'] ?? '';
$sore = $_GET['sore'] ?? '';
if (!$pagi || !$sore) {
    echo json_encode(['success'=>false, 'message'=>'Data tidak lengkap']);
    exit;
}
$update = $conn->query("UPDATE feeder_status SET jadwal_pagi='$pagi', jadwal_sore='$sore' LIMIT 1");
if ($update) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'message'=>'Gagal update']);
} // tutup else
?>