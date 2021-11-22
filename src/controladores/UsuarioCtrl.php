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

    private function getUser($id)
    {
        /******************************************************************************************
        DESCRIPCIÓN:
            Obtiene los datos relacionados a un usuario
        PARAMETROS:
            $id. es el identificador del usuario a buscar.
        RESULTADO:
            Una estructura con los datos encontrados, null en otro caso.
        ******************************************************************************************/
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
        /******************************************************************************************
        DESCRIPCIÓN:
        Genera una respuesta de pagina no encontrada. Se ejecuta cuando no se encontro proceso 
        asociado a la petición realizada o bien cuando ocurrio un error durante el proceso.
        ******************************************************************************************/
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
        /******************************************************************************************
        DESCRIPCIÓN:
        Verifica los datos del usuario (usuario y contraseña) y en caso de ser validas, genera una nueva sesión.
        PARAMETROS
        $usuario. Es el nombre del usuario que se desea registrar. En este caso se usa el correo electrónico registrado como usuario.  
        $contrasena. Es la contraseña registrada por el usuario.  
        Estos datos vienen en el header de la petición POST. 
        ******************************************************************************************/
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
        /******************************************************************************************
        DESCRIPCIÓN:
        Determina que acción se realiza, de acuerdo a los parámetros del usuario. 
        Las acciones a las cuales puede responder este proceso son: 

        'crear'. 
        'actualizar'. 
        'Eliminar'. 
        'cambiar perfil'. 
        'acceso'. 
        'obtener usuarios'. 
        PARAMETROS 
            accion. Es la acción que desea realizar el usuario. 
            
            Cada acción tiene diferentes parámetros, por lo que se explican en cada proceso.  

        ******************************************************************************************/
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
        /******************************************************************************************
        DESCRIPCIÓN:
        
        Genera una lista de los usuarios existentes en la base de datos.

        RESULTADO 

        Una lista estructurada con los usuarios encontrados. 

        ******************************************************************************************/
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
        /******************************************************************************************
        DESCRIPCIÓN:
        Crea un nuevo registro de usuario, validando que no se repita, en este caso el identificador
        es el correo electronico, es decir, no se puede repetir.
        ******************************************************************************************/
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
        /******************************************************************************************
        DESCRIPCIÓN:
        Actualiza los datos del usuario por medio del identificador "id".
        PARAMETROS 
        Estructura con los datos del usuario que actualizaran los existentes. 

        RESULTADO 
        Estructura->ok con true si el registro fue exitoso, false si no fue posible crear el nuevo usuario. 
        ******************************************************************************************/
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
        /******************************************************************************************
        DESCRIPCIÓN:
        Elimina un usuario de la base de usuarios existentes.
        PARAMETROS 
        Id. Es el identificador del usuario a eliminar. 

        RESULTADO 
        Estructura->ok con true si el registro del usuario fue eliminado, false si no fue posible. 

        ******************************************************************************************/
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
        /******************************************************************************************
        DESCRIPCIÓN:
        cambia el rol de un usuario. en este caso solo existen 2 roles posibles: usuario y publicador.

        Un "usuario" es solamente una persona registrada, no tiene permiso de acciones especiales.

        Un "publicador" es un usuario que tiene permisos para administrar las noticias, es decir, para
        registrar, cambiar o eliminar noticias.

        PARAMETROS 
        Id. Es el identificador del usuario al cual se le cambiará el rol. 

        RESULTADO 
        Estructura->ok con true si el registro del usuario fue actualizado en su rol, false si no fue posible. 
        ******************************************************************************************/
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
        /******************************************************************************************
        DESCRIPCIÓN:
            Valida que todos los datos de una persona existan.
        PARAMETROS
            $input. es una estructura que contiene los datos de una persona.
        Resultado.
            true si todos los datos existen, false si falta alguno de los datos.
        ******************************************************************************************/
    
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
        /******************************************************************************************
        DESCRIPCIÓN:
        Genera una respuesta de error.
        Se ejecuta cuando no se encuentra un proceso relacionado a la petición realizada 
        o cuando ocurre un error durante el proceso.
        ******************************************************************************************/
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $this->resp->ok='false';
        $this->resp->message='información NO VALIDA';
        $this->resp->resultado=null;
        $response['body'] = json_encode($this->resp);
        return $response;
    }

    public function validarToken()
    {
        /******************************************************************************************
        DESCRIPCIÓN:
        verifica si la petición contiene u token llmado "Authorization" y en caso de existir calida 
        que sea valido.
        El token es requerido para todas las acciones relacionadas a los usuarios.
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