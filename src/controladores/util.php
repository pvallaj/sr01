<?php
namespace Src\controladores;

class Util {
    /******************************************************************************************
    DESCRIPCIÓN:
    Componente que contiene funciones de uso general.
    ******************************************************************************************/
    public function __construct($db)
    {
        $this->db = $db;   
    }

    public function regEvento($parametros){
        /******************************************************************************************
        DESCRIPCIÓN:
        Registra un evento. 
        Un evento es una acción del usuario de la aplicación
        ******************************************************************************************/
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
            
            
        } catch (\PDOException $e) {
            error_log("ERROR: al crear el registro".PHP_EOL, 3, "log.txt");
            return $e->getMessage();
        }

    }



}