<?php
require 'vendor/autoload.php';
use Dotenv\Dotenv;

use Src\system\DatabaseConnector;
use Src\system\dbc_nnh;
use Src\system\dbc_snh;
use Src\system\dbc_sys;

$dotenv = new DotEnv(__DIR__);
$dotenv->load();

//$dbConnection = (new DatabaseConnector())->getConnection();
$dbSys = (new dbc_sys())->getConnection();

$dbNNH = (new dbc_nnh())->getConnection();

$dbSNH = (new dbc_snh())->getConnection();