/**
 * JavaScript to poll the auto_feed.php endpoint periodically
 * and update the UI with the latest auto feed status.
 */

const autoFeedStatusElem = document.createElement('div');
autoFeedStatusElem.id = 'autoFeedStatus';
autoFeedStatusElem.style.marginTop = '10px';
autoFeedStatusElem.style.fontSize = '0.9rem';
autoFeedStatusElem.style.color = '#00796b';
document.querySelector('.container').appendChild(autoFeedStatusElem);

async function pollAutoFeedStatus() {
  try {
    const response = await fetch('/auto_feed.php');
    if (!response.ok) throw new Error('Network response was not ok');
    const data = await response.json();
    if (data.success) {
      autoFeedStatusElem.textContent = `Auto Feed Status: ${data.message}`;
      console.log(`Auto Feed Status: ${data.message}`);
    } else {
      autoFeedStatusElem.textContent = `Auto Feed Error: ${data.message}`;
      console.error(`Auto Feed Error: ${data.message}`);
    }
  } catch (error) {
    autoFeedStatusElem.textContent = 'Auto Feed: Error connecting to server';
    console.error('Auto Feed polling error:', error);
  }
}

// Poll every 60 seconds
setInterval(pollAutoFeedStatus, 60000);

// Initial poll on page load
pollAutoFeedStatus();
