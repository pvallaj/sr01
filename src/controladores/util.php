<?php
namespace Src\controladores;

class Util {

    
    public function __construct($db)
    {
        $this->db = $db;   
    }

    public function regEvento($parametros){
        //$_SERVER['REMOTE_ADDR']
        $statement = "INSERT INTO log (fecha, evento, informacion, origen)
        values(now(), :evento, :informacion, :origen);";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'evento' => $parametros->seccion.'/'.$parametros->accion,
                'informacion' => json_encode($parametros->parametros), 
                'origen' => $_SERVER['REMOTE_ADDR']
                ));
                //error_log("LOG registro creado exitosamente: ".PHP_EOL, 3, "logs.txt");
        } catch (\PDOException $e) {
            error_log("ERROR: al crear el registro".PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }

    }



}