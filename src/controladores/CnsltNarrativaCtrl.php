<?php
namespace Src\controladores;

use Src\tablas\CnsltNarrativa;
use Src\controladores\Respuesta;

class CnsltNarrativaCtrl {

    private $db;
    private $requestMethod;
    private $catalogo;
    private $resp;
    private $ConsultaCats;
    private $accion;
    private $parametros;
    public function __construct($db, $requestMethod, $catalogo)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->catalogo = $catalogo;
        $this->resp=new Respuesta();
        $this->ConsultaCats=new CnsltNarrativa($db);

        $this->accion='';
        try {
            $this->parametros = (array)json_decode(file_get_contents('php://input'));
            $resultado['accion']=$this->parametros['cn']->accion;
            if(strpos($this->parametros['cn']->accion, ':')){
                $this->parametros=explode(':',$this->parametros['cn']->accion);
                $this->accion=$datos[0];
            }else{
                $this->accion=$this->parametros['cn']->accion;
            }
            $this->parametros = $this->parametros['cn'];
        } catch (\Throwable $th) {
            //throw $th;
        }
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
        if ($this->requestMethod =='POST'  ){
            //error_log("ctrl narrativas.".$this->accion.'----'.PHP_EOL, 3, "c:\\log\\log.txt");
            switch ($this->accion ) {
                case 'consulta catalogo base':
                    $response = $this->consultaCatalogoBase();
                    break;
                case 'consulta narrativas':
                        $response = $this->consultaNarrativas();
                        break;
                case 'consulta detalle narrativa':
                    $response = $this->consultaDetalleNarrativa();
                    break;
                default:
                    $response = $this->notFoundResponse();
                    break;
            }
        }      
        else
            $response = $this->notFoundResponse();

        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }
    public function procesaDetalle()
    {
        switch ($this->requestMethod) {
            case 'POST':            
                $response = $this->consultaDetalle();
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

    private function consultaCatalogoBase()
    {
        $result = $this->ConsultaCats->consultaCatalogosBase();;
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }
    private function consultaNarrativas()
    {
        $result = $this->ConsultaCats->consultaNarrativas($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }
    private function consultaDetalleNarrativa()
    {
        $result = $this->ConsultaCats->consultaDetalleNarrativa($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }
    private function consultaDetalle()
    {
        $result = $this->ConsultaCats->consultaDetalleCatalogo($this->parametros);;
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