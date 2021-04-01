<?php
namespace Src\controladores;

use Src\tablas\CnsltSermones;
use Src\controladores\Respuesta;

class CnsltSermonesCtrl {

    private $db;
    private $requestMethod;
    private $catalogo;
    private $resp;
    private $ConsultaSermones;
    private $accion;
    private $parametros;
    public function __construct($db, $requestMethod)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->resp=new Respuesta();
        $this->ConsultaSermones=new CnsltSermones($db);

        $this->accion='';
        try {
            $this->parametros = (array)json_decode(file_get_contents('php://input'));
            //$resultado['accion']=$ptrms['cn']->accion;
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
    
    public function procesa()
    {

        if ($this->requestMethod =='POST'  ){

            switch ($this->accion ) {
                case 'consulta sermones':
                    $response = $this->consultaSermones();
                    break;
                case 'consulta detalle sermon':
                    $response = $this->consultaDetalleSermon();
                    break;
                case 'consulta catalogos base':
                        $response = $this->consultaCatalogosBase();
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
    private function consultaCatalogosBase()
    {
        $result = $this->ConsultaSermones->obtenerCatalogosBase();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }
    private function consultaSermones()
    {
        $result = $this->ConsultaSermones->obtenerSermones($this->parametros->parametros);
        $total = $this->ConsultaSermones->obtenerTotalSermones();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado->registros=$result;
        $this->resp->resultado->conteo=$total;
        $response['body'] = json_encode($this->resp);
        return $response;
    }
    private function consultaDetalleSermon()
    {
        $result = $this->ConsultaSermones->consultaDetalleSermon($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }
    private function consultaDetalle()
    {
        $result = $this->ConsultaSermones->consultaDetalleCatalogo($this->parametros);
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