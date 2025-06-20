<?php
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');
echo json_encode(['current_time' => date('Y-m-d H:i:s')]);
?>
