<?php
namespace Src\controladores;

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
                if($_FILES){
                    $directorio = "../../public_html/img_noticias/"; 
                    move_uploaded_file($_FILES['file']['tmp_name'], $directorio. $this->resp->resultado->id.'_'.$_FILES['file']['name']);
                }
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
            $this->resp->resultado=$resultado;
            $this->response['body'] = json_encode($this->resp);
        }else{
            $this->response['status_code_header'] = 'HTTP/1.1 404 Not Found';
            $this->response['body'] = null;
        }
        return null;
    }
  
    

   
}