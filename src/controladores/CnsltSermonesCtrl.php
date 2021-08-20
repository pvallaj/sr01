<?php
namespace Src\controladores;
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
use Src\tablas\CnsltSermones;
use Src\controladores\Respuesta;

class CnsltSermonesCtrl {
    /*****************************************************************************************
        Descripción:
            Esta clase ejecuta los procesos relacionados a la sección de sermones de proyecto.     
                     
    ******************************************************************************************/
    private $db;
    private $requestMethod;
    private $catalogo;
    private $resp;
    private $ConsultaSermones;
    private $accion;
    private $parametros;
    public function __construct($db, $requestMethod)
    {
        /*****************************************************************************************
            Descripción: 
                Constructor, realiza las siguidentes actividades:
                Define el bariable de conexión a la base de datos. 
                Extrae los valores de los parametros, definidos en la petición del usuario.
            Parametros
                $db. conexión a la base de datos.
                $requestMethod. es el tipo de requerimiento: POST o GET. En esta aplicación solo se
                usa el POST 
            resultado:
                Ninguno
        ******************************************************************************************/
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
        /*****************************************************************************************
            Descripción:
                ejecuta el proceso que obtiene los cátalogos que el usuario usara en sus consultas
                a la base de datos de sermones. 
            Parametros:
                 Ninguno.
            Resultado:
                Una estructura con los catalogos generados.
        ******************************************************************************************/
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
        /*****************************************************************************************
            Descripción:
                Ejecuta el proceso que busca los sermones coincidentes con los criterios de consulta 
                del usuario. 
            Parametros:
                criterios de consulta, son tomados de la petición del usuario y fueron previmente procesados 
                en el constructor de esta clase.
            Resultado:
                una lista con los registros de sermones que coinciden con los criterios especificados por
                el usuario.
        ******************************************************************************************/
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
        /*****************************************************************************************
            Descripción:
                Ejecuta el proceso que obtiene toda la información relacionada al sermón seleccionado 
                por el usuario 
            Parametros:
                identificador del sermón seleccionado. viene en la petición del usuario y  fue previamente 
                procesado por el constructor de está clase. 
            Resultado:
                una estructura con la información del sermón especificado.
        ******************************************************************************************/
        $result = $this->ConsultaSermones->consultaDetalleSermon($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }
    /*private function consultaDetalle()
    {
        $result = $this->ConsultaSermones->consultaDetalleCatalogo($this->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }*/

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