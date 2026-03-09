<?php
// views/dashboard/mylandingpage.php
$this->layout('layout', [
    'title'    => $cfgHeader['titulo']   ?? 'Sin título',
    'subtitle' => $cfgHeader['subtitulo'] ?? '',
]);

?>

<div class="<?= $cfgHeader['headtagclass'] ?>">
    <<?= $cfgHeader['headtagtitulo'] ?>>
        <?= htmlspecialchars($cfgHeader['titulo']) ?>
    </<?= $cfgHeader['headtagtitulo'] ?>>

    <<?= $cfgHeader['headtagsubtitulo'] ?> class="<?= $cfgHeader['headtagclasssubt'] ?>">
        <?= htmlspecialchars($cfgHeader['subtitulo']) ?>
    </<?= $cfgHeader['headtagsubtitulo'] ?>>
</div>

<div class="<?= $estilos['outerclass'] ?>">
    <div class="<?= $estilos['innerclass'] ?>">
        <?php foreach ($buttons as $btn): 
            // Construir URL, añadiendo el ID cuando url_id = true
            $url = $btn['link'] . ($btn['url_id'] ? '/'.urlencode($userId) : '');
        ?>


            <div class="<?= $estilos['colclass'] ?>">
                <a href="<?= htmlspecialchars($url) ?>"
                    <?php
                        if (!empty($btn['target'])) { ?>
                            target=" <?= $btn['target'] ?>" 
                        <?php  
                        }
                        ?>
                   class="<?= $estilos['btnclass'] ?>">
                    <i class="<?= $estilos['linkiconclass']. ' ' .$btn['icon'] ?>"
                       style="<?= $estilos['linkiconstyle'] ?>"></i>
                    <strong class="<?= $estilos['linkstrongclass'] ?>"
                            style="<?= $estilos['linkstrongstyle'] ?>">
                        <?= htmlspecialchars($btn['text']) ?>
                    </strong>
                        <?php if (!empty($btn['hint'])): ?>
                          <span 
                            class="ms-1 hint-icon" 
                            tabindex="0"
                            data-bs-toggle="tooltip" 
                            data-bs-placement="top" 
                            title="<?= $this->e($btn['hint']) ?>"
                            aria-label="<?= $this->e($btn['hint']) ?>"
                            >
                            <i class="bi bi-info-circle"></i>
                        </span>
                        <?php endif; ?>
                </a>
            </div>
        <?php endforeach; ?>



    </div>
</div>
