z<?php
include 'koneksi.php'; // pastikan file koneksi.php sudah benar

// Ambil data dari tabel feeder_status
$q = $conn->query("SELECT * FROM feeder_status LIMIT 1");
$data = $q->fetch_assoc();

$status_feeder = $data['status_feeder'] ?? '-';
$jadwal_pagi = $data['jadwal_pagi'] ?? '-';
$jadwal_sore = $data['jadwal_sore'] ?? '-';
$lastFeed = $data['last_feed'] ?? '-';
$pagi = $data['jadwal_pagi'] ?? '';
$sore = $data['jadwal_sore'] ?? '';
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
    <strong>Status Feeder:</strong> <span id="status"><?= htmlspecialchars($status_feeder) ?></span>
  </div>
  <div class="status">
    <strong>Jadwal Pagi:</strong> <span id="jadwalPagi"><?= htmlspecialchars($jadwal_pagi) ?></span><br>
    <strong>Jadwal Sore:</strong> <span id="jadwalSore"><?= htmlspecialchars($jadwal_sore) ?></span>
  </div>

  <button id="btnFeed">🍽 Kasih Makan Sekarang</button>
  <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>
  <script src="/public/autoFeed.js"></script>
  <script>
    function publishFeedCommand() {
      const client = mqtt.connect('wss://a0217047345f4f5bb0814a9c88af029b.s1.eu.hivemq.cloud:8884/mqtt', {
        username: 'feeder',
        password: 'Feeder123',
        protocolVersion: 4,
        clean: true,
        reconnectPeriod: 1000,
      });

      client.on('connect', function () {
        console.log('Connected to HiveMQ WebSocket');
        client.publish('feeder/command', 'feed', { qos: 0 }, function (err) {
          if (err) {
            console.error('Publish error:', err);
          } else {
            console.log('Feed command published');
          }
          client.end();
        });
      });

      client.on('error', function (err) {
        console.error('Connection error:', err);
        client.end();
      });
    }

    document.getElementById('btnFeed').addEventListener('click', function () {
      publishFeedCommand();
    });
  </script>

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

  <div style="margin-top:15px;font-size:0.9em;color:#888;">Smart Feeder Ikan v1.0</div>
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
    fetch('current_time.php')
      .then(response => response.json())
      .then(data => {
        let serverTime = new Date(data.current_time);
        // Adjust time to WIB (UTC+7) with 24-hour wrap-around
        let adjustedHour = serverTime.getUTCHours() + 7;
        if (adjustedHour >= 24) adjustedHour -= 24;
        serverTime.setUTCHours(adjustedHour);

        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        dateElem.textContent = days[serverTime.getUTCDay()] + ', ' + serverTime.getUTCDate() + '/' + (serverTime.getUTCMonth() + 1) + '/' + serverTime.getUTCFullYear();
        // Format time as 24-hour with leading zeros
        const hours = adjustedHour.toString().padStart(2, '0');
        const minutes = serverTime.getUTCMinutes().toString().padStart(2, '0');
        const seconds = serverTime.getUTCSeconds().toString().padStart(2, '0');
        timeElem.textContent = `${hours}:${minutes}:${seconds}`;

        // Update last feed time in real-time if available
        const lastFeedElem = document.getElementById('lastFeedInfo');
        if (lastFeedElem) {
          const lastFeedText = lastFeedElem.textContent;
          const match = lastFeedText.match(/(\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2})/);
          if (match) {
            // Parse date string as UTC and convert to WIB (UTC+7)
            const utcDateStr = match[1].replace(/(\d{2})\/(\d{2})\/(\d{4})/, '$3-$2-$1');
            const lastFeedDateUTC = new Date(utcDateStr + 'T00:00:00Z');
            if (!isNaN(lastFeedDateUTC)) {
              // Add 7 hours for WIB timezone
              lastFeedDateUTC.setHours(lastFeedDateUTC.getHours() + 7);
              // Increment last feed time by 1 second
              lastFeedDateUTC.setSeconds(lastFeedDateUTC.getSeconds() + 1);
              const formatted = lastFeedDateUTC.toLocaleString('id-ID', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
              });
              lastFeedElem.textContent = 'Terakhir memberi makan: ' + formatted;
            }
          }
        }
      })
      .catch(err => {
        console.error('Failed to fetch server time:', err);
      });
  }

  btnFeed.onclick = async () => {
    btnFeed.disabled = true;
    btnFeed.textContent = '⏳ Memberi makan...';
    try {
      const res = await fetch('feed.php');
      if (!res.ok) throw new Error('Network response was not ok');
      const data = await res.json();
      if (data.success) {
        // Update last feed info if element exists
        const lastFeedElem = document.getElementById('lastFeedInfo');
        if (lastFeedElem && data.last_feed) {
          lastFeedElem.textContent = `Terakhir memberi makan: ${data.last_feed}`;
        }
        showToast('Pakan diberikan!');
      } else {
        showToast(data.message || 'Gagal memberi makan');
      }
    } catch (error) {
      console.error('Fetch error:', error);
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
    if (confirm('Yakin ingin mereset jadwal?')) {
      inputPagi.value = '';
      inputSore.value = '';
      showToast('Input jadwal direset');
    }
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
    document.getElementById("status").innerText = data.status_feeder === "ONLINE" ? "ONLINE" : "OFFLINE";

    // Update status stok pakan
    const stokElem = document.getElementById("stok");
    if (data.level_pakan == 0) {
      stokElem.innerText = "Habis";
      stokElem.style.color = "red";
      showToast("Stok pakan habis!");
    } else if (data.level_pakan < 30) {
      stokElem.innerText = "Hampir Habis";
      stokElem.style.color = "red";
      showToast("Stok pakan hampir habis!");
    } else {
      stokElem.innerText = "Tersedia";
      stokElem.style.color = "#00695c";
    }

    // Update waktu terakhir memberi makan (real-time, WIB)
    if (data.last_feed) {
      const lastFeedElem = document.getElementById("lastFeedInfo");
      if (lastFeedElem) {
        // Cek apakah format last_feed valid
        if (data.last_feed.includes(' ')) {
          const [tgl, jam] = data.last_feed.split(' ');
          const [year, month, day] = tgl.includes('-') ? tgl.split('-') : tgl.split('/');
          if (jam) {
            const [hour, minute, second] = jam.split(':');
            const dateObj = new Date(year, month - 1, day, hour, minute, second);
            const formatted = dateObj.toLocaleString('id-ID', {
              day: '2-digit', month: '2-digit', year: 'numeric',
              hour: '2-digit', minute: '2-digit', second: '2-digit'
            });
            lastFeedElem.innerText = "Terakhir memberi makan: " + formatted;
          } else {
            lastFeedElem.innerText = "Terakhir memberi makan: " + data.last_feed;
          }
        } else {
          lastFeedElem.innerText = "Terakhir memberi makan: " + data.last_feed;
        }
      }
    }

    // Removed feedProgress update to avoid error as element is not present
    // document.getElementById("feedProgress").value = data.level_pakan;
    fetch('update_status.php?status=ONLINE');
  });

  // Set status_feeder OFFLINE jika tidak ada pesan dalam 10 detik
  let feederTimeout;
  function setFeederOffline() {
    document.getElementById("status").innerText = "OFFLINE";
    fetch('update_status.php?status=OFFLINE');
  }
  client.on("message", () => {
    clearTimeout(feederTimeout);
    feederTimeout = setTimeout(setFeederOffline, 10000);
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
