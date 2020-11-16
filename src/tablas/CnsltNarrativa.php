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
        //------------------------------------------------------------------
        //autor
        $statement = "select distinct autor from cat_bibliografia;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->autores = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        //------------------------------------------------------------------
        //obra
        $statement = "select distinct autor, obra from cat_bibliografia;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->obras = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
        //------------------------------------------------------------------
        //tema o palabra clave.
        $statement = "SELECT idpalabra, palabra FROM cat_palabras2;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->tema = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        //------------------------------------------------------------------
        //clasificacion 
        $statement = "SELECT id_clasificacion AS id, concat(categoria,' - ', descripción) categoria FROM cat_clasificacion;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->clasificacion = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        //------------------------------------------------------------------
        //motivos 
        $statement = "SELECT id_motivo, motivo FROM cat_motivos;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->motivos = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        //------------------------------------------------------------------
        //tipo de versificacion 
        $statement = "SELECT id_versificacion, tipo_verso FROM cat_versificacion;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->versificacion = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        //------------------------------------------------------------------
        //tipo de accion 
        $statement = "SELECT id_tipo_accion, tipo_accion, descripcion FROM cat_tipoaccion;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->tipoaccion = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        //------------------------------------------------------------------
        //soporte 
        $statement = "	SELECT id_soporte, tipo_material FROM cat_soporte;";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute();
            $resultado->soporte = $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }

        return $resultado;
    }

    public function consultaNarrativas($parametros)
    {
        $arr_parametros = array();

        $select=" SELECT t.id_texto,
        t.nombre, t.narratio, t.ubicacion,
        cb.autor, cb.obra 
        ";

        $from=" FROM 
        Texto t,
        cat_bibliografia cb 
        ";
        
        $where="WHERE 
        t.Id_bibliografia=cb.Id_bibliografia
        ";
        

        if($parametros->autor != null){
            $where= $where." and cb.autor=:autor 
            ";
            $arr_parametros['autor']= $parametros->autor;
        }
        if($parametros->obra != null ){
            $where= $where." and cb.obra=:obra 
            ";
            $arr_parametros['obra']= $parametros->obra;
        }

        if($parametros->clasificacion != null){
            $from=$from.",cat_clasificacion cc, tx_clasificacion tc 
            ";
            $where=$where." and t.id_texto=tc.id_texto and tc.id_clasificacion=cc.id_clasificacion and tc.id_clasificacion=:clasificacion 
            ";
            $arr_parametros['clasificacion']= $parametros->clasificacion;
        }

        if($parametros->tema != null){
            $from=$from.",cat_palabras2 cp, tx_palabras2 tp 
            ";
            $where=$where." and t.id_texto=tp.id_texto and tp.idpalabras=cp.idpalabra and tp.idpalabras=:tema 
            ";
            $arr_parametros['tema']= $parametros->tema;
        }

        if($parametros->motivo != null){
            $from=$from.", tx_motivo tm 
            ";
            $where=$where." and t.id_texto=tm.id_texto and tm.id_motivo=:motivo 
            ";
            $arr_parametros['motivo']= $parametros->motivo;
        }

        if($parametros->tipoVerso != null){
            $from=$from.", tx_versificacion tv 
            ";
            $where=$where." and t.id_texto=tv.id_texto and tv.id_versificacion=:tipoVerso 
            ";
            $arr_parametros['tipoVerso']= $parametros->tipoVerso;
        }
        if($parametros->tipoAccion!= null){
            $from=$from.", tx_tipaccion tt 
            ";
            $where=$where." and t.id_texto=tt.id_texto and tt.id_tipo_accion=:tipoAccion 
            ";
            $arr_parametros['tipoAccion']= $parametros->tipoAccion;
        }
        if($parametros->soporte!= null){
            $from=$from.", tx_soporte ts 
            ";
            $where=$where." and t.id_texto=ts.id_texto and ts.id_soporte=:soporte 
            ";
            $arr_parametros['soporte']= $parametros->soporte;
        }
        $statement = $select.$from.$where;

        try {
            
            //error_log("Cnsltnarrativas. ----".$statement.'----'.PHP_EOL, 3, "logs.txt");
            
 
            //error_log("Cnsltnarrativas. ----".$arr_parametros['clasificacion'].'----'.PHP_EOL, 3, "logs.txt");
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
        AND t.Id_Texto=:id_texto    ; ";

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

        //--------detalle de CONTEXTO y Descripción Discursiva

        $statement = "  SELECT 
            t.argumento, 
            CONCAT('Nombre de la acción  dramatica: ', t.Accion_dramatica) as accion_dramatica,
            CONCAT('Nombre del discurso: ', t.denom_discurso) as nombre_discurso,
            'Contexto dramático: ' as contexto_dramatico,
            CONCAT('Marco anterior: ', t.Marco_anterior) as marco_anterior,
            CONCAT('Marco Posterior: ', t.Marco_posterior)  as marco_posterior,
            CONCAT('Formula de apertura: ', t.Formula_apertura) as formula_apertura,
            CONCAT('Formula de cierre: ', t.Formula_cierre) as formula_cierre,
            CONCAT('Ubicación: ', t.Ubicacion) as ubicacion,
            IF((t.dstrcn_discurso=1 and t.dstrcn_d_Prosa=0), 'Verso', 'Prosa') AS descripcion_discursiva,
            CONCAT('Personaje Receptor: ', t.prsnj_receptor) as receptor,
            CONCAT('Personaje Transmisor: ', t.prsnj_transmisor) as trasmisor,
            CONCAT('Personaje Emisor: ', t.prsnj_emisor) as emisor,
            CONCAT('Espacio abierto desde el que se cuenta: ', t.esp_dram_abierto) as ed_abierto,
            CONCAT('Espacio Cerrado desde el que se cuenta: ', t.esp_dram_cerrado) as ed_cerrado,
            CONCAT('Espacio Abierto referido: ', t.esp_dieg_abierto) as er_abierto,
            CONCAT('Espacio Cerrado referido: ', t.esp_dieg_cerrado) as er_cerrado,
            IF( (t.accion_diurna=0 and t.accion_nocturna=0 OR t.accion_diurna=1 and t.accion_nocturna=1), 'No especificado', IF((t.accion_diurna=1 and t.accion_nocturna=0), 'Diurna', 'Nocturna')) AS diurna_nocturna,
            CONCAT('Temporalidad dramática: ', t.`Tiempo dramatico`) as t_dramatico,
            'Momento Referido: ' as m_referido
        FROM texto AS t WHERE id_texto=:id_texto;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id_texto' => $parametros->id_texto));
            $resultado->contexto  = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            return $e->getMessage();
        }

        //--------tipo de accion

        $statement = "SELECT 
            cta.Id_tipo_accion, cta.tipo_accion, cta.descripcion
        FROM 
            tx_tipaccion AS tta,
            cat_tipoaccion AS cta
        where
            cta.Id_tipo_accion=tta.Id_tipo_accion
            AND tta.id_texto=:id_texto;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id_texto' => $parametros->id_texto));
            $resultado->tipoAccion  = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            return $e->getMessage();
        }

        //--------clasificación

        $statement = "  SELECT 
            cc.Id_clasificacion, cc.categoria, cc.`descripción` AS descripcion
        FROM 
            tx_clasificacion AS tc,
            cat_clasificacion AS cc
        where
            tc.Id_Clasificacion=cc.Id_clasificacion
            AND tc.id_texto=:id_texto;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id_texto' => $parametros->id_texto));
            $resultado->clasificacion  = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            return $e->getMessage();
        }

        //--------motivos

        $statement = "  SELECT 
            cm.id_motivo, cm.motivo
        FROM 
            tx_motivo AS tm,
            cat_motivos AS cm
        where
            tm.Id_motivo=cm.Id_motivo
            AND tm.id_texto=:id_texto;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id_texto' => $parametros->id_texto));
            $resultado->motivos  = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            return $e->getMessage();
        }

        //--------temas

        $statement = "  SELECT 
            cp.idpalabra, cp.palabra, cp.descrip AS descripcion
        FROM 
            tx_palabras2 AS tp,
            cat_palabras2 AS cp
        where
            tp.idPalabras=cp.idPalabra
            AND tp.id_texto=:id_texto;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id_texto' => $parametros->id_texto));
            $resultado->temas  = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            return $e->getMessage();
        }

        //--------Versificación
        $statement = "  SELECT 
        cv.id_versificacion, cv.tipo_verso
    FROM 
        tx_versificacion AS tv,
        cat_versificacion AS cv
    where
        tv.id_versificacion=cv.id_versificacion
        AND tv.id_texto=:id_texto;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id_texto' => $parametros->id_texto));
            $resultado->versificacion  = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            return $e->getMessage();
        }

        //--------soporte
        $statement = "SELECT 
            cs.id_soporte, cs.tipo_material
        FROM 
            tx_soporte AS ts,
            cat_soporte AS cs
        where
            ts.Id_soporte=cs.Id_soporte
            AND ts.id_texto=:id_texto;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id_texto' => $parametros->id_texto));
            $resultado->soporte  = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            return $e->getMessage();
        }

        //--------signos actor
        $statement = "SELECT 
            CONCAT('Gestos explícitos de los actores:', sa.gesto_dram_) AS gestos_dramaticos,
            CONCAT('Movimientos explícitos corporales de los actores:', sa.mov_dra_) AS movimientos_dramaticos,
            CONCAT('Modulaciones de Voz explícitas de los actores:', sa.mov_dra_) AS voz_dramaticos,
            CONCAT('Movimientos explícitos en la mirada de los actores:', sa.vista_dram) AS vista_dramaticos,
            CONCAT('Gestos implícitos de los actores:', sa.gesto_dram_no) AS gestos_dramaticos_no,
            CONCAT('Movimientos corporales implícitos de los actores:', sa.mov_dram_no) AS movimientos_dramaticos_no,
            CONCAT('Modulaciones implícitas en la voz de los actores:', sa.voz_dram_no) AS voz_dramaticos_no,
            CONCAT('Movimientos implícitos en la mirada de los actores:', sa.vista_dram_no) AS vista_dramaticos_no,
            CONCAT('Gestos dramáticos de los personajes referidos:', sa.gesto_dieg) AS gestos_dieg,
            CONCAT('Movimientos corporales de los personajes referidos:', sa.mov_dieg) AS movimientos_dieg,
            CONCAT('Modulaciones de voz de los personajes referidos:', sa.voz_dieg) AS voz_dieg,
            CONCAT('Movimientos en la mirada de los personajes referidos:', sa.vista_dieg) AS vista_dieg,
            CONCAT_WS('Signos actorales implícitos de los personajes referidos:', sa.gesto_dieg_no, ' ', sa.mov_dieg_no, ' ', sa.voz_dieg_no, ' ', sa.Vista_dieg_no) AS implicitos
        FROM 
            signos_actor sa
        where
            sa.Id_Texto=:id_texto;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id_texto' => $parametros->id_texto));
            $resultado->signos  = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            return $e->getMessage();
        }

        //--------vinculos
        $statement = "SELECT 
        CONCAT('Vínculos visuales: ', v.visuales) as visuales,
        CONCAT('Vínculos auditivos: ', v.auditivos) as auditivos,
        CONCAT('Vínculos con el presente de la acción dramática: ', v.presente_accion) as presente,
        CONCAT('Vínculos mediante referencias metadiscursivos: ', v.ref_discurso) as discurso,
        CONCAT('Vínculos mediante apelativos al receptor de la relación: ', v.apltvo_recep) as receptor,
        CONCAT('Vínculos mediante apelativos al espectador: ', v.apltvo_espect) as espectador
       from 
       vinculos as v
       where
        v.id_texto=:id_texto;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id_texto' => $parametros->id_texto));
            $resultado->vinculos  = $statement->fetchAll(\PDO::FETCH_ASSOC);
            
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