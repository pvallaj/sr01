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
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
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
            etiquetas LIKE '".$parametros->tomo.", capitulo%, estructura' 
        OR  etiquetas LIKE '".$parametros->tomo.", seccion%, estructura';";
        
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->estructura = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
            return $e->getMessage();
        }

        $statement = "select *
        from info_oe
        WHERE	
        etiquetas LIKE '".$parametros->tomo.", portadaTomo'";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->imagenes = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
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
            $statement = "SELECT 0 id, oe.tipo, oe.referencia, oe.referencia_2, oe.referencia_mini, oe.texto, oe.capitulo, oe.etiquetas, oe.descripcion,
            null as titulo, null as autor, null as fecha, rp.referencia AS referencia_portada
            FROM 
					info_oe as oe INNER JOIN 
					oe_ref_portada as rp ON oe.id=rp.id
            WHERE	
                oe.etiquetas = (
                SELECT replace(etiquetas, 'estructura','portada' ) FROM info_oe WHERE id=".$parametros->idc."
				)
            UNION            
            SELECT oe.id, oe.tipo, oe.referencia, oe.referencia_2, referencia_mini, oe.texto, oe.capitulo, oe.etiquetas, oe.descripcion,
				oe.titulo, oe.autor, oe.fecha, null as referencia_portada
            FROM 
					info_oe AS oe,
					info_refs AS r
            WHERE	
					oe.id=r.id
                    and oe.tipo in (1,2,3,4,5,6)
					AND r.id_ref=".$parametros->idc.";";
        error_log("Sentencia: ".$statement.PHP_EOL, 3, "log.txt");
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->capitulo = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
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
        $statement = "SELECT id, tipo, referencia, referencia_2, referencia_mini , texto, capitulo, etiquetas, descripcion, RAND() as orden  
        FROM info_oe 
        WHERE 
        MATCH (etiquetas, descripcion, texto, capitulo) 
        AGAINST (:terminos IN NATURAL LANGUAGE MODE)
        union
        SELECT id, tipo_recurso AS tipo,
        concat('./assets/fotos/catalogo/',id,'.jpg') as referencia,
        null as referencia_2,
        null as referencia_mini,
        null as texto, capitulo, etiquetas, descripcion , RAND() as orden
        FROM recursos_mmd
        WHERE 
            UPPER(titulo) LIKE UPPER(:termino2)
            OR UPPER(descripcion) LIKE UPPER(:termino2)
            OR UPPER(ciudad_estado) LIKE UPPER(:termino2)
            OR UPPER(anio_siglo) LIKE UPPER(:termino2)
        ORDER BY orden;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':terminos' => $parametros->terminos));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
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
        $statement = "SELECT 
            id, tipo, referencia, referencia_2, referencia_mini, texto, capitulo, etiquetas, descripcion,
            titulo, autor, fecha 
        FROM info_oe 
        WHERE 
        MATCH (etiquetas, descripcion, texto, capitulo) 
        AGAINST (:terminos IN NATURAL LANGUAGE MODE)";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                ':terminos' => '"'.$parametros->terminos.'"'
            ));

            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
            return $e->getMessage();
        }
        return  $res;
    }

    public function imagenesAleatorias($parametros)
    {
        
        /*****************************************************************************************
            Descripción:
                obtine la referencia a N imagenes de forma aleatoria
            Parametros:
                cantidad. número de imagenes a obtener.
            Resultado:
                 Una lista de N imagenes.
        ******************************************************************************************/
        $statement = "SELECT referencia, referencia_2, referencia_mini, descripcion, etiquetas, tipo,
         titulo, autor, fecha 
        FROM info_oe 
        WHERE tipo in (1,2,3,4,5,6) and id>=200
        ORDER BY RAND() LIMIT 5;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':cantidad' => $parametros->cantidad));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
            return $e->getMessage();
        }
        return  $res;
    }

    public function obtReferencias($parametros)
    {
        
        /*****************************************************************************************
            Descripción:
                obtine la referencia los capitulos a los que pertenece el recurso especifico.
            Parametros:
                id. identificador del recurso.
            Resultado:
                 lista de los identificadores de capitulos y sus descripciones.
        ******************************************************************************************/
        $statement = " SELECT 
                        r.id, oe.id, oe.texto AS capitulo
                FROM 
                    info_oe as oe,
                    info_refs AS r
                where
                        r.id_ref=oe.id
                        AND r.id=:id";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':id' => $parametros->id));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);

            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "log.txt");
            return $e->getMessage();
        }
        return  $res;
    }
  
}