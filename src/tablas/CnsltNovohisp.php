<?php
namespace Src\tablas;

class CnsltNovohisp {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }


    public function consultaEstructura()
    {
        
        $statement = "SELECT valor FROM informacion WHERE id=1;";

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

    public function consultaEstructuraXTomo($parametros)
    {
        $resultado= (object)null;

        $statement = "SELECT *
        FROM info_oe
        WHERE	
            etiquetas LIKE 'Tomo".$parametros->tomo.", capitulo%, estructura' 
        OR  etiquetas LIKE 'Tomo".$parametros->tomo.", seccion%, estructura';";
        
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->estructura = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }

        $statement = "select *
        from info_oe
        WHERE	
        etiquetas LIKE 'Tomo".$parametros->tomo.", portadaTomo'";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->imagenes = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }

        return  $resultado;
    }

    public function consultaCapituloTomo($parametros)
    {
        $resultado= (object)null;

        $statement = "SELECT 0 id, tipo, referencia, texto, capitulo, etiquetas, descripcion
        FROM info_oe
        WHERE	
            etiquetas LIKE '".$parametros->capitulo.", portada'
UNION            
SELECT id, tipo, referencia, texto, capitulo, etiquetas, descripcion
        FROM info_oe
        WHERE	
            etiquetas LIKE '".$parametros->capitulo.", contenido';";
        
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->capitulo = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }

        return  $resultado;
    }

    public function consultaInformacionOE($parametros)
    {
        
        $statement = "SELECT id, tipo, referencia, texto, capitulo, etiquetas, descripcion, RAND() as orden  
        FROM info_oe 
        WHERE 
        MATCH (etiquetas, descripcion, texto, capitulo) 
        AGAINST (:terminos IN NATURAL LANGUAGE MODE)
        ORDER BY orden;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':terminos' => $parametros->terminos));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }
        return  $res;
    }

    public function buscar($parametros)
    {
        
        $statement = "SELECT id, tipo, referencia, texto, capitulo, etiquetas, descripcion 
        FROM info_oe 
        WHERE 
        MATCH (etiquetas, descripcion, texto, capitulo) 
        AGAINST (:terminos IN NATURAL LANGUAGE MODE)
        ORDER BY tipo";
        /*$statement = "SELECT id, tipo, referencia, texto, capitulo, etiquetas, descripcion, concat_ws(etiquetas,'', descripcion, texto, capitulo) unido
        FROM info_oe 
        WHERE 
        lower(concat_ws(etiquetas,'', descripcion, texto, capitulo)) LIKE LOWER(:terminos)
        ORDER BY tipo";*/

        //error_log("NH : ".json_encode($parametros).PHP_EOL, 3, "logs.txt");
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':terminos' => '%'.$parametros->terminos.'%'));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }
        return  $res;
    }



  
}