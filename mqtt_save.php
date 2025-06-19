<?php
// Koneksi ke database
$conn = new mysqli("localhost", "tkbmyid_zaidan", "#Us3r_A1r_2025#", "tkbmyid_feeder");
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Koneksi database gagal']));
}

// Ambil data JSON dari POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

// Ambil field yang diperlukan
$status_feeder = isset($data['status_feeder']) ? $conn->real_escape_string($data['status_feeder']) : '';
$level_pakan    = isset($data['level_pakan']) ? intval($data['level_pakan']) : 0;
$waktu         = isset($data['waktu']) ? $conn->real_escape_string($data['waktu']) : '';
$last_feed     = isset($data['last_feed']) ? $conn->real_escape_string($data['last_feed']) : '';

// Update data di tabel feeder_status (atau feeder_data jika sesuai)
$sql = "UPDATE feeder_status SET 
    status_feeder='$status_feeder',
    level_pakan=$level_pakan,
    waktu='$waktu',
    last_feed='$last_feed'
    LIMIT 1";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>