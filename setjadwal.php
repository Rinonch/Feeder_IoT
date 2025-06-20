<?php
include 'koneksi.php';
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

// Check if a schedule exists
$result = $conn->query("SELECT id FROM auto_feed_schedule LIMIT 1");
if ($result && $result->num_rows > 0) {
    // Update existing schedule
    $row = $result->fetch_assoc();
    $id = $row['id'];
    $update = $conn->query("UPDATE auto_feed_schedule SET jadwal_pagi='$pagi', jadwal_sore='$sore', updated_at=NOW() WHERE id=$id");
} else {
    // Insert new schedule
    $update = $conn->query("INSERT INTO auto_feed_schedule (jadwal_pagi, jadwal_sore) VALUES ('$pagi', '$sore')");
}

if ($update) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'message'=>'Gagal update']);
}
?>
