<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();        


}


use App\Core\Helpers\renderTablaHTML;


?>
<style>
  .notification-area {
    background-color: white;
    height: 50px;                /* Fija la altura */
    display: flex;               /* Flex para centrar contenido */
    align-items: center;         /* Centrado vertical */
    justify-content: center;     /* Centrado horizontal */
    border-bottom: 1px solid #ccc;  /* Opcional: línea separadora */
    color: #333;                 /* Color del texto */
    font-weight: bold;           /* Opcional: para destacar texto */
  }
</style>


<?php if ($cfg['tipo'] === 'table'){ ?>

<?php
} ?>    

<?php $this->layout('layout', ['title'=>$cfg['titulo'], 'subtitle'=>$cfg['subtitulo']]) ?>


<!-- contar la cantidad de botones y para establecer las columnas y poner una de lado a la otra -->


<div class="<?= $style['headtagclass'] ?>">

  <<?= $style['headtagtitulo'] ?>>
  
        <?= htmlspecialchars($cfg['titulo']) ?>
        
    </<?= $style['headtagtitulo'] ?>>
</div>
<div class="<?= $style['headtagclasssubt'] ?>">
    <<?= $style['headtagsubtitulo'] ?>>
        <?= htmlspecialchars($cfg['subtitulo']) ?>
    </<?= $style['headtagsubtitulo'] ?>>
</div>
<div class="notification-area">
<?php if (!empty($success = \App\Core\Session::flash('success'))): ?>
    <div style="color: green;"><?= $this->e($success) ?></div>
<?php 
    \App\Core\Session::flash('success', null); // Limpiar el mensaje de éxito después de mostrarlo}

endif; ?>

<?php if (!empty($error = \App\Core\Session::flash('error'))): ?>
    <div style="color: red;"><?= $this->e($error) ?></div>
<?php 
    \App\Core\Session::flash('error', null); // Limpiar el mensaje de éxito después de mostrarlo}

endif; ?>
</div>

<div class="<?= $style['class_div'] ?>"> 
  <div class="<?= $style['class_table_div'] ?>">
      <?php foreach ($comandos as $cmd): 
        $xurl = $cmd['url'];
        
          if( (isset($cmd['url_id'])) && $cmd['url_id'] == true){
            $xurl = $cmd['url'] . '/' . $_SESSION['user']['id'];
            }
        ?>
        <a href="<?= $xurl ?>" class="<?= $this->e($cmd['class']) ?>">
          <i class="<?= $this->e($cmd['icon']) ?>"></i>
          <?= $this->e($cmd['text']) ?>
        </a>
      <?php 
        endforeach; ?>
    </div>
  <div class="<?= $style['class_table_div'] ?>">
    <!-- aca poner filtros dinamicos-->
  </div>  
  <?php if ($cfg['tipo'] === 'table'): ?>

    <?php include 'tablesection.php'; ?>
    
    <?php elseif ($cfg['tipo'] === 'form'): ?>


    <?php include 'formsection.php'; ?>

<?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.pdf-viewer').forEach(container => {
    const url    = container.dataset.fileUrl;
    const canvas = container.querySelector('canvas');
    const ctx    = canvas.getContext('2d');

    pdfjsLib.getDocument(url).promise
      .then(pdf => pdf.getPage(1))
      .then(page => {
        const scale    = 1.2;
        const viewport = page.getViewport({ scale });
        canvas.width   = viewport.width;
        canvas.height  = viewport.height;
        return page.render({ canvasContext: ctx, viewport }).promise;
      })
      .catch(err => {
        console.error('Error cargando PDF:', err);
        container.innerHTML = '<p>No se pudo renderizar el PDF.</p>';
      });
  });
});
</script>


<?php


?>
