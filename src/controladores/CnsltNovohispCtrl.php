<?php
namespace Src\controladores;

use Src\tablas\CnsltNovohisp;
use Src\controladores\Respuesta;

class CnsltNovohispCtrl {

    private $db;
    private $requestMethod;
    private $resp;
    private $Consulta;
    private $accion;
    private $parametros;
    public function __construct($db, $requestMethod)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->resp=new Respuesta();
        $this->Consulta=new CnsltNovohisp($db);

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
            //error_log("ctrl novohisp.".$this->accion.'----'.PHP_EOL, 3, "logs.txt");
            switch ($this->accion ) {
                case 'consulta estructura':
                    $response = $this->consultaEstrucutra();
                    break;
                case 'buscar terminos':
                    $response = $this->buscar();
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


    private function consultaEstrucutra()
    {
        $result = $this->Consulta->consultaEstructura();;
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }
    private function buscar()
    {
        $result = $this->Consulta->buscar($this->parametros->parametros);
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