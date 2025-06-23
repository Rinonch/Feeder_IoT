<?php
include 'koneksi.php';
require_once 'phpMQTT.php';
header('Content-Type: application/json');
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(['success' => false, 'message' => 'Koneksi gagal']);
    exit;
}

date_default_timezone_set('Asia/Jakarta');
$current_time = date('H:i');
$today = date('Y-m-d');

// Ambil jadwal aktif
$result = $conn->query("SELECT jadwal_pagi, jadwal_sore FROM auto_feed_schedule WHERE status = 1 ORDER BY id DESC LIMIT 1");
if (!$result) {
    error_log("Failed to fetch schedule");
    echo json_encode(['success' => false, 'message' => 'Gagal mengambil jadwal']);
    exit;
}
$row = $result->fetch_assoc();
$jadwal_pagi = substr($row['jadwal_pagi'], 0, 5);
$jadwal_sore = substr($row['jadwal_sore'], 0, 5);

// File log harian
$log_file = __DIR__ . '/auto_feed_log.txt';
if (!file_exists($log_file)) file_put_contents($log_file, '');
$log = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

function sudah_feed($waktu, $today, $log) {
    return in_array("$today-$waktu", $log);
}

$triggered = false;
$feed_time = null;

// Validasi jadwal tidak kosong/null
// Perbaikan: izinkan jam 00:00 sebagai waktu valid
if ($jadwal_pagi !== null && $jadwal_pagi !== '' && $current_time === $jadwal_pagi && !sudah_feed($jadwal_pagi, $today, $log)) {
    $triggered = true;
    $feed_time = $jadwal_pagi;
} elseif ($jadwal_sore !== null && $jadwal_sore !== '' && $current_time === $jadwal_sore && !sudah_feed($jadwal_sore, $today, $log)) {
    $triggered = true;
    $feed_time = $jadwal_sore;
}

if ($triggered && $feed_time) {
    $worker_url = 'https://ea56-103-18-35-68.ngrok-free.app/feed'; // Ganti dengan IP/domain worker Anda
    $ch = curl_init($worker_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['secret'=>'Feeder123']));
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpcode == 200) {
        file_put_contents($log_file, "$today-$feed_time\n", FILE_APPEND);
        echo json_encode(['success' => true, 'message' => "Feeding triggered at $feed_time (via worker)"]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to trigger worker', 'worker_response' => $result]);
    }
} else {
    echo json_encode(['success' => true, 'message' => 'No feeding scheduled at this time']);
}
?>
