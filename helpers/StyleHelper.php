<?php
class StyleHelper {
  public static function renderCSS(array $theme): string {
    return <<<CSS
<style>
  body {
    background: {$theme['base']['background']};
    color: {$theme['base']['text']};
    font-family: {$theme['base']['font_family']};
    font-size: {$theme['base']['font_size']};
  }

  .form-container {
    background: {$theme['form']['background']};
    border: 1px solid {$theme['form']['border_color']};
    border-radius: {$theme['form']['border_radius']};
    padding: {$theme['form']['input_padding']};
  }

  .btn-primary {
    background-color: {$theme['button']['primary_bg']};
    color: {$theme['button']['primary_text']};
  }

  .btn-primary:hover {
    background-color: {$theme['button']['primary_hover']};
  }

  table thead {
    background-color: {$theme['table']['header_bg']};
  }

  table tr:nth-child(even) {
    background-color: {$theme['table']['row_even']};
  }

  table tr:nth-child(odd) {
    background-color: {$theme['table']['row_odd']};
  }
</style>
CSS;
  }
}
