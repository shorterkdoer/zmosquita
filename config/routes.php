<?php
// config/routes.php

use App\Controllers\AgendaDeCitasController;
use App\Core\Router;
//use DocumentTemplateController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\AdminMiddleware;
use App\Middlewares\GuestMiddleware;
use App\Controllers\CiudadController;
use App\Controllers\DashboardController;
use App\Controllers\MatriculaController;
use App\Controllers\ProvinciaController;
use App\Controllers\EditorVisualController;
use App\Controllers\DatosPersonalesController;
use App\Controllers\ComprobantesPagoController;
use App\Controllers\ConfigController;
use App\Controllers\CargoController;
use App\Controllers\ComisionController;

use App\Controllers\DocumentTemplateController;
use App\Controllers\NumerosController;
use App\Controllers\TramitesController;
use App\Models\DatosPersonales;;

$_SESSION['previous_url'] = $_SERVER['REQUEST_URI'];

// include_once "adminmenuland.php";

Router::get('/dashboard', [DashboardController::class, 'index']);
Router::get('/login', [AuthController::class, 'loginForm'], [GuestMiddleware::class]);
// Ruta para procesar el login (POST)
Router::post('/login', [AuthController::class, 'logged'], [GuestMiddleware::class]);
Router::get('/logout', [AuthController::class, 'logout']);
Router::get('/', [HomeController::class, 'welcome']);
Router::get('/register', [AuthController::class, 'registerForm']);
Router::post('/register', [AuthController::class, 'register']);
Router::get('/activate/{token}', [AuthController::class, 'activateAccount']);
Router::get('/requisitos', [AuthController::class, 'requisitos']);
Router::get('/institucional', [AuthController::class, 'institucional']);

//Router::get('/micredencial', [DatosPersonalesController::class, 'emitircredencial'], [AuthMiddleware::class]);
Router::get('/micredencial', [MatriculaController::class, 'formcarnet'], [AuthMiddleware::class]);
Router::get('/credencial/{id}', [DatosPersonalesController::class, 'showcredencial']);
Router::get('/carnet/{id}', [DatosPersonalesController::class, 'showcarnet']);
Router::get('/crearcarnet/{id}', [DatosPersonalesController::class, 'generarcredenciales'], [AuthMiddleware::class]);


Router::get('/valores', [ConfigController::class, 'consultar'], [AdminMiddleware::class]);
Router::get('/soporte', [ConfigController::class, 'soportesistema']);


//admin menu
Router::get('/admin-dashboard', [AuthController::class, 'showAdminDashboard'], [AdminMiddleware::class]);

//user menu
Router::get('/user-dashboard', [AuthController::class, 'showUserDashboard']);
Router::get('/matriculas', [AuthController::class, 'showMenuMatriculas']);

Router::get('/controlinscripciones', [AuthController::class, 'showMenuCtrlMatric'], [AdminMiddleware::class]);
Router::get('/controldocumentacion', [AuthController::class, 'showMenuCtrlDocu'], [AdminMiddleware::class]);

//editorvisual/preview
// Ruta para mostrar el formulario de login: solo para invitados (no logueados)

Router::get('/arevision', [AuthController::class, 'piderevision'], [AuthMiddleware::class]);
Router::post('/vaarevision', [AuthController::class, 'procedurevision'], [AuthMiddleware::class]);

Router::get('/api/userpending/data', [UserController::class, 'apiPendingData']);

// /api/comprobantespago/data
Router::get('/solicitudes', [UserController::class, 'pendingbrowse'], [AuthMiddleware::class]);
// Dashboard para usuarios regulares
//Router::get('/user-dashboard', [DashboardController::class, 'userDashboard']);
Router::get('/descargas', [AuthController::class, 'showDescargas']);

// Dashboard para administradores




Router::get('/aceptaruser/{email}', [AuthController::class, 'aceptarsolicitud'], [AuthMiddleware::class]);

// Comision
Router::get('/comision', [ComisionController::class, 'index']);
Router::get('/comision/nuevo', [ComisionController::class, 'nuevo']);
Router::post('/comision/create', [ComisionController::class, 'store']);
Router::get('/comision/editar/{id}', [ComisionController::class, 'editar']);
Router::post('/comision/update/{id}', [ComisionController::class, 'update']);
Router::get('/comision/borrar/{id}', [ComisionController::class, 'borrar']);




// Rutas para Provincias

Router::get('/provincias', [ProvinciaController::class, 'index'], [AdminMiddleware::class]);
Router::get('/provincias/edit/{id}', [ProvinciaController::class, 'edit'], [AdminMiddleware::class]);
Router::get('/provincias/create', [ProvinciaController::class, 'create'], [AdminMiddleware::class]);
Router::post('/provincias/store', [ProvinciaController::class, 'store'], [AdminMiddleware::class]);
Router::post('/provincias/update/{id}', [ProvinciaController::class, 'update'], [AdminMiddleware::class]);
Router::get('/provincias/vista/{id}', [ProvinciaController::class, 'vista'], [AdminMiddleware::class]);
Router::post('/provincias/delete/{id}', [ProvinciaController::class, 'delete'], [AdminMiddleware::class]);


// Rutas para Ciudades

Router::get('/ciudades', [CiudadController::class, 'index'], [AdminMiddleware::class]);
Router::get('/ciudades/edit/{id}', [CiudadController::class, 'edit'], [AdminMiddleware::class]);
Router::get('/ciudades/create', [CiudadController::class, 'create'], [AdminMiddleware::class]);
Router::post('/ciudades/store', [CiudadController::class, 'store'], [AdminMiddleware::class]);
Router::post('/ciudades/update/{id}', [CiudadController::class, 'update'], [AdminMiddleware::class]);
Router::get('/ciudades/vista/{id}', [CiudadController::class, 'vista'], [AdminMiddleware::class]);
Router::post('/ciudades/delete/{id}', [CiudadController::class, 'delete'], [AdminMiddleware::class]);

Router::get('/profile', [UserController::class, 'profile'], [AuthMiddleware::class], [AdminMiddleware::class]);





Router::get('/numerosdematricula', [NumerosController::class, 'editnumerosmat'], [AuthMiddleware::class]);
//$this->e($values[$f['nombre']])
//Router::get('/matriculas/informebajas', [MatriculaController::class, 'infobajas'], [AuthMiddleware::class]);

Router::get('/veradjunto/{doc}', [MatriculaController::class, 'mostraradjunto'], [AuthMiddleware::class]);
//Router::post('/veradjunto/{id}/{doc}', [MatriculaController::class, 'veradjunto'], [AuthMiddleware::class]);

// formulario “Olvidé mi contraseña”
Router::get('/password/forgot',            [AuthController::class, 'forgotForm']);
Router::post('/password/forgot',            [AuthController::class, 'sendForgotPassword']);

// formulario de reset con token
Router::get('/password/reset/([A-Za-z0-9]+)', [AuthController::class, 'resetPasswordForm']);
Router::post('/password/reset',               [AuthController::class, 'resetPassword']);


Router::get('/documentview/{id}/file/{file}', [UserController::class, 'showmyFile'], [AuthMiddleware::class]);

Router::get('/cuota', [ConfigController::class,  'consultar'], [AuthMiddleware::class]);

// routes.php

//$router->get('/wa-login', 'AuthWhatsappController@showQr');      // muestra vista con el QR
//$router->get('/wa-status', 'AuthWhatsappController@statusAjax'); // AJAX para comprobar estado
//$router->get('/wa-callback', 'AuthWhatsappController@callback'); // recibe el número y hace login




///////OJO NO BORRAR la llamada a /padrongeneral, aunque no se use sirve de ejemplo
Router::get('/padrongeneral', [DatosPersonalesController::class, 'adminbrowse'], [AuthMiddleware::class]);
Router::get('/api/datospersonales/data', [DatosPersonalesController::class, 'padronview'], [AuthMiddleware::class]);
/// Actividades de FASE 1 de control
Router::get('/aspirantes', [TramitesController::class, 'adminaspirantes'], [AuthMiddleware::class]);
Router::get('/api/aspirantes/data', [TramitesController::class, 'aspirantesview'], [AuthMiddleware::class]);
Router::get('/marcarrevisor/{id}', [TramitesController::class, 'revisorwrite'], [AuthMiddleware::class]); //[[]]
Router::post('/ponerrevisor/{id}', [TramitesController::class, 'fijarevisor'], [AuthMiddleware::class]);

//Actividades de fase 2
//grid de los que solicitaron control
Router::get('/solicitantes', [TramitesController::class, 'admin4revi'], [AuthMiddleware::class]);
//datasource para el grid
Router::get('/api/asp4revision/data', [TramitesController::class, 'm4review'], [AuthMiddleware::class]);

//boton para actividad para asignar el verificador
Router::get('/marcarverificador/{id}', [TramitesController::class, 'verificadorwrite'], [AuthMiddleware::class]);

//form para asignar el verificador
Router::post('/ponerverificador/{id}', [TramitesController::class, 'fijaverificador'], [AuthMiddleware::class]);

//boton para rechazar fase 1
Router::get('/rechazarfase1/{id}', [TramitesController::class, 'borrarrevisor'], [AuthMiddleware::class]);
//form para rechazar fase 1
Router::post('/rechazarrevision/{id}', [TramitesController::class, 'rechazarrevision'], [AuthMiddleware::class]);
//Router::get('/reenviarmail', [TramitesController::class, 'reenviarmails'], [AuthMiddleware::class]);

//control fase 2 - Agenda de citas
//grid de los que tienen verificador
Router::get('/agendarcita', [AgendaDeCitasController::class, 'adminagenda'], [AuthMiddleware::class]);
Router::get('/api/agenda/data', [AgendaDeCitasController::class, 'agendaview'], [AuthMiddleware::class]);

//funcion admin4revi
//template asp4revision.php
Router::get('/agendadecitas/create', [AgendaDeCitasController::class, 'nuevacita'], [AuthMiddleware::class]);
Router::post('/agendadecitas/store', [AgendaDeCitasController::class, 'store'], [AuthMiddleware::class]);
Router::get('/vercita/{id}', [AgendaDeCitasController::class, 'mostrarcita'], [AuthMiddleware::class]);
Router::get('/borrarcita/{id}', [AgendaDeCitasController::class, 'borrarcita'], [AuthMiddleware::class]);
Router::get('/mailcita/{id}', [AgendaDeCitasController::class, 'reenviarcita'], [AuthMiddleware::class]);

//Listos para revisión física [[]]
Router::get('/acontrolfisico', [TramitesController::class, 'paractrlfisico'], [AuthMiddleware::class]);
Router::get('//api/ctrlfisico/data', [TramitesController::class, 'fisicoview'], [AuthMiddleware::class]);
//datasource para el grid
//Router::get('/api/ctrlfisico/data', [TramitesController::class, 'm4fisico'], [AuthMiddleware::class]);

//datasource para agenda de citas - REMOVED DUPLICATE, using AgendaDeCitasController instead
//Router::get('/api/agenda/data', [TramitesController::class, 'm4fisico'], [AuthMiddleware::class]);

Router::get('/otorgamatricula', [DatosPersonalesController::class, 'browse4matricula'], [AuthMiddleware::class]);
Router::get('/api/otorgar/data', [DatosPersonalesController::class, 'padron4matriculaview'], [AuthMiddleware::class]);


//boton para actividad para asignar turno fecha y hora 
Router::get('/crearcita/{id}', [TramitesController::class, 'agendarcita'], [AuthMiddleware::class]);
//funcion verificadorwrite
//template asignarverificador.php 

//form para asignar el turno y enviar mail
Router::post('/marcaragenda/{id}', [TramitesController::class, 'fijaagenda'], [AuthMiddleware::class]);
//funcion fijaragenda (llama al update)

//boton para rechazar fase 2
Router::get('/rechazarfase2/{id}', [TramitesController::class, 'borrarverif'], [AuthMiddleware::class]);
//template rechazarrevision.php

Router::get('/aprobarfisico/{id}', [TramitesController::class, 'ok2matricular'], [AuthMiddleware::class]);
//template rechazarrevision.php

//form para rechazar fase 2
Router::post('/rechazarverificacion/{id}', [TramitesController::class, 'rechazarctrlfisico'], [AuthMiddleware::class]);
//funcion rechazarrevision (llama al update)







// [[]]
Router::get('/pararevision', [TramitesController::class, 'admin4verificacion'], [AuthMiddleware::class]);
Router::get('/api/rev4verificacion/data', [TramitesController::class, 'm4verificacion'], [AuthMiddleware::class]);
Router::get('/otorgar', [TramitesController::class, 'otorgarmatricula'], [AuthMiddleware::class]);



//grid de los que matriculados aprobados
Router::get('/activos', [DatosPersonalesController::class, 'activosbrowse'], [AuthMiddleware::class]);

//datasource para el grid
Router::get('/api/matricula/activos', [DatosPersonalesController::class, 'activosview'], [AuthMiddleware::class]);

Router::get('/conmatricula', [DatosPersonalesController::class, 'adminmatriculados'], [AuthMiddleware::class]);


// Rutas para comprobantes de pago
Router::get('/miscomprobantes', [ComprobantesPagoController::class, 'index'], [AuthMiddleware::class]);
Router::get('/api/comprobantespago/data', [ComprobantesPagoController::class, 'vermiscomprobantes'], [AuthMiddleware::class]);
// /api/comprobantespago/data
Router::get('/vercomprobante/{id}', [ComprobantesPagoController::class, 'edit'], [AuthMiddleware::class]);
Router::get('/comprobantespago/create/{id}', [ComprobantesPagoController::class, 'create'], [AuthMiddleware::class]);
Router::post('/comprobantespago/store/{id}', [ComprobantesPagoController::class, 'store'], [AuthMiddleware::class]);
Router::post('/comprobantespago/update/{id}', [ComprobantesPagoController::class, 'update'], [AuthMiddleware::class]);
Router::get('/comprobantespago/vista/{id}', [ComprobantesPagoController::class, 'vista'], [AuthMiddleware::class]);
Router::get('/quitarpago/{id}', [ComprobantesPagoController::class, 'delete'], [AuthMiddleware::class]);

// Rutas para editar y actualizar los datos personales del usuario
Router::get('/datospersonales/edit/{id}', [DatosPersonalesController::class, 'edit'], [AuthMiddleware::class]);
Router::post('/datospersonales/update/{id}', [DatosPersonalesController::class, 'update'], [AuthMiddleware::class]);

Router::get('/verdatospersonales/{id}', [DatosPersonalesController::class, 'vistaadmin'], [AuthMiddleware::class]);
//Router::get('/verdocumentacion/{id}', [DatosPersonalesController::class, 'generarPDF'], [AuthMiddleware::class]);

Router::get('/verdocumentacion/{id}', [MatriculaController::class, 'reviewmatri'], [AuthMiddleware::class]);
Router::get('/verlegajo/{id}', [TramitesController::class, 'vistalegajo'], [AuthMiddleware::class]);
Router::get('/api/legajo/data', [TramitesController::class, 'legajodata'], [AuthMiddleware::class]);
//reviewmatri

//'/api/datospersonales/data'

Router::get('/datospersonales/vista/{id}', [DatosPersonalesController::class, 'adminvista'], [AuthMiddleware::class]);
Router::get('/datospersonales/roleview/{id}', [DatosPersonalesController::class, 'rolevista'], [AuthMiddleware::class]);
Router::post('/datospersonales/roleupdate/{id}', [DatosPersonalesController::class, 'roleupdate'], [AuthMiddleware::class]);


Router::get('/matriculas', [MatriculaController::class, 'opcmatric'], [AuthMiddleware::class]);


Router::get('/opcionmatricula', [MatriculaController::class, 'matric_index'], [AuthMiddleware::class]);

Router::get('/menumatricula', [MatriculaController::class, 'menu_matric'], [AuthMiddleware::class]);





//opciones del menú de matriculacion
Router::get('/rematricula', [MatriculaController::class, 'edit_rematric'], [AuthMiddleware::class]);
Router::post('/rematricula/{id}', [MatriculaController::class, 'updaterem'], [AuthMiddleware::class]);

//Router::get('/rematricula', [MatriculaController::class, 'matriculacion'], [AuthMiddleware::class]);
Router::get('/primeramatricula', [MatriculaController::class, 'edit_first'], [AuthMiddleware::class]);
Router::post('/primeramatricula/{id}', [MatriculaController::class, 'updatefirst'], [AuthMiddleware::class]);

Router::get('/previamatricula', [MatriculaController::class, 'edit_prov'], [AuthMiddleware::class]);
Router::post('/previamatricula/{id}', [MatriculaController::class, 'updateprov'], [AuthMiddleware::class]);

Router::get('/titulodeotranacion', [MatriculaController::class, 'edit_extranjero'], [AuthMiddleware::class]);
Router::post('/titulodeotranacion/{id}', [MatriculaController::class, 'updateextranjero'], [AuthMiddleware::class]);


// Para links de uso general
// Rutas para editar y actualizar los datos de la matrícula del usuario
Router::get('/matriculas/edit/{id}', [MatriculaController::class, 'edit'], [AuthMiddleware::class]);
Router::post('/matriculas/update/{id}', [MatriculaController::class, 'update'], [AuthMiddleware::class]);

Router::get('/matriculas/informealtas', [MatriculaController::class, 'infoaltas'], [AuthMiddleware::class]);
Router::post('/matriculas/reportealtas', [MatriculaController::class, 'reportealtas'], [AuthMiddleware::class]);
// /matricula/reportealtas
Router::get('/matriculas/informebajas', [MatriculaController::class, 'infobajas'], [AuthMiddleware::class]);

Router::get('/estadomatricula/{id}', [MatriculaController::class, 'matristatus'], [AuthMiddleware::class]);
Router::post('/setmatricula/{id}', [MatriculaController::class, 'grabarmatricula'], [AuthMiddleware::class]);

Router::get('/matriculaestado', [MatriculaController::class, 'matristatus'], [AuthMiddleware::class]);


Router::get('/matriculanro', [NumerosController::class, 'editnumerosmat'], [AuthMiddleware::class]);
Router::post('/matriculanro/{id}', [NumerosController::class, 'updatenumerosmat'], [AuthMiddleware::class]);

//seccion bajas
Router::get('/menubajas', [MatriculaController::class, 'gestionbajas'], [AuthMiddleware::class]);
Router::get('/iniciarbaja', [MatriculaController::class, 'dardebaja'], [AuthMiddleware::class]);
Router::get('/consultadebajas', [DatosPersonalesController::class, 'bajasbrowse'], [AuthMiddleware::class]);

//datasource para el grid
Router::get('/api/matricula/debaja', [DatosPersonalesController::class, 'bajasview'], [AuthMiddleware::class]);



Router::post('/matriculas/baja', [MatriculaController::class, 'bajarmatricula'], [AuthMiddleware::class]);

Router::get('/bajas/data', [MatriculaController::class, 'databajas'], [AuthMiddleware::class]);


//Router::get('/bajas', [MatriculaController::class


// Seccion matriculados
Router::get('/matriculados', [DatosPersonalesController::class, 'adminmatriculados'], [AuthMiddleware::class]);
Router::get('/api/matriculados/data', [DatosPersonalesController::class, 'matriculadosview'], [AuthMiddleware::class]);


Router::get('/controlcobros', [ComprobantesPagoController::class, 'menucobranzas'], [AuthMiddleware::class]);
Router::get('/cobranzas', [ComprobantesPagoController::class, 'vercobranzas'], [AuthMiddleware::class]);
Router::get('/cobranzasmes', [ComprobantesPagoController::class, 'cobrosxmes'], [AuthMiddleware::class]);

Router::get('/cobranzas/data', [ComprobantesPagoController::class, 'datacobranzas'], [AuthMiddleware::class]);

Router::get('/historialpagos/{id}', [ComprobantesPagoController::class, 'cobrosxprofesional'], [AuthMiddleware::class]);
Router::get('/api/historialpagos/data', [ComprobantesPagoController::class, 'datacobrosxprof'], [AuthMiddleware::class]);


// PDF to PNG Converter routes

Router::get('/datarevprim', [MatriculaController::class, 'rev2prim'], [AuthMiddleware::class]);
Router::get('/datarevextr', [MatriculaController::class, 'rev2extranjero'], [AuthMiddleware::class]);
Router::get('/datarevrematric', [MatriculaController::class, 'rev2rematric'], [AuthMiddleware::class]);
Router::get('/datarevprovin', [MatriculaController::class, 'rev2prov'], [AuthMiddleware::class]);

Router::get('/comprobantespago/lote-colegio',           [ComprobantesPagoController::class, 'loteColegioForm'], [AuthMiddleware::class]);
Router::post('/comprobantespago/lote-colegio/preview',  [ComprobantesPagoController::class, 'loteColegioPreview'], [AuthMiddleware::class]);
Router::post('/comprobantespago/lote-colegio/confirm',  [ComprobantesPagoController::class, 'loteColegioConfirm'], [AuthMiddleware::class]);
Router::post('/comprobantespago/lote-colegio/cancel',   [ComprobantesPagoController::class, 'loteColegioCancel'], [AuthMiddleware::class]);


/*

*/
