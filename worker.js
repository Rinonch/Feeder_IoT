// worker.js - Node.js MQTT Worker untuk auto feed
const express = require('express');
const mqtt = require('mqtt');
const app = express();
app.use(express.json());

const MQTT_URL = 'mqtts://a0217047345f4f5bb0814a9c88af029b.s1.eu.hivemq.cloud:8883';
const MQTT_USER = 'feeder';
const MQTT_PASS = 'Feeder123';
const MQTT_TOPIC = 'feeder/command';
const SECRET = 'Feeder123'; // Ganti dengan secret yang sama di auto_feed.php

app.post('/feed', (req, res) => {
  if (!req.body || req.body.secret !== SECRET) {
    return res.status(403).json({ success: false, message: 'Unauthorized' });
  }
  const client = mqtt.connect(MQTT_URL, {
    username: MQTT_USER,
    password: MQTT_PASS,
    protocolVersion: 4,
    rejectUnauthorized: false // Set false jika tidak ingin validasi CA
  });

  client.on('connect', () => {
    client.publish(MQTT_TOPIC, 'feed', { qos: 0 }, (err) => {
      client.end();
      if (err) {
        console.error('MQTT publish error:', err);
        return res.status(500).json({ success: false, message: 'MQTT publish error', error: err.toString() });
      }
      console.log('Feed command sent to MQTT');
      res.json({ success: true, message: 'Feed command sent to MQTT' });
    });
  });

  client.on('error', (err) => {
    client.end();
    console.error('MQTT connection error:', err);
    res.status(500).json({ success: false, message: 'MQTT connection error', error: err.toString() });
  });
});

app.listen(5000, () => console.log('MQTT Worker listening on port 5000'));