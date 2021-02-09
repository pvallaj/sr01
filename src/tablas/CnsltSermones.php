<?php
namespace Src\tablas;

class CnsltSermones {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function obtenerCatalogosBase()
    {
        $resultado= (object)null;
        //autores-----
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
        //impresores ----------
        $statement = "SELECT id_impresor, impresor_nombre FROM impresores;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->impresores = $statement->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        //Autor de preliminares ----------
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

        //Dedicatarios ----------
        $statement = "SELECT d.id_dedicatario, CONCAT_WS(' ', d.dedicatario_nombre, d.dedicatario_particula, d.dedicatario_apellido) AS autor
        FROM dedicatiarios AS d
        WHERE d.dedicatario_nombre IS NOT null;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->dedicatarios = $statement->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        //CIUDAD ----------
        $statement = "SELECT DISTINCT ciudad FROM sermones WHERE ciudad IS NOT NULL;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->ciudad = $statement->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        //obra ----------
        $statement = "SELECT id_libro,  libro_titulo FROM libros ORDER BY id_libro;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->obra = $statement->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        //orden religiosa ----------
        $statement = "SELECT distinct autor_orden FROM autores where autor_orden IS NOT null;";
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
                $where=$where." and a.id_autor=:id_autor 
                ";
                $arr_parametros['id_autor']= $parametros->id_autor;

        }
        
        if($parametros->autor!=null){
            $where=$where." and upper(concat_ws(' ', Autor_nombre, Autor_particula, Autor_apellido)) like upper(:autor) 
                ";
                $arr_parametros['autor']='%'.$parametros->autor.'%';
        }

        if($parametros->titulo!=null){
            $where=$where." and upper(titulo) like upper(:titulo) 
                ";
                $arr_parametros['titulo']='%'.$parametros->titulo.'%';
        }
        if($parametros->anio!=0){
            $where=$where." and s.`Año`=:anio
                    ";
                $arr_parametros['anio']=$parametros->anio;
        }else{
            if($parametros->anio_ini!=null && $parametros->anio_fin!=null){
                $where=$where." and s.`Año` between :inicio and :fin 
                    ";
                $arr_parametros['inicio']=$parametros->anio_ini;
                $arr_parametros['fin']=$parametros->anio_fin;
            }
        }

        if($parametros->impresor!=null ){
            $where=$where." and s.impresor = :impresor 
                ";
            $arr_parametros['impresor']=$parametros->impresor;
        }

        if($parametros->id_preliminar > 0){
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

        }

        if($parametros->id_dedicatario > 0){
            $from=$from.",
                dedicatiarios d
            ";
            $where=$where." and d.id_sermon =s.id_sermon
                            and d.id_dedicatario=:id_dedicatario 
                ";
            $arr_parametros['id_dedicatario']=$parametros->id_dedicatario;
        }

        if($parametros->orden !=null){

            $where=$where." and a.autor_orden=:orden 
                ";
            $arr_parametros['orden']=$parametros->orden;
        }

        if($parametros->tituloObra !=null){
            $from=$from.",
            sermones_libros sm
            ";
            $where=$where." and sm.id_sermon= s.id_sermon 
                            and sm.id_libro=:tituloObra 
                ";
            $arr_parametros['tituloObra']=$parametros->tituloObra;
        }

        if($parametros->ciudad !=null){
            $where=$where."  
                            and s.ciudad=:ciudad 
                ";
            $arr_parametros['ciudad']=$parametros->ciudad;
        }

        if($parametros->thema !=null){
            $where=$where."  
                    and MATCH (thema, thema_referencia) AGAINST (:thema IN NATURAL LANGUAGE MODE) 
                ";
            $arr_parametros['thema']=$parametros->thema ;
        }

        if($parametros->grabado !=null){
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
            
           /* error_log("CnslSermones. ----
            ".$statement.'
            ----'.PHP_EOL, 3, "logs.txt");*/
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

    public function consultaDetalleSermon($parametros)
    {
        
        $resultado= (object)null;

        $statement = "select 
        concat_ws(' ', Autor_apellido, Autor_nombre, Autor_particula) nombre, a.autor_orden,
        s.id_sermon, s.titulo, s.inicio_sermon, s.ciudad, s.impresor, s.Año as anio, 
        concat_ws(s.thema,', ', s.thema_referencia) thema, 
        s.protesta_fe
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
                cs.Catalogo_nombre as catalogo, cs.numeracion, cg.catalogo_nombre
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

    public function buscar($parametros)
    {
        
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
            return $e->getMessage();
        }
        return  $res;
    }
   
}