<?php
require 'vendor/autoload.php';
use Dotenv\Dotenv;

use Src\system\DatabaseConnector;

$dotenv = new DotEnv(__DIR__);
$dotenv->load();

$dbConnection = (new DatabaseConnector())->getConnection();