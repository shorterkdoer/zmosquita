<?php
namespace App\Core;

class MasterCrud
{
    protected $entity;  // Por ejemplo, 'datospersonales'
    protected $config;  // Configuración específica para la entidad
    protected $campos;  // Campos a mostrar en el formulario
    protected $camposHidden; // Campos ocultos (opcional)
    protected $data;    // Datos actuales (opcional, para edición)

    /**
     * Constructor.
     *
     * @param string $entity Nombre de la entidad (clave en settings.php).
     * @param array  $data   Datos actuales (por ejemplo, el registro a editar).
     * @throws \Exception   Si no se encuentra la configuración para la entidad.
     */
    public function __construct(string $entity, array $config, array $campos, array $data = [], array $camposHidden = [])
    
    {
        $allSettings = require $_SESSION['directoriobase'] . '/config/cruds/' . $entity . '.php';
        if (!isset($allSettings[$entity])) {
            throw new \Exception("Configuración para la entidad {$entity} no encontrada.");
        }
        $this->entity = $entity;
        $this->config = $config;
        $this->campos = $campos;
        $this->camposHidden = $camposHidden;
        $this->data = $data;


    }

    /**
     * Genera y retorna el HTML del formulario según la configuración.
     *
     * @return string HTML generado.
     */
    public function renderForm(): string
    {
        $html = "";

        $encabezado = "";
        $arc = fopen('/views/layout.php',"r");
        while(! feof($arc))  {
            $linea = fgets($arc);
            $encabezado .= $linea;
        }
        fclose($arc);
        
        $html .= $encabezado;
        // agregar el encabezado del formulario
        $html .= "<h2>" . htmlspecialchars($this->config['titulo']) . "</h2>\n";
        $html .= "<form action=\"" . htmlspecialchars($this->config['action']) . "\" method=\"POST\" enctype=\"multipart/form-data\">\n";

        // Itera sobre los campos definidos en la configuración.
        foreach ($this->config['fields'] as $fieldName => $fieldConfig) {
            $value = $this->data[$fieldName] ?? '';
            $html .= $this->renderField($fieldName, $fieldConfig, $value);
        }
        // Renderiza los botones del formulario.
        if (isset($this->config['buttons'])) {
            $html .= "<div class=\"form-buttons\">\n";
            foreach ($this->config['buttons'] as $buttonKey => $buttonLabel) {
                if ($buttonKey === 'save') {
                    $html .= "<button type=\"submit\">" . htmlspecialchars($buttonLabel) . "</button> ";
                } elseif ($buttonKey === 'cancel') {
                    $html .= "<button type=\"button\" onclick=\"window.history.back();\">" . htmlspecialchars($buttonLabel) . "</button> ";
                }
            }
            $html .= "</div>\n";
        }
        $html .= "</form>\n";
        return $html;
    }

    /**
     * Genera el HTML para un campo específico.
     *
     * @param string $fieldName  Nombre del campo.
     * @param array  $fieldConfig Configuración del campo.
     * @param mixed  $value      Valor actual del campo.
     * @return string            HTML generado para ese campo.
     */
    protected function renderField(string $fieldName, array $fieldConfig, $value): string
    {
        $html = "";
        $caption = $fieldConfig['caption'] ?? ucfirst($fieldName);
        $type = $fieldConfig['type'] ?? 'text';

        switch ($type) {
            case 'text':
            case 'number':
            case 'password':
                $html .= "<label for=\"" . htmlspecialchars($fieldName) . "\">" . htmlspecialchars($caption) . ":</label>\n";
                $html .= "<input type=\"" . htmlspecialchars($type) . "\" name=\"" . htmlspecialchars($fieldName) . "\" id=\"" . htmlspecialchars($fieldName) . "\" value=\"" . htmlspecialchars($value) . "\"";
                if (isset($fieldConfig['maxlength'])) {
                    $html .= " maxlength=\"" . intval($fieldConfig['maxlength']) . "\"";
                }
                $html .= ">\n";
                break;

            case 'select':
                // Se espera que 'source' sea una función anónima que retorne las opciones.
                $options = [];
                if (isset($fieldConfig['source']) && is_callable($fieldConfig['source'])) {
                    $options = call_user_func($fieldConfig['source']);
                }
                // Usamos el helper buildSelect (asegúrate de tenerlo definido o incluirlo previamente)
                $html .= buildSelect($fieldName, $options, $value);
                break;

            case 'file':
                $html .= "<label for=\"" . htmlspecialchars($fieldName) . "\">" . htmlspecialchars($caption) . ":</label>\n";
                $html .= "<input type=\"file\" name=\"" . htmlspecialchars($fieldName) . "\" id=\"" . htmlspecialchars($fieldName) . "\">\n";
                // Si hay valor actual (nombre del archivo) se puede mostrar como referencia.
                if (!empty($value)) {
                    $html .= "<p>Archivo actual: " . htmlspecialchars($value) . "</p>\n";
                }
                break;
                
            // Se pueden agregar más tipos según sea necesario.
                
            default:
                $html .= "<label for=\"" . htmlspecialchars($fieldName) . "\">" . htmlspecialchars($caption) . ":</label>\n";
                $html .= "<input type=\"text\" name=\"" . htmlspecialchars($fieldName) . "\" id=\"" . htmlspecialchars($fieldName) . "\" value=\"" . htmlspecialchars($value) . "\">\n";
                break;
        }
        $html .= "<br/>\n";
        return $html;
    }
}
