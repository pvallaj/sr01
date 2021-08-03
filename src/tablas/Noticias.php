<?php
namespace Src\tablas;
use PDO;

class Noticias {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }


    public function obtenerTodasNoticiasActivas()
    {
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
        $rs=new \stdClass();
        
        if($p->nombre_archivo){

            $ruta_archivo="./img_noticias/".$p->id.".jpg";
            try {   
                if (file_exists(realpath($ruta_archivo))) {
                    unlink(realpath($ruta_archivo));
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
            $ruta='./img_noticias/';
            error_log("Imagen a borrar: ".realpath($ruta.$id.'_'.$res[0]['imagen']).PHP_EOL, 3, "log.txt");
            
            if (file_exists(realpath($ruta.$id.'_'.$res[0]['imagen']))) {
                unlink(realpath($ruta.$id.'_'.$res[0]['imagen']));
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
  
    public function crearNoticiaPrueba($p)
    {
        $rs=new \stdClass();
        $rs->resultado=new \stdClass();

        $s_id = $this->db->prepare('SELECT MAX(IFNULL(id,0))+1 as id FROM noticias');
        $s_id->execute();
        $regs=$s_id->fetch(PDO::FETCH_ASSOC);
        print_r($regs);
        $id=$regs['id'];
        error_log("IDN : ".$id." en ".count($regs)."<<<<".PHP_EOL, 3, "log.txt");
        

        return  $rs;
    }
}