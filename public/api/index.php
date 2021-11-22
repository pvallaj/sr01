<?php
 /******************************************************************************************
 DESCRIPCIÓN:
 Servicio principal. este componente recibe todas las solicitudes de la aplicación "Historia de las literaturas en México"
 y las distribuye a otros componentes, de acuerdo a la acción que se desea realizar.
 Todas las peticiones deben tener un elemento llamado "accion", el cual debe tener dos partes,
 separadas por el carácter ":", el lado izquierdo identifica el componente y el lado derecho identifica
 la acción que realizara el componente.
 Cada componente se especializa en una de las siguientes secciones:
sección privada.
    Administrador de usuarios.
    Creación y consulta de noticias
Sección pública
    Consulta de las relaciones.
    Consulta de los sermones.
    Consulta generales.

La sección privada requiere que el usuario este registrado y con una sesión activa.
La parte publica puede ser accedida por todos.
 ******************************************************************************************/
header('Access-Control-Allow-Origin: *');
header('Access-Control-Request-Headers: *');
header("Access-Control-Allow-Headers: Content, X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}


ini_set('log_error', 'true');
ini_set('display_errors', 'false');
error_reporting(E_ALL);

require "../../bootstrap.php";
use Src\controladores\UsuarioCtrl;
use Src\controladores\CnsltNarrativaCtrl;
use Src\controladores\CnsltSermonesCtrl;
use Src\controladores\CnsltNovohispCtrl;
use Src\controladores\NoticiasCtrl;
use Src\controladores\util;

set_error_handler('exceptions_error_handler');

function exceptions_error_handler($severity, $message, $filename, $lineno) {
  if (error_reporting() == 0) {
    error_log(
        "PRECAUCION: ".PHP_EOL.
        $message.PHP_EOL.
        $filename.PHP_EOL.
        $lineno.PHP_EOL, 3, "log.txt");
  }
  if (error_reporting() & $severity) {
    error_log(
        "ERROR: ".PHP_EOL.
        $message.PHP_EOL.
        $filename.PHP_EOL.
        $lineno.PHP_EOL, 3, "log.txt");
  }
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
$parametros = (array)json_decode(file_get_contents('php://input'));

if($parametros==null){ 
    $parametros = array('cn'=> json_decode($_POST['cn']));
}


$Util = new Util($dbSys);
$Util->regEvento($parametros['cn']);
//error_log("inicio: ".$parametros['cn']->seccion.PHP_EOL, 3, "log.txt");
switch ($parametros['cn']->seccion) {
    case 'usuarios':
        $controller = new UsuarioCtrl($dbSys, null, null);
        $controller->usuario();
        break;
    case 'narrativas':
        $catalogo = null;
        if (isset($uri[2])) {
            $catalogo =  $uri[2];
        }
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $controller = new CnsltNarrativaCtrl($dbNNH, $requestMethod, $catalogo);
        $controller->procesa();
        break;
    case 'sermones':
        $catalogo = null;
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $controller = new CnsltSermonesCtrl($dbSNH, $requestMethod);
        $controller->procesa();
        break;
    case 'novohisp':
        $catalogo = null;
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $controller = new CnsltNovohispCtrl($dbSys,$dbNNH, $dbSNH, $requestMethod);
        $controller->procesa();
        break;
    case 'noticias':
        $controller = new NoticiasCtrl($dbSys, $parametros['cn']);
        $controller->procesa();
        break;
    default:
        header("HTTP/1.1 404 Not Found");
        exit();
}

