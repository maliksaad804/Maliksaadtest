<?php
// اگر video parameter دیا گیا ہے تو API request handle کرو
if (isset($_GET['video'])) {
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: application/json; charset=utf-8');

  $url = trim($_GET['video']);
  if (!$url) {
    echo json_encode(['error' => 'Missing video URL']);
    exit;
  }

  // Primary + Backup APIs
  $apis = [
    "https://all-downloader.itxkaal.workers.dev/?url=",
    "https://api.vevioz.com/api/button/?url="
  ];

  $result = null;

  foreach ($apis as $api) {
    $target = $api . urlencode($url);
    $ch = curl_init($target);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err || $code >= 400) continue;

    // اگر response میں کوئی http link ہو تو وہ واپس کر دو
    if (strpos($resp, 'http') !== false) {
      $result = $resp;
      break;
    }
  }

  if (!$result) {
    echo json_encode(['error' => 'All sources failed or invalid URL']);
    exit;
  }

  echo json_encode(['link' => $result]);
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>⚡ Kami Flex — All Media Downloader ⚡</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(120deg,#0f0b16,#1b0b18);
      color: white;
      display: flex; align-items: center; justify-content: center;
      height: 100vh; margin: 0; flex-direction: column;
    }
    .card {
      background: rgba(255,255,255,0.1);
      padding: 24px; border-radius: 16px;
      width: 90%; max-width: 420px; text-align: center;
      box-shadow: 0 6px 24px rgba(0,0,0,0.5);
    }
    input {
      width: 100%; padding: 12px; border-radius: 10px;
      border: none; margin: 8px 0; font-size: 16px;
    }
    button {
      background: linear-gradient(90deg,#ff2d55,#ff8a80);
      border: none; color: white; font-weight: 600;
      padding: 12px 16px; border-radius: 12px; cursor: pointer;
      width: 100%; margin-top: 8px;
    }
    #message { margin-top: 12px; font-size: 14px; }
    .tg-link { color: #00aced; text-decoration: none; font-weight: 600; }
  </style>
</head>
<body>
  <div class="card">
    <h2>⚡ Kami Flex Downloader ⚡</h2>
    <input type="url" id="videoUrl" placeholder="Paste video URL here..." />
    <button id="downloadBtn">Download</button>
    <div id="message"></div>
    <p style="margin-top:10px;">Join us on 
      <a href="https://t.me/Kami_Flex2" class="tg-link">Telegram</a>
    </p>
  </div>

  <script>
    document.getElementById('downloadBtn').addEventListener('click', async () => {
      const url = document.getElementById('videoUrl').value.trim();
      const msg = document.getElementById('message');
      msg.textContent = '';

      if (!url) {
        msg.textContent = '⚠️ Please paste a valid video link.';
        msg.style.color = '#ff8080';
        return;
      }

      msg.textContent = '⏳ Fetching download link...';
      msg.style.color = '#fff';

      try {
        const resp = await fetch(`?video=${encodeURIComponent(url)}`);
        const data = await resp.json();

        if (data.error) {
          msg.textContent = '❌ ' + data.error;
          msg.style.color = '#ff4d7e';
        } else if (data.link) {
          msg.innerHTML = `✅ <a href="${data.link}" target="_blank" style="color:#00ff99;">Download Now</a>`;
          msg.style.color = '#00ff99';
        } else {
          msg.textContent = '⚠️ Unexpected response.';
          msg.style.color = '#ffcc00';
        }
      } catch (e) {
        msg.textContent = '⚠️ Connection error. Try again.';
        msg.style.color = '#ff8080';
      }
    });
  </script>
</body>
</html>
