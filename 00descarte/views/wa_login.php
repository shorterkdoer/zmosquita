<!DOCTYPE html>
<html>
<head><title>Login vía WhatsApp</title></head>
<body>
  <h1>Escanea con tu WhatsApp</h1>
  <img id="qr" src="<?= $qrData ?>" alt="QR WhatsApp" />
  <script>
    // Cada 2s comprobamos si ya se autenticó
    setInterval(async ()=>{
      const res = await fetch('/wa-status');
      const { authenticated } = await res.json();
      if (authenticated) window.location = '/wa-callback';
    }, 2000);
  </script>
</body>
</html>
