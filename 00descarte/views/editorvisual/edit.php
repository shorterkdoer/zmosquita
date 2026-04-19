<form action="/editorvisual/save" method="post" class="container mt-4">
  <h2>Editor visual del tema</h2>

  <div class="row">
    <div class="col-md-6">
      <h4>Base</h4>
      <label>Fondo general:</label>
      <input type="color" name="base_background" value="<?= $tema['base']['background'] ?>" class="form-control">

      <label>Texto:</label>
      <input type="color" name="base_text" value="<?= $tema['base']['text'] ?>" class="form-control">

      <label>Fuente:</label>
      <input type="text" name="font_family" value="<?= $tema['base']['font_family'] ?>" class="form-control">

      <label>Tamaño fuente:</label>
      <input type="text" name="font_size" value="<?= $tema['base']['font_size'] ?>" class="form-control">
    </div>

    <div class="col-md-6">
      <h4>Formulario</h4>
      <label>Fondo:</label>
      <input type="color" name="form_background" value="<?= $tema['form']['background'] ?>" class="form-control">

      <label>Borde:</label>
      <input type="color" name="form_border_color" value="<?= $tema['form']['border_color'] ?>" class="form-control">

      <label>Radio de bordes:</label>
      <input type="text" name="form_border_radius" value="<?= $tema['form']['border_radius'] ?>" class="form-control">

      <label>Padding de inputs:</label>
      <input type="text" name="form_input_padding" value="<?= $tema['form']['input_padding'] ?>" class="form-control">
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-md-6">
      <h4>Botón primario</h4>
      <label>Fondo:</label>
      <input type="color" name="button_primary_bg" value="<?= $tema['button']['primary_bg'] ?>" class="form-control">

      <label>Texto:</label>
      <input type="color" name="button_primary_text" value="<?= $tema['button']['primary_text'] ?>" class="form-control">

      <label>Hover:</label>
      <input type="color" name="button_primary_hover" value="<?= $tema['button']['primary_hover'] ?>" class="form-control">
    </div>

    <div class="col-md-6">
      <h4>Tabla</h4>
      <label>Encabezado:</label>
      <input type="color" name="table_header_bg" value="<?= $tema['table']['header_bg'] ?>" class="form-control">

      <label>Fila par:</label>
      <input type="color" name="table_row_even" value="<?= $tema['table']['row_even'] ?>" class="form-control">

      <label>Fila impar:</label>
      <input type="color" name="table_row_odd" value="<?= $tema['table']['row_odd'] ?>" class="form-control">
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-md-6">
      <label>Nombre del nuevo tema:</label>
      <input type="text" name="guardar_como" placeholder="custom_mitema" class="form-control">
    </div>
    <div class="col-md-6 d-flex align-items-end justify-content-end">
      <button type="submit" class="btn btn-success">Guardar y aplicar</button>
    </div>
  </div>
</form>
