<?php
ini_set('log_error', 'off');
ini_set('display_errors', 'off');
error_reporting(E_ALL);

require "../../bootstrap.php";
use Src\controladores\UsuarioCtrl;
use Src\controladores\CnsltNarrativaCtrl;
use Src\controladores\CnsltSermonesCtrl;
//ini_set('display_errors', 'off');


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
$parametros = (array)json_decode(file_get_contents('php://input'));
$postdata = file_get_contents("php://input");
$request = json_decode($postdata, true);
/*
error_log("index 9 -----.".json_encode($request).PHP_EOL, 3, "logs.txt");
error_log("index 10 -----.".PHP_EOL, 3, "logs.txt");
error_log("index 11 ".json_encode($_POST).PHP_EOL, 3, "logs.txt");
error_log("index 12 -----.".PHP_EOL, 3, "logs.txt");
error_log("index 13 ".json_encode($_REQUEST).PHP_EOL, 3, "logs.txt");
error_log("index 14 -----.".PHP_EOL, 3, "logs.txt");
error_log("index 13 ".json_encode($parametros).PHP_EOL, 3, "logs.txt");
*/
switch ($parametros['cn']->seccion) {
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
        //error_log("csermones 10. Recibiendo peticion de sermones ".PHP_EOL, 3, "c:\\log\\log.txt");
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
