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

use Firebase\JWT\JWT;
use Src\tablas\Noticias;
use Src\controladores\Respuesta;

class NoticiasCtrl {
     /*****************************************************************************************
        Descripción:
            Esta clase ejecuta los procesos relacionados a la sección privada de noticias de proyecto.     

    ******************************************************************************************/
    private $db;
    private $requestMethod;
    private $resp;
    private $Noticias;
    private $accion;
    private $parametros;
    private $response;
    public function __construct($db, $parametros)
    {
        /*****************************************************************************************
            Descripción: 
                Constructor, realiza las siguidentes actividades:
                Define el bariable de conexión a la base de datos. 
                Extrae los valores de los parametros, definidos en la petición del usuario.
            Parametros
                $db. conexión a la base de datos.
                $parametros. Parametros de la petición del usuario. 
            resultado:
                Ninguno
        ******************************************************************************************/
        $this->db = $db;
        $this->resp=new Respuesta();
        
        $this->Noticias=new Noticias($db);

        try {
            $this->parametros =  $parametros;
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    

    public function procesa()
    {
        /*****************************************************************************************
            Descripción:
                determina que acción se realiza, de acuerdo a los paramentros del usuario. 
                Aqui se determina que acción requiere de la validación de que existe un usuario
                registrado y con sesión valida.
            Parametros:
                ninguno, los datos vienen del contructor.
            Resultado:
                Regresa el resultado definido por cada proceso, en caso de que no se encuentre el proceso
                regresa un error de 'pagina no encontrada'. 
        ******************************************************************************************/
        if(
            //no se requiere validación de sesión.
            $this->parametros->accion!='obtener todas las noticias activas' &&
            $this->parametros->accion!='obtener Noticia' &&
            $this->parametros->accion!='crear Noticia Prueba'
        ){
            //validar autorización
            if(!$this->validarToken()){
                $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
                $this->resp->ok='false';
                $this->resp->message='Sesion no valida';
                $this->resp->resultado=null;
                $response['body'] = json_encode($this->resp);
                echo $response['body'];
                return;
            }
        }
        //error_log("Se valido el token: ".$this->parametros->accion.PHP_EOL, 3, "log.txt");
        switch ($this->parametros->accion ) {
            case 'obtener todas las noticias':
                $this->resultado(1, $this->Noticias->obtenerTodasNoticias());
                break;
            case 'obtener todas las noticias activas':
                $this->resultado(1, $this->Noticias->obtenerTodasNoticiasActivas());
                break;
            case 'obtener Noticia':
                $this->resultado(1, $this->Noticias->obtenerNoticia($this->parametros->parametros->id));
                break;
            case 'crear Noticia':
                $this->resultado(1, $this->Noticias->crearNoticia($this->parametros->parametros));
                break;
            case 'actualizar Noticia':
                $this->resultado(1, $this->Noticias->actualizarNoticia($this->parametros->parametros));
                break;
            case 'eliminar Noticia':
                $this->resultado(1, $this->Noticias->eliminarNoticia($this->parametros->parametros->id));
                break;
            default:
                $this->resultado(0, null);
        }
          
        header($this->response['status_code_header']);
        if ($this->response['body']) {
            echo $this->response['body'];
        }
    }

    public function resultado($tipo, $resultado){
        /*****************************************************************************************
            Descripción:
                Evalua el resultado y genera los encabezados para el tipo de respuesta. 
            Parametros:
                $tipo: 0 para un proceso no existente, 1 para un proceso. 
                $resultado. Es el resultado a evaluar.
            Resultado:
                La respuesta evaluada, con los headers adecuados según la respuesta.
        ******************************************************************************************/
        $this->resp->ok=$resultado->ok;
        $this->resp->message=$resultado->message;
        if($tipo==1){
            $this->response['status_code_header'] = 'HTTP/1.1 200 OK';
            if($resultado->ok===false){
                $this->resp->message='Error interno. Revise el registro de eventos.';
            }
            $this->resp->resultado=$resultado->resultado;
            $this->response['body'] = json_encode($this->resp);
        }else{
            $this->response['status_code_header'] = 'HTTP/1.1 404 Not Found';
            $this->response['body'] = null;
        }
        return null;
    }
  
    public function validarToken()
    { 
        /*****************************************************************************************
            Descripción:
                Determina si el token en la petición del usuario es valido, es decir, 
                si esta activo y si fue generado por este servicio. 
            Parametros:
                ninguno. Los datos necesarios se toman del encabezado de la petición. 
            Resultado:
                true. Para un token valido, false en otro caso. 
        ******************************************************************************************/
        if(!isset(getallheaders()['Authorization'])){
            if(!isset(getallheaders()['authorization'])){
                error_log("headers AUTH: Se requiere token y no se encontro".PHP_EOL, 3, "log.txt");
                return false;
            }else{
                $token = getallheaders()['authorization'];
            }
        }else{
            $token = getallheaders()['Authorization'];
        }
        error_log("headers AUTH: ".$token.PHP_EOL, 3, "log.txt");
        $key=getenv('llave');
        try {
            $jwt = JWT::decode($token, $key,array('HS256'));
        } catch (\Throwable $th) {
            error_log("headers AUTH: token no valido: ".$th.'--'.PHP_EOL, 3, "log.txt");
            return false;
        }

        if($jwt->exp < time()){
            error_log("headers AUTH: El token expiro".PHP_EOL, 3, "log.txt");
            return false;
        } 
        $this->userId=$jwt->data->diusr;

        return true;
    }

   
}