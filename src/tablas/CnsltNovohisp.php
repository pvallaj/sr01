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

    public function buscar($parametros)
    {
        
        $statement = "SELECT * FROM info_oe WHERE MATCH (etiquetas, descripcion) AGAINST (:terminos IN NATURAL LANGUAGE MODE);;";
        error_log("NH : ".json_encode($parametros).PHP_EOL, 3, "logs.txt");
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