<?php
namespace Src\controladores;

use Src\tablas\CnsltCatalogo;
use Src\controladores\Respuesta;

class CnsltCatalogoCtrl {

    private $db;
    private $requestMethod;
    private $catalogo;
    private $resp;
    private $ConsultaCats;

    public function __construct($db, $requestMethod, $catalogo)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->catalogo = $catalogo;
        $this->resp=new Respuesta();
        $this->ConsultaCats=new CnsltCatalogo($db);
    }
    
    public function obtHeaders($dato){
        foreach(getallheaders() as $campo => $valor){
            if($dato === $campo){
                return $valor;
            }
        }
        return null;
    }

    public function procesa()
    {
        switch ($this->requestMethod) {
            case 'GET':            
                $response = $this->consultaCatalogo();
            break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function consultaCatalogo()
    {
        $result = $this->ConsultaCats->obtenerCatalogo($this->catalogo);;
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }

   
}