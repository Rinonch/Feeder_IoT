<?php
include 'koneksi.php';
header('Content-Type: application/json');
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['success' => false, 'message' => 'Koneksi gagal']);
    exit;
}

// Get current time in HH:MM format
date_default_timezone_set('Asia/Jakarta');
$current_time = date('H:i'); // remove seconds to match database format

// Get feeding schedule from new table
$result = $conn->query("SELECT jadwal_pagi, jadwal_sore FROM auto_feed_schedule WHERE status = 1 ORDER BY id DESC LIMIT 1");
if (!$result) {
    error_log("Failed to fetch schedule");
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil jadwal']);
    exit;
}
$row = $result->fetch_assoc();
$jadwal_pagi = substr($row['jadwal_pagi'], 0, 5); // remove seconds
$jadwal_sore = substr($row['jadwal_sore'], 0, 5); // remove seconds

$last_trigger_schedule = file_exists(__DIR__ . '/last_trigger_schedule.txt') ? file_get_contents(__DIR__ . '/last_trigger_schedule.txt') : '';

$format_current_time = DateTime::createFromFormat('H:i', $current_time);
$format_jadwal_pagi = DateTime::createFromFormat('H:i', $jadwal_pagi);
$format_jadwal_sore = DateTime::createFromFormat('H:i', $jadwal_sore);

if ($format_current_time && $format_jadwal_pagi && $format_jadwal_sore) {
    $triggered = false;

    if ($format_current_time->format('H:i') === $format_jadwal_pagi->format('H:i') && $last_trigger_schedule !== $jadwal_pagi) {
        $triggered = $jadwal_pagi;
    } elseif ($format_current_time->format('H:i') === $format_jadwal_sore->format('H:i') && $last_trigger_schedule !== $jadwal_sore) {
        $triggered = $jadwal_sore;
    }

    if ($triggered !== false) {
        // Trigger feeding by calling feed.php internally
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://feeder.tk2b.my.id/feed.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $feed_response = curl_exec($ch);
        if ($feed_response === false) {
            error_log("Failed to call feed.php: " . curl_error($ch));
            echo json_encode(['success' => false, 'message' => 'Gagal memanggil feed.php']);
            curl_close($ch);
            exit;
        }
        curl_close($ch);
        $feed_data = json_decode($feed_response, true);
        if ($feed_data && $feed_data['success']) {
            // Update last triggered schedule only
            file_put_contents(__DIR__ . '/last_trigger_schedule.txt', $triggered);
            echo json_encode(['success' => true, 'message' => 'Feeding triggered']);
        } else {
            error_log("Feeding failed: " . $feed_response);
            echo json_encode(['success' => false, 'message' => 'Feeding gagal']);
        }
    } else {
        echo json_encode(['success' => true, 'message' => 'No feeding scheduled at this time']);
    }
} else {
    echo json_encode(['success' => true, 'message' => 'No feeding scheduled at this time']);
}
?>
