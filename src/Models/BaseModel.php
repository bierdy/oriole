<?php

namespace Oriole\Models;

use Oriole\Oriole;

class BaseModel
{
    protected static \PDO|null $dbh = null;
    
    protected \PDOStatement|false $stmt = false;
    
    public string $table = '';
    
    public string $primaryKey = '';
    
    public array $validationRules = [];
    
    public array $validationMessages = [];
    
    protected ? int $fetchMode = null;
    
    protected string $sql = '';
    
    final const BIND_KEY = ':key_';
    
    protected int $bindCounter = 0;
    
    /**
     * @var array
     *
     * [
     *     ':key_0' => 'Mike',
     *     ':key_1' => 'Tim',
     *     ':key_2' => 'Peter',
     * ]
     */
    protected array $binds = [];
    
    protected array $errors = [];
    
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
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$charset}' COLLATE '{$collation}'"
            ];
            
            try {
                self::$dbh = new \PDO($dsn, $username, $password, $options);
            } catch(\PDOException $e) {
                throw new \PDOException($e->getMessage(), (int) $e->getCode());
            }
        }
    }
    
    public function from(string $table) : static
    {
        $this->sql .= " FROM $table ";
        
        return $this;
    }
    
    public function select(string $select) : static
    {
        $this->sql .= " SELECT $select ";
        
        return $this;
    }
    
    public function where(string $name, string $operator, string|int|float $value, string $logic = '') : static
    {
        $key = self::BIND_KEY . $this->bindCounter;
        $this->sql .= " $logic WHERE $name $operator $key ";
        $this->binds[$key] = $value;
        
        $this->bindCounter++;
        
        return $this;
    }
    
    public function andWhere(string $name, string $operator, string|int|float $value) : static
    {
        return $this->where($name, $operator, $value, 'AND');
    }
    
    public function orWhere(string $name, string $operator, string|int|float $value) : static
    {
        return $this->where($name, $operator, $value, 'OR');
    }
    
    public function whereIn(string $name, array $values, string $logic = '') : static
    {
        $whereIn = [];
        foreach ($values as $value) {
            $key = self::BIND_KEY . $this->bindCounter;
            $whereIn[] = $key;
            $this->binds[$key] = $value;
            
            $this->bindCounter++;
        }
        
        $this->sql .= " $logic WHERE $name IN (" . implode(',', $whereIn) . ") ";
        
        return $this;
    }
    
    public function andWhereIn(string $name, array $values) : static
    {
        return $this->whereIn($name, $values, 'AND');
    }
    
    public function orWhereIn(string $name, array $values) : static
    {
        return $this->whereIn($name, $values, 'OR');
    }
    
    public function join(string $type, string $table, string $on) : static
    {
        $type = strtoupper($type);
        $this->sql .= " $type JOIN $table ON $on ";
        
        return $this;
    }
    
    public function groupBy(string $name) : static
    {
        $this->sql .= " GROUP BY $name ";
        
        return $this;
    }
    
    public function orderBy(string $name) : static
    {
        $this->sql .= " ORDER BY $name ";
        
        return $this;
    }
    
    public function setFetchMode(int $fetchMode) : static
    {
        $this->fetchMode = $fetchMode;
        
        return $this;
    }
    
    protected function reset() : void
    {
        $this->fetchMode = null;
        $this->sql = '';
        $this->bindCounter = 0;
        $this->binds = [];
    }
    
    public function execute() : void
    {
        $this->errors = [];
        
        try {
            $this->stmt = self::$dbh->prepare($this->sql);
            
            foreach ($this->binds as $key => $value)
                $this->stmt->bindValue(substr($key, 1), $value);
            
            if (! is_null($this->fetchMode))
                $this->stmt->setFetchMode($this->fetchMode);
            
            $this->stmt->execute();
        } catch (\PDOException $e) {
            $this->errors[] = $e->getMessage();
        }
    
        $this->reset();
    }
    
    public function findAll() : false|array
    {
        $this->execute();
        
        return empty($this->errors) ? $this->stmt->fetchAll() : false;
    }
    
    public function findOne()
    {
        $this->execute();
        
        return empty($this->errors) ? $this->stmt->fetch() : false;
    }
    
    public function getAll(array $values = []) : false|array
    {
        if (empty($this->table))
            $this->errors[] = 'Table key is empty';
        
        if (empty($this->primaryKey))
            $this->errors[] = 'Primary key is empty';
        
        if (! empty($this->errors))
            return false;
        
        $this->select('*')->from($this->table);
        
        if (! empty($values))
            $this->whereIn($this->primaryKey, $values);
        
        $this->execute();
        
        return empty($this->errors) ? $this->stmt->fetchAll() : false;
    }
    
    public function getOne(string|int|float $value)
    {
        if (empty($this->table))
            $this->errors[] = 'Table key is empty';
        
        if (empty($this->primaryKey))
            $this->errors[] = 'Primary key is empty';
        
        if (! empty($this->errors))
            return false;
        
        $this->select('*')->from($this->table)->where($this->primaryKey, '=', $value);
        
        $this->execute();
        
        return empty($this->errors) ? $this->stmt->fetch() : false;
    }
}