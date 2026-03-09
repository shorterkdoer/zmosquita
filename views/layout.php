<!DOCTYPE html>
<html lang="es">
<?php
session_start();
include_once 'head.inc.php';
// Enable error reporting for development
?>

<?php
use App\Core\Session;

$user = Session::get('user');
?>
<nav>
    <?php if ($user): ?>
   
        <strong>Hola! <?php echo "Usuario: " . $user['email']; ?></strong> |
        <a href="/logout">Salir</a>
    <?php else: ?>
        
    <?php endif; ?>
</nav>
<body class="container" style="padding-bottom: 70px;">
<header class="bg-light py-3 border-bottom">
  <div class="container d-flex align-items-center">
    <!-- Logo -->
    <a href="/"><img src="/logo.png" alt="Logo" style="height: 60px; width: auto;" class="me-3"> </a>

    <!-- Textos -->
    <div>
      <h4 class="mb-0"><?php echo Session::get('Title', ''); ?></h4>
      <small class="text-muted"><?php echo Session::get('Subtitle', ''); ?></small>
    </div>
    </div>
    <div>
    <nav class="bg-white border-top">
        <div class="container">
            <?php 
            include_once 'menu.inc.php';
            ?>
        </div>
     </nav>

    </div>
</header>

<?php
use ParagonIE\AntiCSRF\AntiCSRF;

$csrf = new AntiCSRF();
$csrf->insertToken();
?>
    <main>
        <?= $this->section('content') ?>
    </main>

    <footer class="navbar navbar-fixed-bottom">
    
        <p>© <?= date('Y') ?> - <a href="https://www.medanodigital.net">Médano Digital</a></p>
    </footer>
    <script>
document.addEventListener('DOMContentLoaded', function () {
  const forms = document.querySelectorAll('form');

  forms.forEach(form => {
    form.addEventListener('submit', function (e) {
      const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');

      if (submitButton) {
        // Desactivamos el botón y cambiamos el texto
        submitButton.disabled = true;

        // Opcional: cambiamos el texto visual para feedback
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Enviando...';

        // Por si ocurre un error, lo reactivamos a los 10s (opcional)
        setTimeout(() => {
          submitButton.disabled = false;
          submitButton.innerHTML = originalText;
        }, 10000);
      }
    });
  });
});
</script>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

<?php
require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/messages.php';
renderFlashMessage(); 
resetFlashMessage();
?>
</body>
</html>




