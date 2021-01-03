<?php
namespace Src\tablas;

class Noticias {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }


    public function obtenerTodasNoticiasActivas()
    {
        
        $statement = "SELECT * FROM noticias WHERE estado=1 and now() between inicio and termino order by id desc;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }
        return  $res;
    }

    public function obtenerTodasNoticias()
    {
        
        $statement = "SELECT * FROM noticias order by id desc";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }
        return  $res;
    }

    public function obtenerNoticia($id)
    {
        
        $statement = "SELECT * FROM noticias where id=:id;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':id' => $id));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }
        return  $res;
    }

    public function crearNoticia($p)
    {
        $rs=new \stdClass();
        $statement = "INSERT INTO noticias(titulo, texto, imagen, ligaExterna,  inicio, termino) 
        values(:titulo, :texto, :imagen, :ligaExterna,  :inicio, :termino);";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
            ':titulo' =>        $p->titulo,
            ':texto' =>         $p->texto,
            ':imagen' =>        isset($p->imagen)?$p->imagen:null,
            ':ligaExterna' =>   isset($p->ligaExterna)?$p->ligaExterna:null,
            ':inicio' =>        $p->inicio,
            ':termino' =>       $p->termino,
            ));
            $rs->ok=true;
            $rs->message="correcto";
            $rs->id=$this->db->lastInsertId();
            return $rs;
            //return {'ok':true, 'message':'correcto', 'id':$this->db->lastInsertId()};
        } catch (\Exception $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            $rs->ok=false;
            $rs->message="Error interno. Favor de revisar el log";
            return $rs;
        }
        return  $res;
    }

    public function actualizarNoticia($p)
    {
        
        $statement = "UPDATE noticias SET 
            titulo=:titulo, 
            texto=:texto, 
            imagen=:imagen, 
            ligaExterna=:ligaExterna, 
            estado=:estado, 
            inicio=:inicio, 
            termino=:termino  
        WHERE id=:id";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
            'id' =>            $p->id,    
            'titulo' =>        $p->titulo,
            'texto' =>         $p->texto,
            'imagen' =>        $p->imagen,
            'ligaExterna' =>   $p->ligaExterna,
            'inicio' =>        $p->inicio,
            'termino' =>       $p->termino
            ));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }
        return  $res;
    }

    public function eliminarNoticia($id)
    {
        
        $statement = "DELETE FROM noticias where id=:id;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':id' => $id));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }
        return  $res;
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
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }
        return  $res;
    }
  
}