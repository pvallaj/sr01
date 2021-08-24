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
namespace Src\tablas;
use PDO;

class Noticias {
    /*****************************************************************************************
    Descripción:
        Permite realizar todas las operaciones necesarias para los procesos de mantenimiento
        de la sección de usuarios.
******************************************************************************************/
    private $db = null;

    public function __construct($db)
    {
        /*****************************************************************************************
            Descripción:
                constructor 
            Parametros:
                $db. Objeto de conexión a la base de datos. 
            Resultado:
                ninguno 
        ******************************************************************************************/
        $this->db = $db;
    }


    public function obtenerTodasNoticiasActivas()
    {
        /*****************************************************************************************
            Descripción:
                genera una lista con todas las noticias activas. Las noticias se consideran activas si
                se encuentran en el periodo de vigencia. 
            Parametros:
                Ninguno 
            Resultado:
                Una lista con todas las noticias activas. 
        ******************************************************************************************/
        $rs=new \stdClass();
        $rs->resultado=new \stdClass();
        $statement = "SELECT id, titulo, texto, imagen, ligaExterna, DATE_FORMAT(inicio, '%d/%m/%Y') as inicio, DATE_FORMAT(termino, '%d/%m/%Y') as termino
         FROM noticias WHERE  now() between inicio and termino order by id desc;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $rs->ok=true;
            $rs->message="correcto";
            $rs->resultado=$res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
            $rs->ok=false;
            $rs->message="Error interno. Favor de revisar el log";
        }
        return  $rs;
    }

    public function obtenerTodasNoticias()
    {
        /*****************************************************************************************
            Descripción:
                Genera una lista con todas las noticias, sin importar si estan vigentes o no. 
            Parametros:
                Ninguno
            Resultado:
                Una lista con las noticias existentes. 
        ******************************************************************************************/
        $rs=new \stdClass();
        $rs->resultado=new \stdClass();
        $statement = "SELECT id, titulo, texto, imagen, ligaExterna, DATE_FORMAT(inicio, '%d/%m/%Y') as inicio, DATE_FORMAT(termino, '%d/%m/%Y') as termino
        FROM noticias order by id desc";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $rs->ok=true;
            $rs->message="correcto";
            $rs->resultado=$res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
            $rs->ok=false;
            $rs->message="Error interno. Favor de revisar el log";
        }
        return  $rs;
    }

    public function obtenerNoticia($id)
    {
        /*****************************************************************************************
            Descripción:
                Obtiene el detalle de la noticia especificada. 
            Parametros:
                id. Es el identificador de la noticia.
            Resultado:
                Toda la información relacionada a la noticia especificada.
        ******************************************************************************************/
        $rs=new \stdClass();
        $rs->resultado=new \stdClass();
        $statement = "SELECT * FROM noticias where id=:id;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':id' => $id));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $rs->ok=true;
            $rs->message="correcto";
            $rs->resultado=$res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
            $rs->ok=false;
            $rs->message="Error interno. Favor de revisar el log";
        }
        return  $rs;
    }

    public function crearNoticia($p)
    {
        /*****************************************************************************************
            Descripción:
                Crea un nuevo registro de noticia en la base de datos.
            Parametros:
                p. Contiene todos los datos de la noticia, a saber: 
                   titulo de la noticia, 
                   texto de la noticia,
                   imagen relacionada a la noticia,
                   liga o direccion URL relacionada a la noticia, 
                   inicio de vigencia de la noticia,
                   termino de la vigencia de la noticia
                El inicio y termino de la vigencia determinan el periodo de tiempo durante el 
                cual la noticia estará vigente.
            Resultado:
                estructura
                    ok -> con valor true si todo dalio bien y false en otro caso.
                    message -> 'correcto' si todo salio bien. 
        ******************************************************************************************/
        $rs=new \stdClass();
        $rs->resultado=new \stdClass();

        $s_id = $this->db->prepare('SELECT IFNULL(MAX(id),0)+1 as id FROM noticias');
        $s_id->execute();
        $regs=$s_id->fetch(PDO::FETCH_ASSOC);
        $id=$regs['id'];
        error_log("IDN : ".$id." en ".count($regs)."<<<<".PHP_EOL, 3, "log.txt");
        

        $statement = "INSERT INTO noticias(id, titulo, texto, imagen, ligaExterna,  inicio, termino) 
        values(:id, :titulo, :texto, :imagen, :ligaExterna,  :inicio, :termino);";
        
        try {
            $statement = $this->db->prepare($statement);
            $idn = $statement->execute(array(
            ':id'   =>          $id,
            ':titulo' =>        $p->titulo,
            ':texto' =>         $p->texto,
            ':imagen' =>        $id.".jpg",
            ':ligaExterna' =>   isset($p->ligaExterna)?$p->ligaExterna:null,
            ':inicio' =>        $p->inicio,
            ':termino' =>       $p->termino,
            ));

            if($p->nombre_archivo){
                $directorio = "/var/www/html/hlmnovohispana/api/img_noticias/"; 

                $data = explode(',', $p->file);
                $contenido = base64_decode($data[1]);
                $file=fopen($directorio.$id.'.jpg','wb');
                fwrite($file, $contenido);
                fclose($file);
            }

            $rs->ok=true;
            $rs->message="correcto";
            
        } catch (\Exception $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
            $rs->ok=false;
            $rs->message="Error interno. Favor de revisar el log";
        }

        return  $rs;
    }

    public function actualizarNoticia($p)
    {
        /*****************************************************************************************
            Descripción:
                Permite actualizados los datos que forman una noticia, incluyendo la imagen que acompaña
                la noticia. 
            Parametros:
                $p. Contiene todos los datos de la noticia.
                    La imagen viene en formato de base64 
            Resultado:
                estructura
                    ok -> con valor true si todo dalio bien y false en otro caso.
                    message -> 'correcto' si todo salio bien.  
        ******************************************************************************************/
        $rs=new \stdClass();
        
        if($p->nombre_archivo){

            $ruta_archivo="/var/www/html/hlmnovohispana/api/img_noticias/".$p->id.".jpg";
            try {   
                if (file_exists($ruta_archivo)) {
                    unlink($ruta_archivo);
                }
            } catch (\Throwable $th) {
                error_log("ERROR al borrar el archivo: ".$th->getMessage().PHP_EOL, 3, "log.txt");
            }

            $directorio = "img_noticias/"; 
            $data = explode(',', $p->file);
            $contenido = base64_decode($data[1]);
            $file=fopen($directorio.$p->id.'.jpg','wb');
            fwrite($file, $contenido);
            fclose($file);
        }

        $statement = "UPDATE noticias SET 
            titulo=:titulo, 
            texto=:texto, 
            imagen=:imagen, 
            ligaExterna=:ligaExterna, 
            inicio=:inicio, 
            termino=:termino  
        WHERE id=:id";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
            'id' =>            $p->id,    
            'titulo' =>        $p->titulo,
            'texto' =>         $p->texto,
            'imagen' =>        isset($p->imagen)?$p->id.'.jpg':null,
            'ligaExterna' =>   isset($p->ligaExterna)?$p->ligaExterna:null,
            'inicio' =>        $p->inicio,
            'termino' =>       $p->termino
            ));

            $rs->ok=true;
            $rs->message="correcto";
            

        } catch (\PDOException $e) {
            error_log("ERROR ACT imagen: ".$e->getMessage().PHP_EOL, 3, "log.txt");
            $rs->ok=false;
            $rs->message="Error interno. Favor de revisar el log";
        }
              
        
        return  $rs;
    }

    public function eliminarNoticia($id)
    {
        /*****************************************************************************************
            Descripción:
                Permite eliminar la noticia especificada por su ID. 
            Parametros:
                $id. Es el identificador de la noticia 
            Resultado:
                 estructura
                    ok -> con valor true si todo dalio bien y false en otro caso.
                    message -> 'correcto' si todo salio bien.  
        ******************************************************************************************/
        $rs=new \stdClass();
        $rs->resultado=new \stdClass();
        $statement = "SELECT imagen FROM noticias where id=:id;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':id' => $id));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
            return $e->getMessage();
        }

        try {
            $ruta="/var/www/html/hlmnovohispana/api/img_noticias/";
            
            if (file_exists($ruta.$res[0]['imagen'])) {
                unlink($ruta.$res[0]['imagen']);
             }
        } catch (\Throwable $th) {
            error_log("ERROR al borrar el archivo: ".$th->getMessage().PHP_EOL, 3, "log.txt");
        }

        $statement = "DELETE FROM noticias where id=:id;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':id' => $id));
            
            $rs->ok=true;
            $rs->message="El registro de noticia fue eliminado exitosamente";
            
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
            $rs->ok=false;
            $rs->message="Error interno. Favor de revisar el log";
        }
        
        return  $rs;
    }

    public function cambiarEstado($p)
    {
         /*****************************************************************************************
            Descripción:
                Permite cambiar el estado de la noticia especificada por su ID. 
            Parametros:
                $p->id. Es el identificador de la noticia 
            Resultado:
                 estructura
                    ok -> con valor true si todo dalio bien y false en otro caso.
                    message -> 'correcto' si todo salio bien.  
        ******************************************************************************************/
        $statement = "UPDATE noticias SET estado=:estado where id=:id;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':id' => $p->id, ':estado'=>$p->estado));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
            return $e->getMessage();
        }
        return  $res;
    }
  

}