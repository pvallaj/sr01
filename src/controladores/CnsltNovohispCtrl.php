<?php
namespace Src\controladores;

use Src\tablas\CnsltNovohisp;
use Src\tablas\CnsltNarrativa;
use Src\tablas\CnsltSermones;
use Src\controladores\Respuesta;

class CnsltNovohispCtrl {

    private $db;
    private $requestMethod;
    private $resp;
    private $Consulta;
    private $Narrativas;
    private $Sermones;
    private $accion;
    private $parametros;
    public function __construct($db, $dbN, $dbS,  $requestMethod)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->resp=new Respuesta();
        $this->Consulta=new CnsltNovohisp($db);
        $this->Narrativas=new CnsltNarrativa($dbN);
        $this->Sermones=new CnsltSermones($dbS);

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
            
            switch ($this->accion ) {
                case 'consulta estructura':
                    $response = $this->consultaEstrucutra();
                    break;
                case 'consulta estructura x tomo':
                    $response = $this->consultaEstrucutraXTomo();
                    break;
                case 'consulta capitulo tomo':
                    $response = $this->consultaCapituloTomo();
                    break;
                case 'consulta obra escrita':
                    $response = $this->consultaObraEscrita();
                    break;
                case 'buscar terminos':
                    $response = $this->buscar();
                    break;
                case 'imagenes aleatorias':
                        $response = $this->imagenesAleatorias();
                        break;
                case 'referencias recurso':
                        $response = $this->consultaReferenciasRecurso();
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

    private function imagenesAleatorias()
    {
        $result = $this->Consulta->imagenesAleatorias($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function consultaEstrucutra()
    {
        $result = $this->Consulta->consultaEstructura();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }
    private function consultaEstrucutraXTomo()
    {
        $result = $this->Consulta->consultaEstructuraXTomo($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function consultaCapituloTomo()
    {
        $result = $this->Consulta->consultaCapituloTomo($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function consultaReferenciasRecurso()
    {
        $result = $this->Consulta->obtReferencias($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function consultaObraEscrita()
    {
        $result = $this->Consulta->consultaInformacionOE($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }
    private function buscar()
    {
        $rOE = $this->Consulta->buscar($this->parametros->parametros);
        $rNNH = $this->Narrativas->buscar($this->parametros->parametros);
        $rSNH = $this->Sermones->buscar($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=array(
            'obraescrita' => $rOE,
            'narrativas'=> $rNNH,
            'sermones'=> $rSNH,
        );
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