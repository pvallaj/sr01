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
        //error_log("NH : ".json_encode($parametros).PHP_EOL, 3, "logs.txt");
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



  
}