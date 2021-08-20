<?php
/*****************************************************************************************
 Autor: Paulino Valladares Justo.
 Registro de cambios
 -------------------------------
 Fecha:  
 Versión: 1.0
 Descripción: Liberación.
 -------------------------------
 Fecha:  
 Versión: 
 Descripción: 
 -------------------------------
 ******************************************************************************************/
namespace Src\controladores;

use Src\tablas\CnsltNovohisp;
use Src\tablas\CnsltNarrativa;
use Src\tablas\CnsltSermones;
use Src\controladores\Respuesta;

class CnsltNovohispCtrl {
    /*****************************************************************************************
        Descripción:
            Esta clase ejecuta los procesos relacionados a la sección de obra escrita de proyecto.     
                     
    ******************************************************************************************/
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
        /*****************************************************************************************
            Descripción:
                Constructor, realiza las siguidentes actividades.
                Define el bariable de conexión a la base de datos. 
                Extrae los valores de los parametros de usuario.
            Parametros:
                $db. Objeto de conexión a la base de datos.
                $requestMethod. es el tipo de requerimiento: POST o GET. En esta aplicación solo se
                usa el POST       
            Resultado:
                Ninguno
        ******************************************************************************************/
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
        /*****************************************************************************************
            Descripción:
                Busca en los header de la petición, un valor especifico. 
            Parametros:
                $dato -> Es el valor buscado. 
            Resultado:
                El valor del header buscado, en caso de que exista, si no existe regresa null.
        ******************************************************************************************/
        foreach(getallheaders() as $campo => $valor){
            if($dato === $campo){
                return $valor;
            }
        }
        return null;
    }

    public function procesa()
    {
        /*****************************************************************************************
            Descripción:
                determina que acción se realiza, de acuerdo a los paramentros del usuario. 
            Parametros:
                ninguno, los datos vienen del contructor.
            Resultado:
                Regresa el resultado definido por cada proceso, en caso de que no se encuentre el proceso
                regresa un error de 'pagina no encontrada'. 
        ******************************************************************************************/
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
                case 'tomos':
                    $response = $this->consultaTomos();
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
        /*****************************************************************************************
            Descripción:
                Obtiene una lista de imagenes, del stock de imagenes, en forma alatoria. 
            Parametros:
                Ningono. 
            Resultado:
                Ninguno. 
        ******************************************************************************************/
        $result = $this->Consulta->imagenesAleatorias($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

 
    private function consultaEstrucutraXTomo()
    {
        /*****************************************************************************************
            Descripción:
                Ejecuta el proceso que obtiene la estructura, es decir, las secciones y capitulos 
                del tomo especificado por el usuario. 
            Parametros:
                Ninguno. Los parametros se obtiene de la petición realizada por el usuario y 
                previamente procesadas por el constructor de esta clase.
            Resultado:
                Ninguno. 
        ******************************************************************************************/
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
        /*****************************************************************************************
            Descripción:
                Obtiene el detalle del capitulo seleccionado, es decir, su descripción y los recursos relacionados 
            Parametros:
                Ninguno. Los parametros se obtiene de la petición realizada por el usuario y 
                previamente procesadas por el constructor de esta clase. 
            Resultado:
                Una estructura con la descripción del capitulo y los recursos relacionados al mismo. 
        ******************************************************************************************/
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
        /*****************************************************************************************
            Descripción:
                Ejecuta el proceso que obtiene las referencias al recurso, es decir, en que capitulos 
                se encuentra el recurso especificado.
            Parametros:
                Ninguno. Los parametros se obtiene de la petición realizada por el usuario y 
                previamente procesadas por el constructor de esta clase. 
            Resultado:
                Una lista con los capitulos en donde se encuentra el recurso. 
        ******************************************************************************************/
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
        /*****************************************************************************************
            Descripción:
                Realiza la consulta de los terminos especificados por el usuario.
            Parametros:
                Los "terminos", es decir, las palabras especificadas por el usuario. 
            Resultado:
                Una lista con los recursos relacionados a los terminos especificados por el usuario.
        ******************************************************************************************/
        $result = $this->Consulta->consultaInformacionOE($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function consultaTomos()
    {
        /*****************************************************************************************
            Descripción:
                Ejecuta el proceso que obtiene los tomos de las obras.
            Parametros:
                Ninguno. 
            Resultado:
                Una estructura con los tomos por cada obra.
        ******************************************************************************************/
        $result = $this->Consulta->consultaTomos($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function buscar()
    {
        /*****************************************************************************************
            Descripción:
                Ejecuta los procesos de consulta de los terminos definidos por el usuarios. La
                Consulta se realiza en las bases de datos de sermones, de relaciones y en la
                información de obra escrita. 
            Parametros:
                Terminos o parablas de consulta. Son tomados de la petición del usuario.
            Resultado:
                 una estructura con los registros coincidentes con los terminos. La estructura 
                 está formada en los grupos de sermones, relaciones (narrativas) y obra escrita.
        ******************************************************************************************/
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
        /*****************************************************************************************
            Descripción:
                genera una respuesta de "pagina no encontrada". Se aplica cuando no se encuentra 
                El proceso a realizar. 
            Parametros:
                ninguno. 
            Resultado:
                Respuesta de error: pagina no encontrada. 
        ******************************************************************************************/
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }

   
}