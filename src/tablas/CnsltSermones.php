<?php
namespace Src\tablas;

class CnsltSermones {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function obtenerAutores()
    {
        $statement = "
        SELECT id_autor, concat_ws(' ', Autor_nombre, Autor_particula, Autor_apellido) as autor 
        FROM autores 
        where autor_nombre is not null order by Autor_nombre;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function obtenerSermones($parametros)
    {
        if($parametros->id_autor > 0){
           
            error_log("csermones 0".$parametros->autor." - ".$parametros->pagtam." - ".$parametros->desde." - ".PHP_EOL, 3, "C:\\proyectos\\UNAM\\codigo\\Servidor\\log\\log.txt");
            $statement ="Select
                s.id_sermon,
                a.autor_apellido, a.autor_nombre,  a.autor_particula, 
                s.titulo, s.ciudad, s.`Año` as anio
            from
                autores as a,
                sermones as s
            where
                a.id_autor=s.id_autor
                and a.id_autor=:id_autor;";
            try {
                $statement = $this->db->prepare($statement);
                $statement->execute(
                    array('id_autor' => $parametros->id_autor/*,
                          'lmt'      => $parametros->pagtam,
                          'desde'    => $parametros->desde*/)
                );
                $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
                return $res;
            } catch (\PDOException $e) {
                exit($e->getMessage());
            }

        }else{
            error_log("csermones 1".$parametros->autor." - ".$parametros->pagtam." - ".$parametros->desde." - ".PHP_EOL, 3, "C:\\proyectos\\UNAM\\codigo\\Servidor\\log\\log.txt");
        $statement = "Select
                s.id_sermon,
                a.autor_apellido, a.autor_nombre,  a.autor_particula, 
                s.titulo, s.ciudad, s.`Año` as anio
            from
                autores as a,
                sermones as s
            where
                a.id_autor=s.id_autor
                and upper(concat_ws(' ', Autor_nombre, Autor_particula, Autor_apellido)) like upper(:autor)
            order by a.autor_apellido
            ;";
            try {
                $statement = $this->db->prepare($statement);
                $statement->execute(
                    array('autor' => '%'.$parametros->autor.'%')
                    /*    'pagina'      => $parametros->pagtam,
                        'desde'    => $parametros->desde)*/
                );
                $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
                return $res;
            } catch (\PDOException $e) {
                exit($e->getMessage());
                error_log("csermones 1 - Error: ".$e->getMessage()." - ".PHP_EOL, 3, "C:\\proyectos\\UNAM\\codigo\\Servidor\\log\\log.txt");
            }
        }
        
    }

    public function consultaDetalleSermon($parametros)
    {
        //error_log("csermones 2".$parametros->id_sermon.PHP_EOL, 3, "C:\\proyectos\\UNAM\\codigo\\Servidor\\log\\log.txt");
        $resultado= (object)null;

        $statement = "select 
        concat_ws(' ', Autor_apellido, Autor_nombre, Autor_particula) nombre, a.autor_orden,
        s.id_sermon, s.titulo, s.inicio_sermon, s.ciudad, s.impresor, s.Año as anio, s.thema, s.protesta_fe
    from
        autores a,
        sermones s
    where
        a.id_autor=s.id_autor
        and s.id_sermon=:id_sermon;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':id_sermon' => $parametros->id_sermon));
            $resultado->sermon = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $statement2 = "SELECT 
         concat_ws(' ', l.Libro_autor_apellido, l.libro_autor_nombre, l.Libro_autor_particula) as autor,
        l.libro_titulo, l.libro_ciudad, l.`libro _impresor` as libro_impresor, l.`Libro_año` as libro_anio
    FROM
        libros as l,
        sermones_libros as sl 
    WHERE
        l.id_libro=sl.id_libro
        AND sl.id_sermon=:id_sermon;";
        try {
            $statement2 = $this->db->prepare($statement2);
            $statement2->execute(array('id_sermon' => $parametros->id_sermon));
            $resultado->libro = $statement2->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $statement3 = "SELECT 
        p.preliminar_tipo, p.preliminar_titulo, p.orden_dentro_sermon,
        CONCAT_WS(' ', a.autor_apellido, a.autor_nombre, a.autor_particula) autor
    FROM 
        sermones_preliminares AS p,
        autores AS a
    WHERE 
        a.id_autor= p.ID_Autor 
        AND p.ID_Sermon=:id_sermon
    ORDER BY p.Orden_dentro_sermon;";
        try {
            $statement3 = $this->db->prepare($statement3);
            $statement3->execute(array('id_sermon' => $parametros->id_sermon));
            $resultado->preliminares = $statement3->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
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