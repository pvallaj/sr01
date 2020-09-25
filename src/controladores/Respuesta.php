<?php
namespace Src\controladores;

class Respuesta {

    public $ok = null;
    public $message = null;
    public $resultado = null;

    public function responde($ok, $message, $resultado){
        $this->ok = $ok;
        $this->message = $ok;
        $this->resultado = $resultado;
    }
}