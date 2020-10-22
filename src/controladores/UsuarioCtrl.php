<?php
namespace Src\controladores;

use Firebase\JWT\JWT;

use Src\tablas\UsuarioIF;
use Src\controladores\Respuesta;

class UsuarioCtrl {

    private $db;
    private $requestMethod;
    private $userId;
    private $resp;

    private $UsuarioIF;

    public function __construct($db, $requestMethod, $userId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->userId = $userId;
        $this->resp=new Respuesta();
        $this->UsuarioIF = new UsuarioIF($db);
    }
    
    public function obtHeaders($dato){
        foreach(getallheaders() as $campo => $valor){
            if($dato === $campo){
                return $valor;
            }
        }
        return null;
    }

    public function processRequest()
    {
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

    private function getAllUsers()
    {
        $result = $this->UsuarioIF->findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='correcto';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
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

    private function createUserFromRequest($datos)
    {

        if (! $this->validatePerson($datos)) {
            return $this->unprocessableEntityResponse();
        }
        if(count($this->UsuarioIF->buscarUsuario($datos['correo']))>=1){
            $this->resp->ok='false';
            $this->resp->message='El correo que desea registrar ya existe.';
            $response['body'] = json_encode($this->resp);
            return $response; 
        } 
        $insertados=$this->UsuarioIF->insert($datos);
        $regs=$response['status_code_header'] = 'HTTP/1.1 201 Created';

        $this->resp->ok='true';
        if($insertados>0){
            $this->resp->message='Registro insertado exitosamente';
        }else{
            $this->resp->message='No se inserto registro';
        }
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function updateUserFromRequest($id)
    {
        $result = $this->UsuarioIF->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }

        if (! $this->validatePerson($_POST)) {
            return $this->unprocessableEntityResponse();
        }
        $regs_act=$this->UsuarioIF->update($id, $_POST);
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

    private function deleteUser($id)
    {
        $result = $this->UsuarioIF->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $this->UsuarioIF->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $this->resp->ok='true';
        $this->resp->message='eliminación exitosa';
        $this->resp->resultado=null;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function validatePerson($input)
    {
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

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $this->resp->ok='false';
        $this->resp->message='información NO VALIDA';
        $this->resp->resultado=null;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
    
        $this->resp->ok='false';
        $this->resp->message='información NO encontrada';
        $this->resp->resultado=$result;
        $response['body'] = json_encode($this->resp);
        return $response;
    }


    //Registro 
    public function registrarAcceso()
    {
        
        $input = ((array) json_decode(file_get_contents('php://input'), TRUE))['cn'];
        $usuario = $this->UsuarioIF->buscarUsuario($input['usuario']);
        //var_dump($input);
        if ( !$usuario) {
            $this->resp->ok='false';
            $this->resp->message='Usuario / o contraseña incorrectos 1';
            $this->resp->resultado=null;
            
        }else{
            if(!password_verify($input['contrasena'], $usuario[0]['contrasena'])){
                $this->resp->message='Usuario / o contraseña incorrectos 2';
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
                        'usr' => $_POST['usuario'],
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
        header($response['status_code_header']);
        echo $response['body'];
        return $response;
    }
    //Usuario
    public function usuario(){
        $input = ((array) json_decode(file_get_contents('php://input'), TRUE))['cn'];

        if ($input['accion']=='actualizar'){
            $response = $this->updateUserFromRequest($this->userId);
        }
        if ($input['accion']=='crear'){
            $response = $this->createUserFromRequest($input);    
        }
        if ($input['accion']=='eliminar'){
            $response = $this->createUserFromRequest();    
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($this->resp);
        //$this->resp->resultado=null;
        header($response['status_code_header']);
        echo $response['body'];
        return $response;
    }

    public function validarToken()
    {
        if(!array_key_exists('token', getallheaders())) return null;   
        
        $token = getallheaders()['token'];
        
        if(is_null($token)) return false;

        $key=getenv('llave');
        try {
            $jwt = JWT::decode($token, $key,array('HS256'));
        } catch (\Throwable $th) {
            return false;
        }

        if($jwt->exp < time()) return false;
        $this->userId=$jwt->data->diusr;

        return true;
    }
}