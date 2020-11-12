<?php
namespace Src\system;

class dbc_sys {

    private $dbConnection = null;

    public function __construct()
    {
        $host = getenv('DB_HOST_SYS');
        $port = getenv('DB_PORT_SYS');
        $db   = getenv('DB_DATABASE_SYS');
        $user = getenv('DB_USERNAME_SYS');
        $pass = getenv('DB_PASSWORD_SYS');
        //error_log("SYS ".$host." - ".$port." - ".$db." - ".$user." - ".$pass.PHP_EOL, 3, "c:\\log\\log.txt");
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