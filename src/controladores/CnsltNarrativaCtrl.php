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
use Src\tablas\CnsltNarrativa;
use Src\controladores\Respuesta;

class CnsltNarrativaCtrl {
    /*****************************************************************************************
        Descripción:
            Esta clase ejecuta los procesos relacionados a la sección de relaciones o narrativas de proyecto.     
                     
    ******************************************************************************************/
    private $db;
    private $requestMethod;
    private $catalogo;
    private $resp;
    private $ConsultaCats;
    private $accion;
    private $parametros;
    public function __construct($db, $requestMethod, $catalogo)
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
                case 'consulta catalogo base':
                    $response = $this->consultaCatalogoBase();
                    break;
                case 'consulta narrativas':
                    $response = $this->consultaNarrativas();
                    break;
                case 'consulta detalle narrativa':
                    $response = $this->consultaDetalleNarrativa();
                    break;
                case 'consulta vinculos':
                    $response = $this->consultaVinculos();
                    break;
                case 'consulta contexto':
                    $response = $this->consultaContexto();
                    break;
                case 'consulta signos actorales':
                    $response = $this->consultaSignos();
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

    private function consultaCatalogoBase()
    {
        /*****************************************************************************************
            Descripción:
                Ejecuta el proceso que obtiene los catalogos para la consulta de las "relaciones". 
            Parametros:
                Ninguno
            Resultado:
                Los catalogos de
                * autores.
                * obras
                * Palabras clave
                * Clasificaciones
                * motivos
                * tipos de versos
                * tipos de accion
                * soportes 
        ******************************************************************************************/
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
        /*****************************************************************************************
            Descripción:
                ejecuta el proceso de consulta de RELACIONES.  
            Parametros:
                Toma los parametros de la petición
                del usuario, previamente procesadas en el constructor.
            Resultado:
                Una lista con las RELACIONES que cumplen los cliterios definidos por el usuario. 
        ******************************************************************************************/
        $result = $this->ConsultaCats->consultaNarrativas($this->parametros->parametros);
        $total = $this->ConsultaCats->consultaTotalNarrativas();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado->registros=$result;
        $this->resp->resultado->conteo=$total;
        $response['body'] = json_encode($this->resp);
        return $response;
    }
    private function consultaDetalleNarrativa()
    {
        /*****************************************************************************************
            Descripción:
                Ontiene toda la información relacionada a la RELACIÓN especificada por el usuario. 
            Parametros:
                Ninguno. Los parametros los toma de la petición del usuario, previamente procesadas 
                por el constructor.
            Resultado:
                Una estructura con la información de la relación. 
        ******************************************************************************************/
        $result = $this->ConsultaCats->consultaDetalleNarrativa($this->parametros->parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }


    private function consultaSignos()
    {
        /*****************************************************************************************
            Descripción:
                Ejecuta el proceso para la obtención de la infomación que construira el mapa de signos actorales. 
            Parametros:
                Ninguno. 
            Resultado:
                Una estructura con los signos actorales.
        ******************************************************************************************/
        $result = $this->ConsultaCats->consultaSignos();;
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function consultaVinculos()
    {
        /*****************************************************************************************
            Descripción:
                Ejecuta el proceso para la obtención de la infomación que construira el mapa de Vinculos actorales. 
            Parametros:
                Ninguno. 
            Resultado:
                Una estructura con los Vinculos actorales.
        ******************************************************************************************/
        $result = $this->ConsultaCats->consultaVinculos();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function consultaContexto()
    {
        /*****************************************************************************************
            Descripción:
                Ejecuta el proceso para la obtención de la infomación que construira el mapa de contexto actorales. 
            Parametros:
                Ninguno. 
            Resultado:
                Una estructura con los contexto actorales.
        ******************************************************************************************/
        $result = $this->ConsultaCats->consultaContexto();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function notFoundResponse()
    {
        /*****************************************************************************************
            Descripción:
                Genera una respuesta de "Pagina no encontrada". para los casos en los que la opción de proceso, no existe.
            Parametros:
                Ninguno. 
            Resultado:
                codigo de pagina no enontrada.
        ******************************************************************************************/
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = null;
        return $response;
    }


}