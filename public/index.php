<?php
ini_set('log_error', 'off');
ini_set('display_errors', 'off');
error_reporting(E_ALL);

require "../bootstrap.php";
use Src\controladores\UsuarioCtrl;
use Src\controladores\CnsltNarrativaCtrl;
use Src\controladores\CnsltSermonesCtrl;
//ini_set('display_errors', 'off');


header('Access-Control-Allow-Origin: *');
header('Access-Control-Request-Headers: *');
header("Access-Control-Allow-Headers: Origin, Content, X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,X-Auth-Token, content-type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS") {
    die();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

//error_log("csermones 10.".'URL:'.$uri[1].'----'.PHP_EOL, 3, "logs.txt");
switch ($uri[1]) {
    case 'acceso':
            $userId = null;
            if (isset($uri[2])) {
                $userId = (int) $uri[2];
            }

            $controller = new UsuarioCtrl($dbSys, null, null);
            $controller->registrarAcceso();
            break;
    case 'usuario':
                $controller = new UsuarioCtrl($dbSys, null, null);
                $controller->usuario();
                break;
    case 'catalogos':
            $catalogo = null;
            if (isset($uri[2])) {
                $catalogo =  $uri[2];
            }

            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $controller = new CnsltCatalogoCtrl($dbNNH, $requestMethod, $catalogo);
            $controller->procesa();
            break;
    case 'detalleCatalogos':
                $catalogo = null;
                if (isset($uri[2])) {
                    $catalogo =  $uri[2];   
                }
                $requestMethod = $_SERVER["REQUEST_METHOD"];
                $controller = new CnsltCatalogoCtrl($dbNNH, $requestMethod, $catalogo);
                $controller->procesaDetalle();
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
        //error_log("csermones 10. Recibiendo peticion de sermones ".PHP_EOL, 3, "logs.txt");
        $catalogo = null;
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $controller = new CnsltSermonesCtrl($dbSNH, $requestMethod);
        $controller->procesa();
        break;
    default:
        header("HTTP/1.1 404 Not Found");
        exit();
}
// the user id is, of course, optional and must be a number:
