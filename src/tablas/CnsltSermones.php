<?php
/*****************************************************************************************
    Descripción:
        Obtiene la información de la base de datos para la sección de SERMONES.
    Autor: Paulino Valladares Justo.
    Fecha creación: 18/01/2020
    Historial de correcciones:
    -----------------------------------------------------------------------------------------
    Fecha:
    Descripción:
******************************************************************************************/
namespace Src\tablas;

class CnsltSermones {

    private $db = null;

    public function __construct($db)
    {
        /*****************************************************************************************
            Descripción:
                constructr 
            Parametros:
                ninguno 
            Resultado:
                ninguno 
        ******************************************************************************************/
        $this->db = $db;
    }
    
    public function obtenerCatalogosBase()
    {
        /*****************************************************************************************
            Descripción:
                Obtiene todos los datos necesarios para el formulario de consulta. 
            Parametros:
                ninguno
            Resultado:
                un arreglo con las listas de:
                autores, impresores, autores de preliminares, dedicatarios, ciudad, titulo de libro (obra), ordern religiosa
        ******************************************************************************************/
        $resultado= (object)null;
        /*****************************************************************************************
        * Obtiene la lista de autores
        ******************************************************************************************/
        $statement = "SELECT distinct a.id_autor, CONCAT_WS(' ', a.autor_nombre, a.autor_particula, a.autor_apellido) as autor 
        FROM 
            autores AS a,
            sermones AS s
        WHERE 
            s.id_autor=a.id_autor
            AND a.autor_nombre is not null order BY a.Autor_nombre;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->autores = $statement->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        /*****************************************************************************************
        * Obtiene la lista de impresores
        ******************************************************************************************/
        $statement = "SELECT id_impresor, impresor_nombre FROM impresores order by impresor_nombre;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->impresores = $statement->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        /*****************************************************************************************
        * Obtiene la lista de autores de preliminares
        ******************************************************************************************/
        $statement = "SELECT distinct a.id_autor, CONCAT_WS(' ', a.autor_nombre, a.autor_particula, a.autor_apellido) as autor 
        FROM 
            autores AS a,
            sermones_preliminares AS s
        WHERE 
            s.id_autor=a.id_autor
            AND a.autor_nombre is not null order BY a.Autor_nombre;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->preliminares = $statement->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        /*****************************************************************************************
        * obtiene la lista de dedicatarios.
        ******************************************************************************************/
        $statement = "SELECT distinct CONCAT_WS(' ', trim(d.dedicatario_nombre), d.dedicatario_particula, d.dedicatario_apellido) AS autor
        FROM dedicatiarios AS d
        WHERE d.dedicatario_nombre IS NOT null
        ORDER BY CONCAT_WS(' ', trim(d.dedicatario_nombre), d.dedicatario_particula, d.dedicatario_apellido);";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->dedicatarios = $statement->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        /*****************************************************************************************
        * obtiene la lista de ciudades
        ******************************************************************************************/
        $statement = "SELECT DISTINCT ciudad FROM sermones WHERE ciudad IS NOT NULL order by ciudad;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->ciudad = $statement->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        /*****************************************************************************************
        * Obtiene la lista de obras 
        ******************************************************************************************/
        $statement = "SELECT id_libro,  libro_titulo FROM libros ORDER BY libro_titulo;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->obra = $statement->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        /*****************************************************************************************
        * Obtiene la lista de ordenes religiosas
        ******************************************************************************************/
        $statement = "SELECT distinct autor_orden FROM autores where autor_orden IS NOT null order by autor_orden;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->orden = $statement->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        return $resultado;
    }

    public function obtenerSermones($parametros)
    {
        /*****************************************************************************************
            Descripción:
                Obtiene la lista de sermones coincidentes con los prorametros especificados por el usuario.
                Cada uno de los parametros pueden contener información o no, en caso de que no tengan información 
                se ignoran en creación del filtro.
            Parametros:
                 Parametros de consulta: 
                    id_autor. es el identificador del autor seleccionado por el usuario.
                    autor. es el nombre o parte del nombre escrito del autor, escrito por el usuario.
                    titulo. Es el titulo de la obra seleccionada por el usuario
                    ano_ini. Es el año de inicio de consulta seleccionado por el usuario. Si no selecciona un  año el valor es 1610.
                    ano_fin . Es el año de fin de consulta seleccionado por el usario
                    id_preliminar. Es el identificador del autor de los preliminares.
                    id_dedicatario. Es el identificador del dedicatario.
                    orden. Es la orden religiosa.
                    tituloObra. Es el titulo de la obra.
            Resultado:
                 Una lista con los registros coincidentes.
        ******************************************************************************************/
        $select=" SELECT
        s.id_sermon,
        a.autor_apellido, a.autor_nombre,  a.autor_particula, a.autor_orden,
        s.titulo, s.ciudad, s.`Año` as anio
        ";

        $from="FROM
         autores as a,
         sermones as s ";
        
        $where="WHERE 
        a.id_autor=s.id_autor
        ";
        
        if($parametros->id_autor > 0){
        /*****************************************************************************************
        *   Se agrega el filtro del autor cuando viene con ID
        ******************************************************************************************/
                $where=$where." and a.id_autor=:id_autor 
                ";
                $arr_parametros['id_autor']= $parametros->id_autor;

        }
        
        if($parametros->autor!=null){
            /*****************************************************************************************
            *   Se agrega el filtro del autor cuando viene coomo nombre.
            ******************************************************************************************/
            $where=$where." and upper(concat_ws(' ', Autor_nombre, Autor_particula, Autor_apellido)) like upper(:autor) 
                ";
                $arr_parametros['autor']='%'.$parametros->autor.'%';
        }

        if($parametros->titulo!=null){
            /*****************************************************************************************
            *   Se agrega el filtro de titulo de la obra
            ******************************************************************************************/
            $where=$where." and upper(titulo) like upper(:titulo) 
                ";
                $arr_parametros['titulo']='%'.$parametros->titulo.'%';
        }
        if($parametros->anio!=0){
            /*****************************************************************************************
            *  Se agrega el filtro del año de publicación
            ******************************************************************************************/
            $where=$where." and s.`Año`=:anio
                    ";
                $arr_parametros['anio']=$parametros->anio;
        }else{
            if($parametros->anio_ini!=null && $parametros->anio_fin!=null){
                /*****************************************************************************************
                * Se agrega el filtro de rango de años
                ******************************************************************************************/
                $where=$where." and s.`Año` between :inicio and :fin 
                    ";
                $arr_parametros['inicio']=$parametros->anio_ini;
                $arr_parametros['fin']=$parametros->anio_fin;
            }
        }

        if($parametros->impresor!=null ){
            /*****************************************************************************************
            *   Se agrega el filtro de impresor
            ******************************************************************************************/
            $where=$where." and s.impresor = :impresor 
                ";
            $arr_parametros['impresor']=$parametros->impresor;
        }

        if($parametros->id_preliminar > 0){
            /*****************************************************************************************
            * Se agrega el filtro de identificador del preliminar.
            ******************************************************************************************/
            $from=$from.",
            (SELECT s.id_sermon, a.id_autor as id_preliminar 
        FROM 
            autores AS a,
            sermones_preliminares AS s
        WHERE 
            s.id_autor=a.id_autor
        ) sp ";
            $where=$where." and sp.id_sermon=s.id_sermon
                            and sp.id_preliminar=:id_preliminar 
            ";
            $arr_parametros['id_preliminar']= $parametros->id_preliminar;

        }else{
            if($parametros->preliminar!=null){
                $from=$from.",
            (SELECT s.id_sermon, a.id_autor as id_preliminar 
        FROM 
            autores AS a,
            sermones_preliminares AS s
        WHERE 
            s.id_autor=a.id_autor
            and upper(concat_ws(' ', a.autor_nombre, a.autor_particula, a.autor_apellido)) like upper(:autor_preliminar)
        ) sp ";
            $where=$where." and sp.id_sermon=s.id_sermon 
            ";
            $arr_parametros['autor_preliminar']= '%'.$parametros->preliminar.'%';
            }
        }

        if($parametros->dedicatario != null){
            /*****************************************************************************************
            *   Se agrega el filtro de dedicatarios.
            ******************************************************************************************/
            $from=$from.",
                dedicatiarios d
            ";
            $where=$where." and d.id_sermon =s.id_sermon
                            and upper(CONCAT_WS(' ', trim(d.dedicatario_nombre), d.dedicatario_particula, d.dedicatario_apellido))=upper(:dedicatario) 
                ";
            $arr_parametros['dedicatario']=$parametros->dedicatario;
        }

        if($parametros->orden !=null){
            /*****************************************************************************************
            *   Se agrega el filtro de orden religiosa
            ******************************************************************************************/
            $where=$where." and a.autor_orden=:orden 
                ";
            $arr_parametros['orden']=$parametros->orden;
        }

        if($parametros->tituloObra !=null){
            /*****************************************************************************************
            * Se agrega el filtro de titulo de la obra
            ******************************************************************************************/
            $from=$from.",
            sermones_libros sm
            ";
            $where=$where." and sm.id_sermon= s.id_sermon 
                            and sm.id_libro=:tituloObra 
                ";
            $arr_parametros['tituloObra']=$parametros->tituloObra;
        }

        if($parametros->ciudad !=null){
            /*****************************************************************************************
            * Se agrega el filtro de ciudad
            ******************************************************************************************/
            $where=$where."  
                            and s.ciudad=:ciudad 
                ";
            $arr_parametros['ciudad']=$parametros->ciudad;
        }

        if($parametros->thema !=null){
            /*****************************************************************************************
            * Se agrega el filtro de termino (palabra o palabras)
            ******************************************************************************************/
            $where=$where."  
                    and MATCH (thema, thema_referencia) AGAINST (:thema IN NATURAL LANGUAGE MODE) 
                ";
            $arr_parametros['thema']=$parametros->thema ;
        }

        if($parametros->grabado !=null){
            /*****************************************************************************************
            *   Se agrega el filtro de grabados
            ******************************************************************************************/
            $from=$from.",
                grabados gr
            ";
            $where=$where."  
                    and s.id_sermon=gr.id_sermon
                    and upper(gr.grabado_descripcion) like upper(:grabado)  
                ";
            $arr_parametros['grabado']='%'.$parametros->grabado.'%' ;
        }

        $statement = $select.$from.$where;
        try {
            
            error_log("CnslSermones. ----
            ".$statement.'
            ----'.PHP_EOL, 3, "logs.txt");
            $statement = $this->db->prepare($statement);
            if(count($arr_parametros)>0)
                $statement->execute($arr_parametros);
            else
                $statement->execute();
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    public function obtenerTotalSermones()
    {
        /*****************************************************************************************
            Descripción:
                Obtiene el total de registros de sermones existentes.
            Parametros:
                Ninguno
            Resultado:
                El total de registros de sermones de la base de datos.
        ******************************************************************************************/
        $statement=" SELECT COUNT(*) as total FROM autores as a, sermones as s WHERE a.id_autor=s.id_autor;";

        try {

            $statement = $this->db->prepare($statement);
            $statement->execute();
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    public function consultaDetalleSermon($parametros)
    {
        /*****************************************************************************************
            Descripción:
                Obtiene toda la información relacionada a un sermón 
            Parametros:
                id_sermon. identificador del sermón seleccionado por el usuario.
            Resultado:
                detalle bibliografico del sermón.
                detalle del libro relacionado al sermón.
                detalle de los preliminares del sermón.
                detalle del catálogo.
                detalle del grabado.
                detalle de los preliminares.
                detalle de los repositorios.

        ******************************************************************************************/
        $resultado= (object)null;

        $statement = "select 
        concat_ws(' ', Autor_nombre, Autor_particula, Autor_apellido) nombre, a.autor_orden,
        s.id_sermon, s.titulo, s.inicio_sermon, s.ciudad, i.impresor_nombre as impresor, s.Año as anio, 
        concat_ws(', ', s.thema_corr,s.thema_ref_corr) thema, 
        s.protesta_fe, s.digitalizado_en1, s.digitalizado_en2, s.digitalizado_en3
    from
        autores a,
        sermones s,
        impresores i
    where
        a.id_autor=s.id_autor
        and s.impresor=i.id_impresor
        and s.id_sermon=:id_sermon;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':id_sermon' => $parametros->id_sermon));
            $resultado->sermon = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $statement2 = "SELECT 
         concat_ws(' ', l.libro_autor_nombre, l.Libro_autor_particula, l.Libro_autor_apellido) as autor,
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
        //Preliminares
        $statement3 = "SELECT 
        p.preliminar_tipo, p.preliminar_titulo, p.orden_dentro_sermon,
        CONCAT_WS(' ', a.autor_nombre, a.autor_particula, a.autor_apellido) autor
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

        $statement4 = "SELECT 
                cs.Catalogo_nombre as catalogo, cs.numeracion, cs.cat_nombre_completo as catalogo_nombre
        FROM 
            sermones_catalogos AS cs,
            catalogos AS cg	
        WHERE 
            cs.Catalogo_nombre=cg.ID_Catalogo
            AND cg.id_catalogo IN (1,5,3,4,6)
                and cs.id_sermon=:id_sermon;";
        try {
            $statement4 = $this->db->prepare($statement4);
            $statement4->execute(array('id_sermon' => $parametros->id_sermon));
            $resultado->catalogos = $statement4->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $statement5 = "select id_grabado, grabado_descripcion as grabado
        FROM grabados WHERE id_sermon=:id_sermon;";
        try {
            $statement5 = $this->db->prepare($statement5);
            $statement5->execute(array('id_sermon' => $parametros->id_sermon));
            $resultado->grabados = $statement5->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        $statement6 = "SELECT 
                sr.clasificacion, sr.enlace_digitalizacion, r.repositorio_tipo 
            FROM 
                sermones_repositorios AS sr,
                repositorios r
            WHERE 
                sr.Repositorio_tipo = r.ID_Repositorio
                and sr.ID_Sermon=:id_sermon;";
        try {
            $statement6 = $this->db->prepare($statement6);
            $statement6->execute(array('id_sermon' => $parametros->id_sermon));
            $resultado->repositorios = $statement6->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        return $resultado;
    }

    /*public function consultaDetalleCatalogo($parametros)
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
    }*/

    public function buscar($parametros)
    {
        /*****************************************************************************************
            Descripción:
                Realiza la busqueda basada en palabras, se activa cuando el usuario utiliza la 
                herramienta de "buscar", en la parte superior de la pantalla.
                La consulta se realiza en forma de "Lenguaje natural", lo que implica que se tomará
                como coincidente cada palabra de la frace proporcionada, sin considerar mayusculas y minusculas, 
                ni acentos.
            Parametros:
                 terminos. son las palabras a buscar.
            Resultado:
                 Una lista con los sermones encontrados.
        ******************************************************************************************/
        $statement = "select 
        s.id_sermon,
        a.autor_apellido, a.autor_nombre,  a.autor_particula, a.autor_orden,
        s.titulo, s.ciudad, s.`Año` as anio
    from
        autores a,
        sermones s
    where
        a.id_autor=s.id_autor
		  AND (
				MATCH (Autor_apellido, Autor_nombre, Autor_particula) AGAINST (:terminos IN NATURAL LANGUAGE MODE)
				or
				MATCH (titulo) AGAINST (:terminos IN NATURAL LANGUAGE MODE)
			);";
            //error_log("NVH : ".$statement.PHP_EOL, 3, "logs.txt");
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(':terminos' => $parametros->terminos));
            $res = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $res;
        } catch (\PDOException $e) {
            error_log("ERROR: ".$e->getMessage().PHP_EOL, 3, "logs.txt");
            return null;
        }
        return  $res;
    }
   
}