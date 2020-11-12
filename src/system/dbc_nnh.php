<?php
namespace Src\system;

class dbc_nnh {

    private $dbConnection = null;

    public function __construct()
    {
        $host = getenv('DB_HOST_NNH');
        $port = getenv('DB_PORT_NNH');
        $db   = getenv('DB_DATABASE_NNH');
        $user = getenv('DB_USERNAME_NNH');
        $pass = getenv('DB_PASSWORD_NNH');
        //error_log("NNH ".$host." - ".$port." - ".$db." - ".$user." - ".$pass.PHP_EOL, 3, "c:\\log\\log.txt");
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