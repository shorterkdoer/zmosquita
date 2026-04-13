<?php

namespace App\Controllers;

use App\Core\Controller;
use Foundation\Core\Request;
use Foundation\Core\Session;
use Foundation\Core\CSRF;
use App\Models\Matricula;
use App\Models\Comision;
use App\Models\Numeros;
use App\Models\DatosPersonales;
use App\Models\ComprobantesPago;
use App\Models\Tramites;
use App\Models\User;
use App\Services\MatriculaService;
use App\Services\FileService;
use App\Services\DocumentService;
use App\Services\EmailService;
use App\Services\TramiteService;
use TCPDF;

/**
 * MatriculaController - Refactored to use Service Layer
 *
 * This controller now delegates business logic to services:
 * - MatriculaService: matricula status, workflow operations
 * - FileService: file upload and validation
 * - DocumentService: PDF generation, credentials
 * - TramiteService: tramite creation
 */
class MatriculaController extends Controller
{
    protected MatriculaService $matriculas;
    protected FileService $files;
    protected DocumentService $documents;
    protected EmailService $emails;
    protected TramiteService $tramites;

    public function __construct()
    {
        $this->emails = new EmailService();
        $this->tramites = new TramiteService($this->emails);
        $this->matriculas = new MatriculaService($this->tramites, $this->emails);
        $this->files = new FileService();
        $this->documents = new DocumentService();
    }

    // ==================== EDIT METHODS ====================

    /**
     * Show matricula edit form (main entry point)
     */
    public function edit(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);
        $id = (int)($params[0] ?? 0);

        if (!$this->canEdit($user, $id)) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        $matricula = $this->getOrCreateMatricula($id);
        $status = $this->matriculas->getStatus($id);

        // Load different config based on status
        $configFile = $status === ''
            ? '/config/cruds/matricula/matricula_edit.php'
            : '/config/cruds/matricula/matricula_review.php';

        $this->renderMatriculaForm($configFile, $matricula, $id);
    }

    /**
     * Show re-matriculation form
     */
    public function edit_rematric(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = $user['id'] ?? 0;
        if (!$this->isAdmin($user)) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        $matricula = $this->getOrCreateMatricula($id, ['interviniente' => 0]);
        $status = $this->matriculas->getStatus($id);

        $configFile = $status === ''
            ? '/config/cruds/matricula/matricula_edit_re.php'
            : '/config/cruds/matricula/matricula_review.php';

        $this->renderMatriculaForm($configFile, $matricula, $id);
    }

    /**
     * Show first matriculation form
     */
    public function edit_first(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = $user['id'] ?? 0;
        $matricula = $this->getOrCreateMatricula($id, ['interviniente' => 0]);
        $status = $this->matriculas->getStatus($id);

        $configFile = $status === ''
            ? '/config/cruds/matricula/matricula_edit_pri.php'
            : '/config/cruds/matricula/matricula_review.php';

        $this->renderMatriculaForm($configFile, $matricula, $id);
    }

    /**
     * Show previous matriculation form
     */
    public function edit_prov(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = $user['id'] ?? 0;
        $matricula = $this->getOrCreateMatricula($id, ['interviniente' => 0]);
        $status = $this->matriculas->getStatus($id);

        $configFile = $status === ''
            ? '/config/cruds/matricula/matricula_edit_prov.php'
            : '/config/cruds/matricula/matricula_review.php';

        $this->renderMatriculaForm($configFile, $matricula, $id);
    }

    /**
     * Show foreign title matriculation form
     */
    public function edit_extranjero(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = $user['id'] ?? 0;
        $matricula = $this->getOrCreateMatricula($id, ['interviniente' => 0]);
        $status = $this->matriculas->getStatus($id);

        $configFile = $status === ''
            ? '/config/cruds/matricula/matricula_edit_extr.php'
            : '/config/cruds/matricula/matricula_review.php';

        $this->renderMatriculaForm($configFile, $matricula, $id);
    }

    // ==================== UPDATE METHODS ====================

    /**
     * Update matricula with file uploads (main method)
     */
    public function update(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($params[0] ?? 0);
        if ($id !== $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
            return;
        }

        $fileFields = [
            'notaddjj', 'dnifrente', 'dnidorso', 'titulooriginalfrente',
            'titulooriginaldorso', 'fotoregistrodegraduados', 'fotocarnet',
            'antecedentespenales', 'libredeudaalimentario', 'constanciaCUIL',
            'apostillado', 'matriculaprevia', 'certificadoetica'
        ];

        $data = $this->processFileUploads($id, $fileFields, $_POST);

        if (!Matricula::updatebyUser($id, $data)) {
            Session::flash('error', 'Error actualizando datos.');
            $this->redirect($this->getRefererPath());
            return;
        }

        Session::flash('success', 'Datos actualizados correctamente.');
        $this->redirect('/dashboard');
    }

    /**
     * Update re-matriculation
     */
    public function updaterem(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($params[0] ?? 0);
        if ($id !== $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        $fileFields = [
            'matriculaministerio', 'notaddjj', 'dnifrente', 'dnidorso',
            'titulooriginalfrente', 'titulooriginaldorso', 'fotoregistrodegraduados',
            'fotocarnet', 'antecedentespenales', 'libredeudaalimentario', 'constanciaCUIL'
        ];

        $data = $this->processFileUploads($id, $fileFields, $_POST);

        if (!Matricula::updatebyUser($id, $data)) {
            Session::flash('error', 'Error actualizando datos.');
            $this->redirect($this->getRefererPath());
            return;
        }

        Session::flash('success', 'Datos actualizados correctamente.');
        $this->redirect('/rematricula');
    }

    /**
     * Update first matriculation
     */
    public function updatefirst(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($params[0] ?? 0);
        if ($id != $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
            return;
        }

        $fileFields = [
            'notaddjj', 'dnifrente', 'dnidorso', 'titulooriginalfrente',
            'titulooriginaldorso', 'fotocarnet', 'antecedentespenales',
            'libredeudaalimentario', 'constanciaCUIL'
        ];

        $data = $this->processFileUploads($id, $fileFields, $_POST);

        if (!Matricula::updatebyUser($id, $data)) {
            Session::flash('error', 'Error actualizando datos.');
            $this->redirect($this->getRefererPath());
            return;
        }

        Session::flash('success', 'Datos actualizados correctamente.');
        $this->redirect('/primeramatricula');
    }

    /**
     * Update previous matriculation
     */
    public function updateprov(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($params[0] ?? 0);
        if ($id !== $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
            return;
        }

        $fileFields = [
            'matriculaprevia', 'certificadoetica', 'notaddjj', 'dnifrente',
            'dnidorso', 'titulooriginalfrente', 'titulooriginaldorso',
            'fotocarnet', 'antecedentespenales', 'libredeudaalimentario', 'constanciaCUIL'
        ];

        $data = $this->processFileUploads($id, $fileFields, $_POST);

        if (!Matricula::updatebyUser($id, $data)) {
            Session::flash('error', 'Error actualizando datos.');
            $this->redirect($this->getRefererPath());
            return;
        }

        Session::flash('success', 'Datos actualizados correctamente.');
        $this->redirect('/previamatricula');
    }

    /**
     * Update foreign title matriculation
     */
    public function updateextranjero(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($params[0] ?? 0);
        if ($id !== $user['id']) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
            return;
        }

        $fileFields = [
            'apostillado', 'notaddjj', 'dnifrente', 'dnidorso',
            'titulooriginalfrente', 'titulooriginaldorso', 'fotocarnet',
            'antecedentespenales', 'libredeudaalimentario', 'constanciaCUIL'
        ];

        $data = $this->processFileUploads($id, $fileFields, $_POST);

        if (!Matricula::updatebyUser($id, $data)) {
            Session::flash('error', 'Error actualizando datos.');
            $this->redirect($this->getRefererPath());
            return;
        }

        Session::flash('success', 'Datos actualizados correctamente.');
        $this->redirect('/titulodeotranacion');
    }

    // ==================== VALIDATION & REVIEW ====================

    /**
     * Validate and redirect to revision for foreign title
     */
    public function rev2extranjero(): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($user['id'] ?? 0);
        $matricula = $this->getOrCreateMatricula($id);

        $requiredFields = [
            'apostillado' => 'Falta el apostillado',
            'notaddjj' => 'Falta la nota solicitud',
            'dnifrente' => 'Ingrese el DNI',
            'titulooriginalfrente' => 'Falta el título original',
            'fotocarnet' => 'Falta la foto carnet',
            'antecedentespenales' => 'Falta el certificado de antecedentes penales',
            'libredeudaalimentario' => 'Falta el libre deuda alimentario',
            'constanciaCUIL' => 'Falta la constancia de CUIL'
        ];

        foreach ($requiredFields as $field => $error) {
            if (empty($matricula[$field])) {
                Session::flash('error', $error);
                $this->redirect('/titulodeotranacion');
                return;
            }
        }

        $this->validatePaymentAndPersonalData($id, '/titulodeotranacion');
        $this->redirect('/arevision');
    }

    /**
     * Validate and redirect to revision for first matriculation
     */
    public function rev2prim(): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($user['id'] ?? 0);
        $matricula = $this->getOrCreateMatricula($id);

        $requiredFields = [
            'notaddjj' => 'Falta la nota solicitud',
            'dnifrente' => 'Ingrese el DNI',
            'titulooriginalfrente' => 'Falta el título original',
            'fotocarnet' => 'Falta la foto carnet',
            'antecedentespenales' => 'Falta el certificado de antecedentes penales',
            'libredeudaalimentario' => 'Falta el libre deuda alimentario',
            'constanciaCUIL' => 'Falta la constancia de CUIL'
        ];

        foreach ($requiredFields as $field => $error) {
            if (empty($matricula[$field])) {
                Session::flash('error', $error);
                $this->redirect('/primeramatricula');
                return;
            }
        }

        $this->validatePaymentAndPersonalData($id, '/primeramatricula');
        $this->redirect('/arevision');
    }

    /**
     * Validate and redirect to revision for previous matriculation
     */
    public function rev2prov(): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($user['id'] ?? 0);
        $matricula = $this->getOrCreateMatricula($id);

        $requiredFields = [
            'matriculaprevia' => 'Falta el número de matricula de la jurisdicción anterior',
            'certificadoetica' => 'Falta certificado de ética'
        ];

        foreach ($requiredFields as $field => $error) {
            if (empty($matricula[$field])) {
                Session::flash('error', $error);
                $this->redirect('/previamatricula');
                return;
            }
        }

        $baseFields = [
            'notaddjj', 'dnifrente', 'titulooriginalfrente', 'fotocarnet',
            'antecedentespenales', 'libredeudaalimentario', 'constanciaCUIL'
        ];

        foreach ($baseFields as $field) {
            if (empty($matricula[$field])) {
                Session::flash('error', 'Faltan datos completar');
                $this->redirect('/previamatricula');
                return;
            }
        }

        $this->validatePaymentAndPersonalData($id, '/previamatricula');
        $this->redirect('/arevision');
    }

    /**
     * Validate and redirect to revision for re-matriculation
     */
    public function rev2rematric(): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($user['id'] ?? 0);
        $matricula = $this->getOrCreateMatricula($id);

        if (empty($matricula['matriculaministerio'])) {
            Session::flash('error', 'Falta el número de matricula del ministerio.');
            $this->redirect('/rematricula');
            return;
        }

        $baseFields = [
            'notaddjj', 'dnifrente', 'titulooriginalfrente', 'fotocarnet',
            'antecedentespenales', 'libredeudaalimentario', 'constanciaCUIL'
        ];

        foreach ($baseFields as $field) {
            if (empty($matricula[$field])) {
                Session::flash('error', 'Faltan datos por completar');
                $this->redirect('/rematricula');
                return;
            }
        }

        $this->validatePaymentAndPersonalData($id, '/rematricula');
        $this->redirect('/arevision');
    }

    // ==================== ADMIN: GRANT MATRICULA ====================

    /**
     * Show form to grant matricula
     */
    public function matristatus(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($params[0] ?? 0);

        // Check if user is commission president or vice president
        if (!$this->isCommissionAuthorized($user['id'])) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
            return;
        }

        $matricula = $this->getOrCreateMatricula($id);

        // Determine matricula number to assign
        if (!empty($matricula['matriculaministerio'])) {
            $matricula['matriculaasignada'] = $matricula['matriculaministerio'];
        } else {
            $numeros = Numeros::findByRotulo('Matricula');
            if (!$numeros) {
                Session::flash('error', 'No se encontró el número de matrícula.');
                $this->redirect('/dashboard');
                return;
            }
            $matricula['matriculaasignada'] = $numeros['valor'] + 1;
        }

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matriculaestado.php';

        $cfg = $cfgedit['config'] ?? [];
        $cfg['url_action'] .= '/' . $id;
        $campos = $cfgedit['campos'] ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos = $cfgedit['comandos'] ?? [];
        $buttons = $cfgedit['buttons'] ?? [];

        $this->view('cruds/index', [
            'cfg' => $cfg,
            'style' => $style,
            'fields' => $campos,
            'values' => $matricula,
            'actions' => $actividades,
            'comandos' => $comandos,
            'buttons' => $buttons,
            'id' => $id,
            'user_id' => $id,
        ]);
    }

    /**
     * Process matricula granting
     */
    public function grabarmatricula(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($params[0] ?? 0);

        if (!$this->isCommissionAuthorized($user['id'])) {
            Session::flash('error', 'No tiene privilegios.');
            $this->redirect('/dashboard');
            return;
        }

        // Validate date
        $mfecha = $_POST['aprobado'] ?? '';
        $fecha_actual = date("Y/m/d");

        if (empty($mfecha)) {
            Session::flash('error', 'Verifique la fecha.');
            $this->redirect('/estadomatricula/' . $id);
            return;
        }

        if (strtotime($mfecha) > strtotime($fecha_actual)) {
            Session::flash('error', 'La fecha de aprobación no puede ser posdatada.');
            $this->redirect('/estadomatricula/' . $id);
            return;
        }

        // Check if matricula number is already assigned
        $matriculaAsignada = (int)($_POST['matriculaasignada'] ?? 0);
        if (Matricula::findByAsignada($matriculaAsignada)) {
            Session::flash('error', 'El número de matrícula ya está asignado a otro usuario.');
            $this->redirect('/estadomatricula/' . $id);
            return;
        }

        // Get active commission
        $comision = Comision::activa();

        // Use MatriculaService to grant matricula
        $result = $this->matriculas->otorgarMatricula($id, $matriculaAsignada, $comision['id']);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/estadomatricula/' . $id);
            return;
        }

        // Update numbers counter
        $ultimamatricula = Numeros::findByRotulo('Matricula');
        if ($matriculaAsignada > $ultimamatricula['valor']) {
            Numeros::updatebyRotulo($matriculaAsignada, 'Matricula');
        }

        // Create tramite record
        $nombrerevisor = DatosPersonales::GetNombreById($user['id']);
        $observaciones = 'Intervino: ' . $nombrerevisor . ' Matricula Otorgada ';
        Tramites::CustomQry("INSERT INTO tramites (user_id, fecha, observaciones) VALUES ($id, '$mfecha', '$observaciones')");

        // Send email notification
        $email = User::GetEmail($id);
        $subject = 'Credencial de matrícula otorgada';
        $body = 'La presente se envía al efecto de hacerle saber que su matrícula ha sido otorgada y que se puede obtener desde http://www.coprobilp.org.ar/carnet/' . $matriculaAsignada;
        $this->sendGeneralEmail($email, $subject, $body);

        Session::flash('success', 'Matrícula actualizada correctamente.');
        $this->redirect('/dashboard');
    }

    // ==================== ADMIN: BAJAS ====================

    /**
     * Show bajas management page
     */
    public function gestionbajas(Request $request): void
    {
        $user = Session::get('user');
        if (!$user || !isset($user['id'])) {
            Session::flash('error', 'Debe iniciar sesión para acceder al panel de control');
            $this->redirect('/login');
            return;
        }

        $cfgdash = require $_SESSION['directoriobase'] . '/views/dashboard/menubajas.php';
        $landingCfg = $cfgdash['landing'] ?? [];
        $cfgHeader = $landingCfg['header'] ?? [];
        $buttons = $landingCfg['botones'] ?? [];

        $cfgstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/landingstyle.php';
        $landinCSS = $cfgstyle['styles'] ?? [];

        $this->view('dashboard/mylandingpage', [
            'cfgHeader' => $cfgHeader,
            'estilos' => $landinCSS,
            'buttons' => $buttons,
            'userId' => $user['id'],
        ]);
    }

    /**
     * Process baja form submission (POST handler)
     */
    public function bajarmatricula(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        if (!$this->isCommissionAuthorized($user['id'])) {
            Session::flash('error', 'No tiene privilegios.');
            $this->redirect('/dashboard');
            return;
        }

        // Validate date
        $mfecha = $_POST['fecha'] ?? '';
        if (empty($mfecha)) {
            Session::flash('error', 'Verifique la fecha.');
            $this->redirect('/iniciarbaja');
            return;
        }

        $fechaBarras = str_replace('-', '/', $mfecha);
        $xmat = (int)($_POST['matriculado'] ?? 0);
        $motivo = $_POST['motivo'] ?? '';

        // Use MatriculaService to process baja
        $result = $this->matriculas->darDeBaja($xmat, $motivo);

        if (!$result['success']) {
            Session::flash('error', $result['error']);
            $this->redirect('/iniciarbaja');
            return;
        }

        // Update additional fields
        $comision = Comision::activa();
        $data = [
            'baja' => $fechaBarras,
            'funcionario' => $user['id'],
            'comisionotorgante' => $comision['id']
        ];

        Matricula::updatebyUser($xmat, $data);

        // Create tramite record
        $nombrerevisor = DatosPersonales::GetNombreById($user['id']);
        $observaciones = '** Dado de baja ** Motivo: ' . $motivo . ' Intervino: ' . $nombrerevisor;
        Tramites::CustomQry("INSERT INTO tramites (user_id, fecha, observaciones) VALUES ($xmat, '$mfecha', '$observaciones')");

        error_log('Baja para la matrícula ' . $xmat);

        Session::flash('success', 'Matrícula dada de baja.');
        $this->redirect('/menubajas');
    }

    /**
     * Show dar de baja form (GET handler)
     */
    public function dardebaja(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        if (!$this->isAdmin($user)) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/user-dashboard');
            return;
        }

        if (!$this->isCommissionAuthorized($user['id'])) {
            Session::flash('error', 'No tiene privilegios.');
            $this->redirect('/dashboard');
            return;
        }

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];

        $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/tramites/bajas/nuevabaja.php';
        $cfg = $cfgedit['config'] ?? [];
        $id_field = $cfgedit['config']['field_id'];

        $campos = $cfgedit['campos'] ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos = $cfgedit['comandos'] ?? [];
        $buttons = $cfgedit['buttons'] ?? [];
        $tables = $cfgedit['QrySpec']['tables'] ?? [];
        $joinconditions = $cfgedit['QrySpec']['joincond'] ?? '';
        $filter = $cfgedit['QrySpec']['filter'] ?? '';
        $order = $cfgedit['QrySpec']['order'] ?? [];

        require_once $_SESSION['directoriobase'] . '/app/Core/Helpers/string4query.php';

        $matriculadosList = DatosPersonales::HtmlDropDown($campos['matriculado']['options']);
        $campos['matriculado']['listavalores'] = $matriculadosList;

        $this->pendingquery = str4qry($tables, $campos, $actividades, $filter, $joinconditions, $order, $id_field);

        $zcolumns = Self::mkColumns($campos, $actividades);
        $zcolumns = trim(stripslashes($zcolumns), '"');

        $this->view('cruds/index', [
            'cfg' => $cfg,
            'fields' => $campos,
            'style' => $style,
            'values' => [],
            'actions' => $actividades,
            'comandos' => $comandos,
            'buttons' => $buttons,
            'divname' => $cfg['divname'] ?? '',
            'id' => 'id',
            'link_id' => 'user_id',
            'scriptjs_data' => $this->pendingquery,
            'scriptjs_columns' => $this->pendingcolumns,
            'zcolumns' => $zcolumns,
            'url_data' => $_SESSION['base_url'] . $cfg['url_data'],
            'matriculadosList' => $matriculadosList,
        ]);
    }

    // ==================== CREDENTIAL/VIEW ====================

    /**
     * Show credential form
     */
    public function formcarnet(Request $request): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = $user['id'] ?? 0;
        $matricula = $this->getOrCreateMatricula($id);

        if (empty($matricula['matriculaasignada'])) {
            Session::flash('error', 'No se encontró el número de matrícula.');
            $this->redirect('/dashboard');
            return;
        }

        $userfolder = $this->getUserFolder($id);
        $pdfFile = $_SESSION['directoriobase'] . '/' . $userfolder . 'credencial_' . $matricula['matriculaasignada'] . '.pdf';
        $pngFile = $_SESSION['directoriobase'] . '/' . $userfolder . 'credencial_' . $matricula['matriculaasignada'] . '.png';

        if (!file_exists($pdfFile) || !file_exists($pngFile)) {
            Session::flash('error', 'Solicite reemisión del carnet.');
            $this->redirect($_SERVER['HTTP_REFERER']);
            return;
        }

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/matricula/vistacredencial.php';

        $cfg = $cfgedit['config'] ?? [];
        $campos = $cfgedit['campos'] ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos = $cfgedit['comandos'] ?? [];
        $buttons = $cfgedit['buttons'] ?? [];

        $this->view('cruds/index', [
            'cfg' => $cfg,
            'style' => $style,
            'fields' => $campos,
            'values' => $matricula,
            'actions' => $actividades,
            'comandos' => $comandos,
            'buttons' => $buttons,
            'id' => $id,
            'user_id' => $id,
        ]);
    }

    /**
     * Show attached file
     */
    public function mostraradjunto(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        require_once $_SESSION['directoriobase'] . '/config/actions/veradjunto.php';

        $id = $_SESSION["idrec"] ?? 0;
        $myadj = (string)($params[0] ?? '');

        if (empty($myadj)) {
            Session::flash('error', 'No se especificó ningún archivo.');
            $this->redirect('/dashboard');
            return;
        }

        $rutaaladjunto = $this->getUserFolder($id);
        renderFileViewer($myadj, $rutaaladjunto, false);
    }

    /**
     * Emit credential (static method for route access)
     */
    public static function emitircredencial(int $nromatricula): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $matricula = Matricula::findByAsignada($nromatricula);
        if ($matricula == null) {
            die('Matrícula no encontrada.');
        }

        self::crearcredencial($nromatricula);
    }

    /**
     * Show credential PDF
     */
    public static function mostrarcredencial(int $nromatricula): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $matricula = Matricula::findByAsignada($nromatricula);
        if ($matricula == null) {
            die('Matrícula no encontrada.');
        }

        $locuser = $matricula['user_id'];
        $uploadFolder = self::getUserUploadFolder($locuser);
        $pdfgen = $uploadFolder . '/credencial_' . $matricula['matriculaasignada'] . '.pdf';

        if (file_exists($pdfgen)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($pdfgen) . '"');
            readfile($pdfgen);
            exit;
        } else {
            die('Credencial no encontrada.');
        }
    }

    /**
     * Create credential PDF
     */
    public static function crearcredencial(int $numeromatricula): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $matricula = Matricula::findByAsignada($numeromatricula);
        if (!$matricula) {
            die('Matrícula no encontrada.');
        }

        $locuser = $matricula['user_id'];
        $datos = DatosPersonales::findByUserId($locuser);
        if (!$datos) {
            die('Registro de usuario no encontrados.');
        }

        // Import QR library
        require_once $_SESSION['directoriobase'] . '/vendor/bacon/bacon-qr-code/src/BaconQrCode.php';

        $pdf = new TCPDF('P', 'mm', 'A5', true, 'UTF-8', false);
        $pdf->AddPage();

        // Configuration
        $x0 = 36;
        $y0 = 80;

        // Background image
        $imagen_fondo = $_SESSION['directoriobase'] . '/public/img/credencial_fondo.jpeg';
        $pdf->Image($imagen_fondo, $x0, $y0, 75, 50);

        // Photo
        $uploadFolder = self::getUserUploadFolder($locuser);
        if (!empty($matricula['fotocarnet'])) {
            $fotocarnet = $uploadFolder . '/' . $matricula['fotocarnet'];
            if (file_exists($fotocarnet)) {
                $pdf->Image($fotocarnet, $x0 + 53, $y0 + 5, 19, 19);
            }
        }

        // Name
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetXY($x0 + 4, $y0 + 18);
        $pdf->MultiCell(46, 4, $datos['apellido'] . ', ' . $datos['nombres'], 0, 'L');

        // Matricula number
        $pdf->SetXY($x0 + 4, $y0 + 28);
        $pdf->Cell(25, 5, $matricula['matriculaasignada'], 0, 0, 'L');

        // Approval date
        $pdf->SetXY($x0 + 32, $y0 + 28);
        $pdf->Cell(18, 5, $matricula['aprobado'], 0, 0, 'L');

        // QR Code
        $xconfig = require $_SESSION['directoriobase'] . '/config/settings.php';
        $qr_text = $xconfig['base_url'] . '/credencial/' . urlencode($matricula['matriculaasignada']);
        $temp_qr = tempnam(sys_get_temp_dir(), 'qr_') . '.png';

        $qrCode = \BaconQrCode\Renderer\Image\Png::create(
            \BaconQrCode\Encoder\Encoder::encode($qr_text),
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150)
        );
        file_put_contents($temp_qr, $qrCode);

        $pdf->Image($temp_qr, $x0 + 53, $y0 + 28, 19, 19);
        unlink($temp_qr);

        // Save PDF
        $savingFolder = self::getUserUploadFolder($locuser);
        $pdfgen = $savingFolder . '/credencial_' . $matricula['matriculaasignada'] . '.pdf';
        $pdf->Output('F', $pdfgen);
    }

    // ==================== OTHER METHODS ====================

    public function matriculacion(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);
        $this->redirect('/opcionmatricula');
    }

    public function matric_index(): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        if (($user['role'] ?? '') !== 'admin') {
            header('Location: /menumatriculacion');
        }
        exit;
    }

    public function menu_matric(): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->view('dashboard/menumatricula', ['user' => $user]);
    }

    public function infoaltas(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($params[0] ?? 0);

        if (!$this->canEdit($user, $id)) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
            return;
        }

        $matricula = $this->getOrCreateMatricula($id);

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgedit = require $_SESSION['directoriobase'] . '/config/actions/matriculasdealta.php';

        $cfg = $cfgedit['config'] ?? [];
        $campos = $cfgedit['campos'] ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos = $cfgedit['comandos'] ?? [];
        $buttons = $cfgedit['buttons'] ?? [];

        $this->view('cruds/index', [
            'cfg' => $cfg,
            'style' => $style,
            'fields' => $campos,
            'values' => $matricula,
            'actions' => $actividades,
            'comandos' => $comandos,
            'buttons' => $buttons,
            'id' => $id,
            'user_id' => $id,
        ]);
    }

    public function reviewmatri(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($params[0] ?? 0);

        if (!$this->canEdit($user, $id)) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
            return;
        }

        $matricula = $this->getOrCreateMatricula($id);

        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgedit = require $_SESSION['directoriobase'] . '/config/cruds/matricula/matricula_review.php';

        $cfg = $cfgedit['config'] ?? [];
        $cfg['url_action'] .= '/' . $id;
        $campos = $cfgedit['campos'] ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos = $cfgedit['comandos'] ?? [];
        $buttons = $cfgedit['buttons'] ?? [];

        $this->view('cruds/index', [
            'cfg' => $cfg,
            'style' => $style,
            'fields' => $campos,
            'values' => $matricula,
            'actions' => $actividades,
            'comandos' => $comandos,
            'buttons' => $buttons,
            'id' => $id,
            'user_id' => $id,
        ]);
    }

    public function reportealtas(Request $request, array $params = []): void
    {
        $this->ensureSession();
        $user = $_SESSION['user'] ?? null;
        $this->requireAuth($user);

        $id = (int)($params[0] ?? 0);

        if (!$this->canEdit($user, $id)) {
            Session::flash('error', 'No tiene permiso para editar estos datos.');
            $this->redirect('/dashboard');
            return;
        }
        // TODO: Implement report generation
    }

    // ==================== HELPER METHODS ====================

    /**
     * Ensure session is started
     */
    protected function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Require user to be authenticated
     */
    protected function requireAuth(?array $user): void
    {
        if (!$user) {
            Session::flash('error', 'Debe iniciar sesión.');
            $this->redirect('/login');
        }
    }

    /**
     * Check if user is admin
     */
    protected function isAdmin(array $user): bool
    {
        return ($user['role'] ?? '') === 'admin';
    }

    /**
     * Check if user can edit matricula
     */
    protected function canEdit(?array $user, int $id): bool
    {
        if (!$user) return false;
        return $this->isAdmin($user) || $id === $user['id'];
    }

    /**
     * Check if user is commission president or vice president
     */
    protected function isCommissionAuthorized(int $userId): bool
    {
        return Comision::espresidente($userId) || Comision::esvicepresidente($userId);
    }

    /**
     * Get or create matricula for user
     */
    protected function getOrCreateMatricula(int $userId, array $extraData = []): array
    {
        $matricula = Matricula::findByUserId($userId);

        if (!$matricula) {
            $data = ['user_id' => $userId] + $extraData;
            Matricula::create($data);
            $matricula = Matricula::findByUserId($userId);
        }

        return $matricula;
    }

    /**
     * Process file uploads for matricula
     */
    protected function processFileUploads(int $userId, array $fileFields, array $postData): array
    {
        $data = $postData;
        $referer = $this->getRefererPath();

        foreach ($fileFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $result = $this->files->uploadForUser($userId, $_FILES[$field], $field);

                if (!$result['success']) {
                    Session::flash('error', $result['error']);
                    $this->redirect($referer);
                }

                $data[$field] = $result['filename'];
            }
        }

        return $data;
    }

    /**
     * Validate payment and personal data
     */
    protected function validatePaymentAndPersonalData(int $userId, string $redirectUrl): void
    {
        if (!ComprobantesPago::informopagos($userId)) {
            Session::flash('error', 'Debe abonar el arancel de inscripción.');
            $this->redirect($redirectUrl);
            return;
        }

        if (DatosPersonales::faltandatos($userId)) {
            Session::flash('error', 'Datos personales incompletos.');
            $this->redirect($redirectUrl);
        }
    }

    /**
     * Render matricula form
     */
    protected function renderMatriculaForm(string $configFile, array $matricula, int $id): void
    {
        $crudstyle = require $_SESSION['directoriobase'] . '/config/cruds/defaults/crudstyle.php';
        $style = $crudstyle['style'] ?? [];
        $cfgedit = require $_SESSION['directoriobase'] . $configFile;

        $cfg = $cfgedit['config'] ?? [];
        $cfg['url_action'] .= '/' . $id;
        $campos = $cfgedit['campos'] ?? [];
        $actividades = $cfgedit['actividades'] ?? [];
        $comandos = $cfgedit['comandos'] ?? [];
        $buttons = $cfgedit['buttons'] ?? [];

        $this->view('cruds/index', [
            'cfg' => $cfg,
            'style' => $style,
            'fields' => $campos,
            'values' => $matricula,
            'actions' => $actividades,
            'comandos' => $comandos,
            'buttons' => $buttons,
            'id' => $id,
            'user_id' => $id,
        ]);
    }

    /**
     * Send general email
     */
    protected function sendGeneralEmail(string $email, string $subject, string $body): bool
    {
        return $this->emails->send($email, $subject, $body);
    }
}
