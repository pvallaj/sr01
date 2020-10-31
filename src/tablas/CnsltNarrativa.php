<?php
namespace Src\tablas;

class CnsltNarrativa {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function obtenerCatalogo($catalogo)
    {

        switch ($catalogo) {
            case 'Palabras':
                $statement = "SELECT idPalabra as id, palabra, descrip as descripcion FROM cat_palabras2;";
                break;

            case 'Categorias':
                    $statement = "SELECT id_clasificacion as id, categoria, `descripción` as descripcion FROM cat_clasificacion;";
                    break;
            default:
                return null;
        }

        try {
            $statement = $this->db->query($statement);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function consultaCatalogosBase()
    {
        $resultado= (object)null;

        $statement = "select distinct autor from cat_bibliografia;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->autores = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $statement = "select distinct autor, obra from cat_bibliografia;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->obras = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $statement = "SELECT id_clasificacion AS id, concat(categoria,' - ', descripción) categoria FROM cat_clasificacion;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->clasificacion = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        return $resultado;
    }

    public function consultaNarrativas($parametros)
    {
        
        $statement = "select t.id_texto,
            t.nombre, t.narratio, t.ubicacion,
            cb.autor, cb.obra
        from 
            Texto t,
            cat_bibliografia cb
        where
            t.Id_bibliografia=cb.Id_bibliografia ";
        if($parametros->autor != null){
            $statement= $statement.' and cb.autor=:autor ';
        }
        if($parametros->obra != null ){
            $statement= $statement.' and cb.obra=:obra ';
        }

        try {
            $statement = $this->db->prepare($statement);
            if($parametros->autor==null and $parametros->obra==null){
                $statement->execute();
            }
            if($parametros->autor!=null and $parametros->obra==null){
                $statement->execute(array('autor' => $parametros->autor));
            }
            if($parametros->autor!=null and $parametros->obra!=null){
                $statement->execute(array(
                    'autor' => $parametros->autor,
                    'obra' => $parametros->obra
                ));
            }
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    public function consultaDetalleCatalogo($parametros)
    {
        switch ($parametros->catalogo) {
            case 'palabras':
                $statement = "SELECT idPalabra as id, palabra, descrip as descripcion FROM cat_palabras2;";
                break;

            case 'categoria':
                    $statement = "select t.id_texto as id, t.nombre, t.narratio 
                    from 
                        texto t, 
                        tx_clasificacion tx
                    where
                        t.id_texto=tx.id_texto
                        and id_clasificacion= :cid ;";
                    break;
            default:
                return null;
        }

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('cid' => $parametros->id));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }



  
}