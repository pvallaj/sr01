<?php
/*****************************************************************************************
Autor: Paulino Valladares Justo.
Registro de cambios
-------------------------------
Fecha:  
Versión: 1.0
Descripción: Liberación.
-------------------------------
Fecha:  
Versión: 
Descripción: 
-------------------------------
******************************************************************************************/
namespace Src\tablas;

class UsuarioIF {
    /*****************************************************************************************
    Descripción:
        Permite realizar todas las operaciones necesarias en la sección de usuarios, es decir:
        alta, bajas, cambios, etc de usuario.
******************************************************************************************/
    private $db = null;

    public function __construct($db)
    {
        /*****************************************************************************************
            Descripción:
                constructor 
            Parametros:
                $db. Objeto de conexión a la base de datos. 
            Resultado:
                ninguno 
        ******************************************************************************************/
        $this->db = $db;
    }

    public function obtenerUsuarios()
    {
        /*****************************************************************************************
            Descripción:
                Genera una lista con los usuarios existentes en la base de datos. 
            Parametros:
                Ninguno.
            Resultado:
                Una lista de los usuarios encontrados. 
        ******************************************************************************************/
        $statement = "
            SELECT 
                id, nombre, paterno, materno, correo, role, telefono
            FROM
                usuarios;
        ";

        try {
            $statement = $this->db->query($statement);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function find($id)
    {
        /*****************************************************************************************
            Descripción:
                Obtiene los datos del usuario especificado por su ID 
            Parametros:
                $id. es el identificador del usuario. 
            Resultado:
                una estructura que contiene los datos del usuarios, o null en caso de no encontrar datos. 
        ******************************************************************************************/
        $statement = "
            SELECT 
                id, nombre, paterno, materno, correo, role
            FROM
                usuarios
            WHERE id = ?;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($id));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }



    public function buscarUsuario($usuario)
    {
        /*****************************************************************************************
            Descripción:
                Obtiene los datos del usuario especificado por su correo 
            Parametros:
                $usuario. es el correo del usuario. 
            Resultado:
                una estructura que contiene los datos del usuarios, o null en caso de no encontrar datos. 
        ******************************************************************************************/
        $statement = "
            SELECT 
                id, nombre, paterno, materno, role, contrasena
            FROM
                usuarios
            WHERE correo = ?;
        ";
        //error_log("USUARIOS: ".$usuario.PHP_EOL, 3, "log.txt");
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($usuario));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }
    

    public function crearUsuario(Array $input)
    {
        /*****************************************************************************************
            Descripción:
                Crea un nuevo registro de usuario. 
            Parametros:
                $input. es una estructura que contiene toda la información del usuario: nombre, 
                apellido paterno, apellido materno, correo, role, telefono y contraseña.  
            Resultado:
                 El numero de registros insertados, 1 en caso e exito y 0 en caso de error.
        ******************************************************************************************/
        $statement = "
            INSERT INTO usuarios 
                (nombre, paterno, materno, correo, role, contrasena,telefono)
            VALUES
                (:nombre, :paterno, :materno, :correo, :role, :contrasena, :telefono);
        ";
        $hash = password_hash($input['contrasena'], PASSWORD_DEFAULT, [15]);
        //error_log("USUARIOS: ".$hash.'---'.$input['nombre'].PHP_EOL, 3, "log.txt");
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'nombre' => $input['nombre'],
                'paterno'  => $input['paterno'],
                'materno' => $input['materno'],
                'correo' => $input['correo'], //?? null  -- para omitir campo vacio
                'role' => $input['role'],
                'telefono' => $input['telefono'],
                'contrasena' => $hash
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function actualizar($datos)
    {
        if($datos['contrasena']==''){
            $statement = "
                UPDATE usuarios
                SET 
                    nombre = :nombre,
                    paterno  = :paterno,
                    materno = :materno,
                    correo = :correo,
                    role = :role
                WHERE id = :id;
            ";
        }else{
            $statement = "
                UPDATE usuarios
                SET 
                    nombre = :nombre,
                    paterno  = :paterno,
                    materno = :materno,
                    correo = :correo,
                    role = :role,
                    contrasena = :contrasena
                WHERE id = :id;
            ";
            $hash = password_hash($datos['contrasena'], PASSWORD_DEFAULT, [15]);
        }
        
        try {
            $statement = $this->db->prepare($statement);
            if($datos['contrasena']==''){
                $statement->execute(array(
                    'id' => (int) $datos['id'],
                    'nombre' => $datos['nombre'],
                    'paterno'  => $datos['paterno'],
                    'materno' => $datos['materno'] ,
                    'correo' => $datos['correo'] ,
                    'role' => $datos['role']
                ));
                //error_log("USUARIOS: Sin contraseña ".$datos['contrasena'].PHP_EOL, 3, "log.txt");
            }else{
                $statement->execute(array(
                    'id' => (int) $datos['id'],
                    'nombre' => $datos['nombre'],
                    'paterno'  => $datos['paterno'],
                    'materno' => $datos['materno'] ,
                    'correo' => $datos['correo'] ,
                    'role' => $datos['role'] ,
                    'contrasena' => $hash ,
                ));
                //error_log("USUARIOS: CON contraseña ".$datos['contrasena'].PHP_EOL, 3, "log.txt");
            }
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function eliminar($id)
    {
        $statement = "
            DELETE FROM usuarios
            WHERE id = :id;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id' => $id));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function cambiarRole($id, $role)
    {
        $statement = "UPDATE usuarios SET role=:role WHERE id = :id;";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id' => $id, 'role' => $role));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }
}