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

use Src\tablas\UsuarioIF;
use Src\controladores\Respuesta;

class UsuarioCtrl {
    /*****************************************************************************************
        Descripción:
            Realiza las operaciones necesarias en la sección de usuario, esto es: altas, bajas, 
            cambios y listados de los usuarios de este sistema.
    ******************************************************************************************/
    private $db;
    private $requestMethod;
    private $userId;
    private $resp;

    private $UsuarioIF;

    public function __construct($db, $requestMethod, $userId)
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
                $userId. Es el identificador del usuario.
            resultado:
                Ninguno
        ******************************************************************************************/
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->userId = $userId;
        $this->resp=new Respuesta();
        $this->UsuarioIF = new UsuarioIF($db);
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

    public function processRequest()
    {
        /*****************************************************************************************
            Descripción:
                Determina el proceso a ejecutar de acuerdo a la petición del usuario. 
            Parametros:
                token. Es el identificador de la sesión y se toma de la petición del usuario. Si el token
                       no existe o no es valido, la petición no se realiza. 
            Resultado:
                cada proceso genera su propio resultado 
        ******************************************************************************************/
        switch ($this->requestMethod) {
            case 'GET':            
                if(!$this->validarToken()){
                    $response['status_code_header'] = 'HTTP/1.1 401 Unauthorized';
                    $this->resp->responde('false', 'Sesion novalida', null);
                    header($response['status_code_header']);
                    $response['body'] = json_encode($this->resp);
                    return $response;
                }
 
                if ($this->userId) {
                    $response = $this->getUser($this->userId);
                } else {
                    $response = $this->getAllUsers();
                };
                break;
            case 'POST':
                $input = ((array) json_decode(file_get_contents('php://input'), TRUE))['cn'];
                if ($input['accion']='actualizar'){
                    $response = $this->updateUserFromRequest($this->userId);
                }
                if ($input['accion']='crear'){
                    $response = $this->createUserFromRequest();    
                }
                break;
            case 'DELETE':
                $response = $this->deleteUser($this->userId);
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

    

    private function getUser($id)
    {
        
        $result = $this->UsuarioIF->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
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
    
        $this->resp->ok='false';
        $this->resp->message='información NO encontrada';
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    //----------------------------------------------------------------------------------------------

    //Registro 
    public function registrarAcceso()
    {
        
        $input = ((array) json_decode(file_get_contents('php://input'), TRUE))['cn']['parametros'];
        $usuario = $this->UsuarioIF->buscarUsuario($input['usuario']);
        //var_dump($input);
        if ( !$usuario) {
            $this->resp->ok='false';
            $this->resp->message='Usuario / o contraseña incorrectos ';
            $this->resp->resultado=null;
            
        }else{
            if(!password_verify($input['contrasena'], $usuario[0]['contrasena'])){
                $this->resp->message='Usuario / o contraseña incorrectos ';
                $this->resp->ok='false';
            }else{
                $this->resp->message='Registro exitoso';
                $this->resp->ok='true';
                $time = time();
                //echo $time;
                $token = array(
                    'iat' => $time, // Tiempo que inició el token
                    'exp' => $time + (60*60*24), // Tiempo que expirará el token (+1 dia)
                    'data' => [ // información del usuario
                        'usr' => $input['usuario'],
                        'diusr' => $usuario[0]['id'],
                        'nombre' => $usuario[0]['nombre'].' '.$usuario[0]['paterno'].' '.$usuario[0]['materno']
                    ]
                );
                $key=getenv('llave');
                $jwt = JWT::encode($token, $key);

                $this->resp->ok='true';
                $this->resp->message='Login correcto';
                $this->resp->resultado=(object)[
                    "token" => $jwt, 
                    "role" => $usuario[0]['role'], 
                    "nombre"=>$usuario[0]['nombre'].' '.$usuario[0]['paterno'].' '.$usuario[0]['materno'] 
                ];
            }
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($this->resp);
        $this->resp->resultado=null;
        return $response;
    }
    //Usuario
    public function usuario(){
        $input = ((array) json_decode(file_get_contents('php://input'), TRUE))['cn'];
        if($input['accion']!= 'crear'){
            if(!$this->validarToken() && $input['accion']!= 'acceso'){
                $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
                $this->resp->ok='false';
                $this->resp->message='Sesion no valida';
                $this->resp->resultado=null;
                $response['body'] = json_encode($this->resp);
                echo $response['body'];
                return $response; 
            }
        }
       
        switch ($input['accion'] ) {
            case 'crear':
                $response = $this->crearUsuario($input['parametros']);
                break;
            case 'actualizar':
                $response = $this->actualizarUsuario($input['parametros']);
                break;
            case 'eliminar':
                $response = $this->eliminarUsuario($input['parametros']['id']);
                break;
            case 'cambiar perfil':
                $response = $this->cambiarRole($input['parametros']['id'], $input['parametros']['role']);
                 break;
            case 'acceso':
                $response = $this->registrarAcceso();
                break;

            case 'obtener usuarios':
                $response = $this->obtenerUsuarios();
                break;
            
            default:
                $response = $this->notFoundResponse();
                break;
        }
       
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }

        
        return $response;
    }

    private function obtenerUsuarios()
    {
        $result = $this->UsuarioIF->obtenerUsuarios();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function crearUsuario($datos)
    {

        if (! $this->validatePersona($datos)) {
            return $this->noProcesable();
        }
        if(count($this->UsuarioIF->buscarUsuario($datos['correo']))>=1){
            $this->resp->ok='false';
            $this->resp->message='El correo que desea registrar ya existe.';
            $response['body'] = json_encode($this->resp);
            return $response; 
        } 
        $insertados=$this->UsuarioIF->crearUsuario($datos);
        $regs=$response['status_code_header'] = 'HTTP/1.1 201 Created';

        if($insertados>0){
            $this->resp->ok='true';
            $this->resp->message='Registro insertado exitosamente';
        }else{
            $this->resp->ok='false';
            $this->resp->message='No se inserto registro.';
        }
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function actualizarUsuario($parametros)
    {
        $result = $this->UsuarioIF->find($parametros['id']);
        if (! $result) {
            return $this->notFoundResponse();
        }

        if (! $this->validatePersona($parametros)) {
            return $this->noProcesable();
        }
        $regs_act=$this->UsuarioIF->actualizar($parametros);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        if($regs_act>0){
            $this->resp->message='Actualización exitosa';
        }else{
            $this->resp->message='No se encontraron coincidencias';
        }
        
        $this->resp->resultado=null;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function eliminarUsuario($id)
    {
        $result = $this->UsuarioIF->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $this->UsuarioIF->eliminar($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='eliminación exitosa';
        $this->resp->resultado=null;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function cambiarRole($id, $role)
    {
        $result = $this->UsuarioIF->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $this->UsuarioIF->cambiarRole($id, $role);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='eliminación exitosa';
        $this->resp->resultado=null;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function validatePersona($input)
    {
        //error_log("USUARIOS: ".$hash.'---'.$input['nombre'].PHP_EOL, 3, "log.txt");
        if (! isset($input['nombre'])) {
            return false;
        }
        if (! isset($input['paterno'])) {
            return false;
        }
        if (! isset($input['materno'])) {
            return false;
        }
        if (! isset($input['correo'])) {
            return false;
        }
        if (! isset($input['role'])) {
            return false;
        }
        if (! isset($input['telefono'])) {
            return false;
        }
        if (! isset($input['contrasena'])) {
            return false;
        }
        return true;
    }

    private function noProcesable()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $this->resp->ok='false';
        $this->resp->message='información NO VALIDA';
        $this->resp->resultado=null;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    public function validarToken()
    {
        //if(!array_key_exists('Authorization', getallheaders())) return false;   
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