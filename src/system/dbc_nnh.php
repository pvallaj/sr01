<?php
namespace Src\system;

class dbc_nnh {

    private $dbConnection = null;

    public function __construct()
    {
        /******************************************************************************************
        DESCRIPCIÓN:
        Crea un objeto de conexión a la base de datos de "narrativas" o "relaciones".
        DB_HOST_NNH. Es el nombre del servidor que ejecuta el servicio de la base de datos. 
        DB_PORT_NNH. Es el puerto asignado a la base de datos, en el servidor. 
        DB_DATABASE_NAME. Es el nombre de la base de datos, en el manejador de bases de datos. 
        DB_USER_NAME: Es el usuario que tiene permisos de lectura y edición en los registros de la base de datos. 
        DB_PASSWORD_NNH: Es la contraseña de acceso del usuario asignado. 

        Todos los parámetros son leidos del archivo “.env” en el directorio raíz de esta aplicación. 

        ******************************************************************************************/
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
            $this->dbConnection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function getConnection()
    {
        /******************************************************************************************
        DESCRIPCIÓN:
        egresa el objeto de conexión, si la conexión no se logró el objeto queda como null y es lo que regresa. 
        ******************************************************************************************/
        return $this->dbConnection;
    }
}