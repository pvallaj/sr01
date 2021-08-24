<?php
namespace Src\controladores;
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
class Respuesta {
    /*****************************************************************************************
    * Descripción: Clase para una respuesta estandar
    *
    ******************************************************************************************/
    public $ok = null;
    public $message = null;
    public $resultado = null;

    public function responde($ok, $message, $resultado){
        $this->ok = $ok;
        $this->message = $ok;
        $this->resultado = $resultado;
    }
}