<?php
/*****************************************************************************************
 Descripción: Permite las consultas sobre la base de datos de "novohisp" o "sección de obra escrita".
 Autor: Paulino Valladares Justo.
 Fecha creación: 18/01/2020
 Historial de correcciones:
 -----------------------------------------------------------------------------------------
 Fecha:
 Descripción:
******************************************************************************************/
namespace Src\tablas;

class CnsltNovohisp {

    private $db = null;

    public function __construct($db)
    {
        /*****************************************************************************************
        * Contrcutor del objeto.
        ******************************************************************************************/
        $this->db = $db;
    }


    public function consultaEstructura()
    {
        /*****************************************************************************************
        * Obtiene la estructura de la obra escrita, del primer volumen siglo XVI.
        * Nota. Actualmente no se usa.
        ******************************************************************************************/
        $statement = "SELECT valor FROM informacion WHERE id=1;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }
        return  $res;
    }

    public function consultaEstructuraXTomo($parametros)
    {
        /*****************************************************************************************
            Descripción:
                Obtiene la estructura de la obra escrita, por tomo. 
            Parametros:
                 tomo. Es el tomo del cual se desea obtener la estructura: SXVI, SXVII y SXVII
            Resultado:
                 Una lista con los capitulos y secciones de la obra seleccionada.
                 Una lista con la portada y contraportada de la obra seleccionada.
        ******************************************************************************************/
        $resultado= (object)null;

        $statement = "SELECT *
        FROM info_oe
        WHERE	
            etiquetas LIKE 'Tomo".$parametros->tomo.", capitulo%, estructura' 
        OR  etiquetas LIKE 'Tomo".$parametros->tomo.", seccion%, estructura';";
        
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->estructura = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }

        $statement = "select *
        from info_oe
        WHERE	
        etiquetas LIKE 'Tomo".$parametros->tomo.", portadaTomo'";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->imagenes = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }

        return  $resultado;
    }

    public function consultaCapituloTomo($parametros)
    {
        /*****************************************************************************************
            Descripción:
                obtiene todos los elementos: textos, imagenes, videos, etc., relacionados a un capitulo. 
            Parametros:
                capitulo. capitulo seleccionado.
            Resultado:
                Una lista con los elementos relacionados al capitulo 
        ******************************************************************************************/
        $resultado= (object)null;

        //$correccion=explode(',',$parametros->capitulo);
        //$correccion=implode("%,%",$correccion);
        $parametros2=\str_replace(", ",",%",$parametros->capitulo);
        $statement = "SELECT 0 id, tipo, referencia, texto, capitulo, etiquetas, descripcion
        FROM info_oe
        WHERE	
            etiquetas = '".$parametros->capitulo.", portada'
UNION            
SELECT id, tipo, referencia, texto, capitulo, etiquetas, descripcion
        FROM info_oe
        WHERE	
            etiquetas like '".$parametros2.",%contenido%';";
        error_log("Sentencia: ".$statement.PHP_EOL, 3, "logs.txt");
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->capitulo = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }

        return  $resultado;
    }

    public function consultaInformacionOE($parametros)
    {
        /*****************************************************************************************
            Descripción:
                Realiza la busqueda de los TERMINOS especificados, en la sección de obra escrita.
                La busqueda se hace en modo 'lenguaje natural', lo que implica que si hay varias 
                se tomarán como coincidentes las palabras individuales.
                Si hay fraces encerradas entre ", se buscará como solo una sola parabra.
                El resultado se ordena en forma aleatoria en cada consulta hecha.
                Esta funcion esta pensada en la funcionalidad necesaria cuando un usuario selecciona
                un capitulo de la obra escrita.
            Parametros:
                terminos. son las parablas a buscar.
            Resultado:
                 Una lista con todos los elementos que coinciden con el termino buscado.
        ******************************************************************************************/
        $statement = "SELECT id, tipo, referencia, texto, capitulo, etiquetas, descripcion, RAND() as orden  
        FROM info_oe 
        WHERE 
        MATCH (etiquetas, descripcion, texto, capitulo) 
        AGAINST (:terminos IN NATURAL LANGUAGE MODE)
        ORDER BY orden;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':terminos' => $parametros->terminos));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }
        return  $res;
    }

    public function buscar($parametros)
    {
        
        /*****************************************************************************************
            Descripción:
                Realiza la busqueda de los TERMINOS especificados, en la sección de obra escrita.
                La busqueda se hace en modo 'lenguaje natural', lo que implica que si hay varias 
                se tomarán como coincidentes las palabras individuales.
                Si hay fraces encerradas entre ", se buscará como solo una sola parabra.
                Esta función esta pensando en la funcionalidad necesaria cuando el usuario usa la 
                herramienta "buscar"
            Parametros:
                terminos. son las parablas a buscar.
            Resultado:
                 Una lista con todos los elementos que coinciden con el termino buscado.
        ******************************************************************************************/
        $statement = "SELECT id, tipo, referencia, texto, capitulo, etiquetas, descripcion 
        FROM info_oe 
        WHERE 
        MATCH (etiquetas, descripcion, texto, capitulo) 
        AGAINST (:terminos IN NATURAL LANGUAGE MODE)
        ORDER BY tipo";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':terminos' => '%'.$parametros->terminos.'%'));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return $e->getMessage();
        }
        return  $res;
    }



  
}