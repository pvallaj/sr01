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
        //$this->write_log("SYS ".$host." - ".$port." - ".$db." - ".$user." - ".$pass.PHP_EOL);
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
    public function write_log($log_msg)
    {
        $log_dir = "logs";
        if (!file_exists($log_dir))
        {
            mkdir($log_dir, 0777, true);
        }
        $log_file_data = $log_dir.'/debug.log';
      file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
       
    }
    public function getConnection()
    {
        return $this->dbConnection;
    }
}