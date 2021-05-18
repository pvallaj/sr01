<?php
namespace Src\controladores;

use Firebase\JWT\JWT;
use Src\tablas\Noticias;
use Src\controladores\Respuesta;

class NoticiasCtrl {

    private $db;
    private $requestMethod;
    private $resp;
    private $Noticias;
    private $accion;
    private $parametros;
    private $response;
    public function __construct($db, $parametros)
    {
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
        if(
            $this->parametros->accion!='obtener todas las noticias activas' &&
            $this->parametros->accion!='obtener Noticia'
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
        //error_log("Se valido el token: ".$this->parametros->accion.PHP_EOL, 3, "logs.txt");
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
        //if(!array_key_exists('Authorization', getallheaders())) return false;   
        $token = getallheaders()['authorization'];
        if(is_null($token)) $token = getallheaders()['Authorization'];
        
        error_log("headers AUTH: ".$token.PHP_EOL, 3, "logs.txt");
        if(is_null($token)){
            error_log("headers AUTH: Se requiere token y no se encontro".PHP_EOL, 3, "logs.txt");
            return false;
        } 

        $key=getenv('llave');
        try {
            $jwt = JWT::decode($token, $key,array('HS256'));
        } catch (\Throwable $th) {
            error_log("headers AUTH: token no valido: ".$th.'--'.PHP_EOL, 3, "logs.txt");
            return false;
        }

        if($jwt->exp < time()){
            error_log("headers AUTH: El token expiro".PHP_EOL, 3, "logs.txt");
            return false;
        } 
        $this->userId=$jwt->data->diusr;

        return true;
    }

   
}