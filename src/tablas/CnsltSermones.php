<?php
namespace Src\tablas;

class CnsltSermones {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function obtenerSermones($parametros)
    {
        $statement = "Select
        a.autor_apellido, a.autor_nombre,  a.autor_particula, s.titulo, s.ciudad, s.`Año` as anio
    from
        autores as a,
        sermones as s
    where
        a.id_autor=s.id_autor
        and upper(autor_apellido) like upper(:autor);";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('autor' => '%'.$parametros->autor.'%'));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            exit($e->getMessage());
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