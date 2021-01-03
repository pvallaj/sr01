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
        //error_log("SNH".$host." - ".$port." - ".$db." - ".$user." - ".$pass.PHP_EOL, 3, "C:\\log\\log.txt");
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