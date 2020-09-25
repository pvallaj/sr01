<?php
namespace Src\tablas;

class UsuarioIF {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findAll()
    {
        $statement = "
            SELECT 
                id, nombre, paterno, materno, correo, role
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
        $statement = "
            SELECT 
                id, nombre, paterno, materno, role, contrasena
            FROM
                usuarios
            WHERE correo = ?;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array($usuario));
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function insert(Array $input)
    {

        $statement = "
            INSERT INTO usuarios 
                (nombre, paterno, materno, correo, role, contrasena)
            VALUES
                (:nombre, :paterno, :materno, :correo, :role, :contrasena);
        ";
        $hash = password_hash($input['contrasena'], PASSWORD_DEFAULT, [15]);

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'nombre' => $input['nombre'],
                'paterno'  => $input['paterno'],
                'materno' => $input['materno'],
                'correo' => $input['correo'], //?? null  -- para omitir campo vacio
                'role' => $input['role'],
                'contrasena' => $hash
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function update($id, Array $input)
    {
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
        $hash = password_hash($input['contrasena'], PASSWORD_DEFAULT, [15]);
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'id' => (int) $id,
                'nombre' => $input['nombre'],
                'paterno'  => $input['paterno'],
                'materno' => $input['materno'] ,
                'correo' => $input['correo'] ,
                'role' => $input['role'] ,
                'contrasena' => $hash ,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function delete($id)
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
}