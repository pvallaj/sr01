<?php
namespace Src\tablas;

class CnsltCatalogo {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function obtenerCatalogo($catalogo)
    {

        switch ($catalogo) {
            case 'palabras':
                $statement = "SELECT idPalabra, palabra, descrip FROM cat_palabras2;";
                break;

            case 'categoria':
                    $statement = "SELECT id_clasificacion, categoria, `descripción` FROM cat_clasificacion;";
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



  
}