<?php
// Koneksi ke database
$conn = new mysqli("localhost", "tkbmyid_zaidan", "#Us3r_A1r_2025#", "tkbmyid_feeder");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

// Ambil data feeder
$result = $conn->query("SELECT * FROM feeder_status LIMIT 1");
$data = $result->fetch_assoc();
$pagi = substr($data['jadwal_pagi'], 0, 5); // Format HH:MM
$sore = substr($data['jadwal_sore'], 0, 5);
$lastFeed = $data['last_feed'] ? date("d/m/Y H:i:s", strtotime($data['last_feed'])) : "Belum pernah memberi makan";
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Smart Feeder Ikan</title>
  <link rel="icon" type="image/x-icon" href="./favicon/favicon.ico">
  <style>
    body {
      font-family: 'Comic Neue', cursive;
      background: linear-gradient(to bottom, #a0e9ff, #0077be);
      margin: 0; padding: 0;
      overflow-x: hidden;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      min-height: 100vh;
      padding-top: 30px;
      color: #004d40;
    }
    .container {
      max-width: 420px;
      background: rgba(255, 255, 255, 0.95);
      padding: 25px;
      border-radius: 20px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      text-align: center;
      user-select: none;
    }
    h1 {
      margin-bottom: 20px;
      color: #00796b;
      font-size: 26px;
      text-shadow: 1px 1px 2px #004d40;
    }
    .status, .time-box, .label-section {
      font-size: 1.1rem;
      margin: 10px 0;
      color: #333;
    }
    .time-box {
      background: #d1faff;
      padding: 15px;
      border-radius: 12px;
      color: #004d40;
      box-shadow: inset 0 0 8px #00796b;
    }
    button {
      padding: 12px 24px;
      font-size: 1rem;
      background: #ff9800;
      color: white;
      border: none;
      border-radius: 12px;
      margin: 10px 8px 10px 8px;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      min-width: 140px;
    }
    button:hover:not(:disabled) {
      background: #ef6c00;
      transform: scale(1.05);
    }
    button:disabled {
      background: #ccc;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
      color: #666;
    }
    .btn-danger {
      background: #f44336;
    }
    .btn-danger:hover:not(:disabled) {
      background: #c62828;
    }
    input[type="time"] {
      padding: 10px;
      font-size: 1rem;
      border-radius: 10px;
      border: 1px solid #ccc;
      margin: 5px;
      width: 130px;
      transition: border-color 0.3s ease;
    }
    input[type="time"]:focus {
      border-color: #00796b;
      outline: none;
      box-shadow: 0 0 5px #00796b;
    }
    label {
      display: block;
      margin: 8px 0 0 0;
      color: #00796b;
      font-weight: bold;
      user-select: none;
    }
    #toast {
      visibility: hidden;
      min-width: 250px;
      margin-left: -125px;
      background-color: #00796b;
      color: #fff;
      text-align: center;
      border-radius: 25px;
      padding: 15px;
      position: fixed;
      z-index: 1;
      left: 50%;
      bottom: 30px;
      font-size: 1rem;
      box-shadow: 0 4px 8px rgba(0,0,0,0.3);
      transition: visibility 0s linear 0.3s, opacity 0.3s ease-in-out;
      opacity: 0;
      user-select: none;
    }
    #toast.show {
      visibility: visible;
      opacity: 1;
      transition-delay: 0s;
    }
    .last-feed {
      margin-top: 10px;
      font-size: 1rem;
      color: #00695c;
      font-style: italic;
      user-select: none;
    }
  </style>
</head>
<body>
<div class="container">
  <h1>🐟 Smart Feeder Ikan</h1>

  <div class="time-box">
    <div><strong>Hari/Tanggal:</strong> <span id="date">-</span></div>
    <div><strong>Waktu:</strong> <span id="time">-</span></div>
  </div>

  <div class="status">
    <strong>Stok Pakan:</strong> <span id="stok">Tersedia</span>
  </div>

  <div class="status">
    <strong>Status Feeder:</strong> <span id="status">-</span><br>
    <strong>Level Pakan:</strong> <span id="level">-</span><br>
    <strong>Waktu Sensor:</strong> <span id="waktu">-</span>
  </div>
  <div class="status">
    <strong>Jadwal Pagi:</strong> <span id="jadwalPagi"><?= htmlspecialchars($pagi) ?></span><br>
    <strong>Jadwal Sore:</strong> <span id="jadwalSore"><?= $sore ?></span>
  </div>

  <button id="btnFeed">🍽 Kasih Makan Sekarang</button>

  <div class="last-feed" id="lastFeedInfo">Terakhir memberi makan: <?= $lastFeed ?></div>

  <div class="label-section">⚙ Atur Jadwal</div>

  <label>Pagi: 
    <input type="time" id="inputPagi" value="<?= $pagi ?>" />
  </label>
  <label>Sore: 
    <input type="time" id="inputSore" value="<?= $sore ?>" />
  </label>

  <button id="btnSave" class="btn-danger">💾 Simpan Jadwal</button>
  <button id="btnReset">♻ Reset Jadwal</button>

  <div id="toast"></div>
</div>

<script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
<script>
  const dateElem = document.getElementById('date');
  const timeElem = document.getElementById('time');
  const btnFeed = document.getElementById('btnFeed');
  const lastFeedInfo = document.getElementById('lastFeedInfo');
  const inputPagi = document.getElementById('inputPagi');
  const inputSore = document.getElementById('inputSore');
  const btnSave = document.getElementById('btnSave');
  const btnReset = document.getElementById('btnReset');
  const jadwalPagiElem = document.getElementById('jadwalPagi');
  const jadwalSoreElem = document.getElementById('jadwalSore');
  const toast = document.getElementById('toast');

  function showToast(message) {
    toast.textContent = message;
    toast.className = 'show';
    setTimeout(() => {
      toast.className = toast.className.replace('show', '');
    }, 3000);
  }

  function updateDateTime() {
    const now = new Date();
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    dateElem.textContent = days[now.getDay()] + ', ' + now.getDate() + '/' + (now.getMonth()+1) + '/' + now.getFullYear();
    timeElem.textContent = now.toLocaleTimeString();
  }

  btnFeed.onclick = async () => {
    btnFeed.disabled = true;
    btnFeed.textContent = '⏳ Memberi makan...';
    try {
      const res = await fetch('feed.php');
      const data = await res.json();
      if (data.success) {
        lastFeedInfo.textContent = `Terakhir memberi makan: ${data.last_feed}`;
        showToast('Pakan diberikan!');
      } else {
        showToast(data.message || 'Gagal memberi makan');
      }
    } catch {
      showToast('Gagal memberi makan');
    }
    btnFeed.textContent = '🍽 Kasih Makan Sekarang';
    btnFeed.disabled = false;
  };

  btnSave.onclick = async () => {
    const pagi = inputPagi.value;
    const sore = inputSore.value;
    if (!pagi || !sore) {
      showToast('Harap isi jadwal pagi dan sore!');
      return;
    }
    if (pagi >= sore) {
      showToast('Jadwal pagi harus sebelum jadwal sore!');
      return;
    }
    btnSave.disabled = true;
    btnSave.textContent = '💾 Menyimpan...';
    try {
      const res = await fetch(`setjadwal.php?pagi=${pagi}&sore=${sore}`);
      const data = await res.json();
      if (data.success) {
        jadwalPagiElem.textContent = pagi;
        jadwalSoreElem.textContent = sore;
        showToast('Jadwal disimpan!');
      } else {
        showToast(data.message || 'Gagal menyimpan jadwal');
      }
    } catch {
      showToast('Gagal menyimpan jadwal');
    }
    btnSave.textContent = '💾 Simpan Jadwal';
    btnSave.disabled = false;
  };

  btnReset.onclick = () => {
    inputPagi.value = '';
    inputSore.value = '';
    showToast('Input jadwal direset');
  };

  const options = {
    username: "feeder",
    password: "Feeder123",
    protocol: "wss",
    port: 8884,
    clientId: "web-client-" + Math.random().toString(16).substr(2, 8),
    connectTimeout: 4000,
    clean: true
  };

  const client = mqtt.connect("wss://a0217047345f4f5bb0814a9c88af029b.s1.eu.hivemq.cloud:8884/mqtt", options);

  client.on("connect", () => {
    console.log("Connected to HiveMQ");
    client.subscribe("feeder/status", err => {
      if (!err) console.log("Subscribed to topic");
    });
  });

  client.on("message", (topic, message) => {
    const data = JSON.parse(message.toString());
    document.getElementById("status").innerText = data.status_feeder;
    document.getElementById("level").innerText = data.level_pakan + "%";
    document.getElementById("waktu").innerText = data.waktu;

    const levelElem = document.getElementById("level");
    levelElem.innerText = data.level_pakan + "%";
    levelElem.style.color = data.level_pakan < 30 ? "red" : "#00695c";

    // Tambahkan baris berikut untuk update waktu terakhir memberi makan secara real-time
    if (data.last_feed) {
      document.getElementById("lastFeedInfo").innerText = "Terakhir memberi makan: " + data.last_feed;
    }
  });

  client.on("close", () => {
    showToast("Koneksi ke MQTT terputus!");
  });
  client.on("reconnect", () => {
    showToast("Menyambungkan ulang ke MQTT...");
  });

  updateDateTime();
  setInterval(updateDateTime, 1000);
</script>
</body>
</html>
