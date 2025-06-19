// JavaScript to publish MQTT "feed" command via HiveMQ WebSocket from browser

// Include MQTT.js library via CDN in your HTML:
// <script src="https://unpkg.com/mqtt/dist/mqtt.min.js"></script>

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
