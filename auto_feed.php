<?php
$conn = new mysqli("localhost", "tkbmyid_zaidan", "#Us3r_A1r_2025#", "tkbmyid_feeder");
header('Content-Type: application/json');
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['success' => false, 'message' => 'Koneksi gagal']);
    exit;
}

// Get current time in HH:MM format
date_default_timezone_set('Asia/Jakarta');
$current_time = date('H:i:s'); // include seconds to match database format

// Get feeding schedule
$result = $conn->query("SELECT jadwal_pagi, jadwal_sore FROM feeder_status LIMIT 1");
if (!$result) {
    error_log("Failed to fetch schedule");
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil jadwal']);
    exit;
}
$row = $result->fetch_assoc();
$jadwal_pagi = $row['jadwal_pagi'];
$jadwal_sore = $row['jadwal_sore'];

// To avoid multiple triggers within the same second, store last trigger time
$last_trigger_file = __DIR__ . '/last_trigger_time.txt';
$last_trigger_time = file_exists($last_trigger_file) ? file_get_contents($last_trigger_file) : '';

$format_current_time = DateTime::createFromFormat('H:i:s', $current_time);
$format_jadwal_pagi = DateTime::createFromFormat('H:i:s', $jadwal_pagi);
$format_jadwal_sore = DateTime::createFromFormat('H:i:s', $jadwal_sore);

if ($format_current_time && ($format_current_time->format('H:i') === $format_jadwal_pagi->format('H:i') || $format_current_time->format('H:i') === $format_jadwal_sore->format('H:i'))) {
    if ($last_trigger_time !== $current_time) {
        // Trigger feeding by calling feed.php internally
        $feed_response = file_get_contents('http://feeder.tk2b.my.id/feed.php');
        if ($feed_response === false) {
            error_log("Failed to call feed.php");
            echo json_encode(['success' => false, 'message' => 'Gagal memanggil feed.php']);
            exit;
        }
        $feed_data = json_decode($feed_response, true);
        if ($feed_data && $feed_data['success']) {
            // Update last trigger time
            file_put_contents($last_trigger_file, $current_time);
            echo json_encode(['success' => true, 'message' => 'Feeding triggered']);
        } else {
            error_log("Feeding failed: " . $feed_response);
            echo json_encode(['success' => false, 'message' => 'Feeding gagal']);
        }
    } else {
        echo json_encode(['success' => true, 'message' => 'Feeding already triggered for this time']);
    }
} else {
    echo json_encode(['success' => true, 'message' => 'No feeding scheduled at this time']);
}
?>
