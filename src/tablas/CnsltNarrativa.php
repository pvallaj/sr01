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

    public function consultaDetalleNarrativa($parametros)
    {
        $resultado= (object)null;
        //error_log("cnarrativas.".$parametros->id_texto.'----'.PHP_EOL, 3, "/Users/paulinovj/proyectos/unam/sr01/log/log.txt");
        $statement = "select
        autor, obra,  
        CONCAT('Ed. ',editor) as editor, 
        CONCAT('Ed. Paleográfica', b.`ed paleográfica` ) AS ed_paleo, 
        CONCAT('Coor. ', b.director_coord) AS director_cor,
        CONCAT('Trad. ', b.traductor) AS director_cor,
        b.editor,
        b.ciudad,
        b.`año` as anio,
        CONCAT('en ', b.obra_anfitrion) AS obra_anfitrion,
        CONCAT('t. ', b.tomo) AS tomo,
        CONCAT('col. ', b.coleccion) AS coleccion,
        CONCAT('pp. ', b.`pp princeps`) AS pp
    FROM 
        cat_bibliografia AS b,
        texto AS t
    WHERE
        b.Id_bibliografia=t.Id_bibliografia
        AND t.Id_Texto=:id_texto	; ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id_texto' => $parametros->id_texto));
            $resultado->bibliograficos  = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
        
        //--------detalle de princeps

        $statement = "select 
        p.autor, 
        p.obra_tomo, 
        CONCAT('Librero: ', p.librero_casa) as librero, 
        p.ciudad, 
        p.a_costa_de, 
        p.ano as anio, 
        p.obra_tomo, 
        CONCAT('PP.', b.`pp princeps`) as princeps
    from 
        cat_bibliografia AS b,
        texto AS t,
        cat_princep as p
    where
        b.id_ed_princeps=p.id_princep
        and t.id_bibliografia=b.id_bibliografia
        and t.id_texto=:id_texto;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id_texto' => $parametros->id_texto));
            $resultado->princeps  = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            return $e->getMessage();
        }


        return $resultado;
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