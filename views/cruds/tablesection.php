<?php
?>
<table id="<?= $this->e($divname) ?>" class="<?= $this->e($style['class_table']) ?>">
    <thead class="<?= $this->e($style['class_thead']) ?>">
        <tr>
            <?php foreach ($fields as $key => $field): ?>
                <?php if (empty($field['hidden'])): ?>
                    <th class="<?= $style['class_th'] ?>"><?= $field['label'] ?></th>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php foreach ($actions as $key => $botonito): ?>
                    <th class="<?= $style['class_th'] ?>"><?= $botonito['text'] ?></th>
            <?php endforeach; ?>

        </tr>
    </thead>
    <tbody class="<?= $this->e($style['class_tbody']) ?>">
    </tbody>
</table>

<!-- Scripts necesarios -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script de inicialización dinámico -->
<script>

  $.extend( true, $.fn.dataTable.defaults, {
    "language": {
        "decimal": ",",
        "thousands": ".",
        "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
        "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
        "infoPostFix": "",
        "infoFiltered": "(filtrado de un total de _MAX_ registros)",
        "loadingRecords": "Cargando...",
        "lengthMenu": "Mostrar _MENU_ registros",
        "paginate": {
            "first": "Primero",
            "last": "Último",
            "next": "Siguiente",
            "previous": "Anterior"
        },
        "processing": "Procesando...",
        "search": "Buscar:",
        "searchPlaceholder": "Término de búsqueda",
        "zeroRecords": "No se encontraron resultados",
        "emptyTable": "Ningún dato disponible en esta tabla",
        "aria": {
            "sortAscending":  ": Activar para ordenar la columna de manera ascendente",
            "sortDescending": ": Activar para ordenar la columna de manera descendente"
        },
        //only works for built-in buttons, not for custom buttons
        "select": {
            "rows": {
                _: '%d filas seleccionadas',
                0: 'clic fila para seleccionar',
                1: 'una fila seleccionada'
            }
        }
    }           
} ); 

jQuery(document).ready(function () {
  jQuery('#<?=$this->e($divname)?>').DataTable({
    ajax: {
      url: '<?= $url_data?>',
      dataSrc: function(json) {
        // Handle both old format (aaData) and new format (data)
        if (json.error) {
          console.error('DataTables Error:', json.error);
          return [];
        }
        return json.data || json.aaData || [];
      },
      error: function(xhr, error, code) {
        console.error('AJAX Error:', error, code);
        console.error('Response:', xhr.responseText);
      }
    },
    processing: true,
    serverSide: false,
    responsive: true,
    paging: true,
    searching: true,
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
    },
    columns: [
      <?= stripslashes($zcolumns) ?>
    ]
  });
});
</script>

