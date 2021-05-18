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
            if($parametros->accion=="actualizar Noticia" || $parametros->accion=="crear Noticia"){
                $informacion= 'titulo: '.$parametros->parametros->titulo.','.
                              'texto: '.$parametros->parametros->texto.','.
                              'inicio: '.$parametros->parametros->inicio.','.
                              'liga: '.$parametros->parametros->ligaExterna.','.
                              'termino: '.$parametros->parametros->termino;
                $statement->execute(array(
                    'evento' => $parametros->seccion.'/'.$parametros->accion,
                    'informacion' => $informacion, 
                    'origen' => $_SERVER['REMOTE_ADDR']
                    ));
            }else{
                $statement->execute(array(
                    'evento' => $parametros->seccion.'/'.$parametros->accion,
                    'informacion' => json_encode($parametros->parametros), 
                    'origen' => $_SERVER['REMOTE_ADDR']
                    ));
            }
            
        } catch (\PDOException $e) {
            error_log("ERROR: al crear el registro".PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }

    }



}