<?php
namespace Src\system;

class dbc_sys {
    /******************************************************************************************
    DESCRIPCIÓN:
    Crea un objeto de conexión a la base de datos de ""
    ******************************************************************************************/
    private $dbConnection = null;

    public function __construct()
    {
        $host = getenv('DB_HOST_SYS');
        $port = getenv('DB_PORT_SYS');
        $db   = getenv('DB_DATABASE_SYS');
        $user = getenv('DB_USERNAME_SYS');
        $pass = getenv('DB_PASSWORD_SYS');
        try {
            $this->dbConnection = new \PDO(
                "mysql:host=$host;port=$port;charset=utf8mb4;dbname=$db",
                $user,
                $pass
            );
            $this->dbConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
    
    public function getConnection()
    {
        return $this->dbConnection;
    }
}