<?php

namespace Oriole\Models;

use Oriole\Oriole;

class BaseModel
{
    protected static \PDO|null $dbh = null;
    protected \PDOStatement|false $sth = false;
    
    public function __construct()
    {
        if (is_null(self::$dbh)) {
            $databaseConfig = (new Oriole())->getConfig('database');
            
            $hostname = $databaseConfig['hostname'];
            $username = $databaseConfig['username'];
            $password = $databaseConfig['password'];
            $database = $databaseConfig['database'];
            $charset = $databaseConfig['charset'];
            $collation = $databaseConfig['collation'];
            $port = $databaseConfig['port'];
            $dsn = "mysql:host={$hostname};port={$port};dbname={$database};charset={$charset}";
            $options = [
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$charset}' COLLATE '{$collation}'"
            ];
            
            try {
                self::$dbh = new \PDO($dsn, $username, $password, $options);
            } catch(\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int) $e->getCode());
            }
        }
    }
}