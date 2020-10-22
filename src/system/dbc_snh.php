<?php
namespace Src\system;

class dbc_snh {

    private $dbConnection = null;

    public function __construct()
    {
        $host = getenv('DB_HOST_SNH');
        $port = getenv('DB_PORT_SNH');
        $db   = getenv('DB_DATABASE_SNH');
        $user = getenv('DB_USERNAME_SNH');
        $pass = getenv('DB_PASSWORD_SNH');
        
        try {
            $this->dbConnection = new \PDO(
                "mysql:host=$host;port=$port;charset=utf8mb4;dbname=$db",
                $user,
                $pass
            );
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->dbConnection;
    }
}