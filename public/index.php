<?php
require "../bootstrap.php";
use Src\controladores\UsuarioCtrl;
use Src\controladores\CnsltCatalogoCtrl;
use Src\controladores\CnsltSermonesCtrl;
ini_set('display_errors', 'off');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

switch ($uri[1]) {
    case 'usuario':
        $userId = null;
        if (isset($uri[2])) {
            $userId = (int) $uri[2];
        }

        $requestMethod = $_SERVER["REQUEST_METHOD"];

        // pass the request method and user ID to the PersonController and process the HTTP request:
        $controller = new UsuarioCtrl($dbConnection, $requestMethod, $userId);
        $controller->processRequest();
        break;
    case 'registro':
            $userId = null;
            if (isset($uri[2])) {
                $userId = (int) $uri[2];
            }

            $controller = new UsuarioCtrl($dbConnection, null, null);
            $controller->registrarAcceso();
            break;
    case 'catalogos':
            $catalogo = null;
            if (isset($uri[2])) {
                $catalogo =  $uri[2];
            }

            $requestMethod = $_SERVER["REQUEST_METHOD"];
            $controller = new CnsltCatalogoCtrl($dbConnection, $requestMethod, $catalogo);
            $controller->procesa();
            break;
    case 'detalleCatalogos':
                $catalogo = null;
                if (isset($uri[2])) {
                    $catalogo =  $uri[2];
                }
                $requestMethod = $_SERVER["REQUEST_METHOD"];
                $controller = new CnsltCatalogoCtrl($dbConnection, $requestMethod, $catalogo);
                $controller->procesaDetalle();
                break;
    case 'sermones':
        $catalogo = null;
        $requestMethod = $_SERVER["REQUEST_METHOD"];
        $controller = new CnsltSermonesCtrl($dbConnection, $requestMethod);
        $controller->procesa();
        break;
    default:
        header("HTTP/1.1 404 Not Found");
        exit();
}
// the user id is, of course, optional and must be a number:
