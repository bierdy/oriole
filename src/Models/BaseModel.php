<?php

namespace Oriole\Models;

use PDO;
use PDOStatement;
use Oriole\Oriole;
use Oriole\Validation\Rules;
use Oriole\Validation\Messages;
use PDOException;
use Exception;
use InvalidArgumentException;

class BaseModel
{
    protected static PDO|null $dbh = null;
    
    protected PDOStatement|false $stmt = false;
    
    public string $table = '';
    
    public string $primaryKey = '';
    
    public string $createdAtKey = 'created_at';
    
    public string $updatedAtKey = 'updated_at';
    
    public array $validationRules = [];
    
    public array $validationMessages = [];
    
    /**
     * @var Rules|null
     */
    public ? Rules $validationRulesHandler = null;
    
    /**
     * @var Messages|null
     */
    public ? Messages $validationMessagesHandler = null;
    
    protected ? int $fetchMode = null;
    
    protected string $sql = '';
    
    final protected const BIND_KEY = ':key_';
    
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
    
    /**
     * [
     *     'data' => [
     *         [
     *             'name' => 'Field "name" is required field,
     *             'surName' => 'Field "surName" is required field,
     *         ],
     *         [
     *             'name' => 'Field "name" is required field,
     *             'surName' => 'Field "surName" is required field,
     *         ],
     *     ],
     *     'pdo' => [],
     *     'logic' => [],
     * ]
     *
     * @var array
     */
    protected array $errors = [];
    
    protected int $errorDataCounter = -1;
    
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
            $dsn = "mysql:host=$hostname;port=$port;dbname=$database;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '$charset' COLLATE '$collation'"
            ];
            
            try {
                self::$dbh = new PDO($dsn, $username, $password, $options);
            } catch(PDOException $e) {
                throw new PDOException($e->getMessage(), (int) $e->getCode());
            }
        }
        
        $this->validationRulesHandler = new Rules();
        $this->validationMessagesHandler = new Messages();
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
        $this->sql .= ' ' . ($logic ? : 'WHERE') . " $name $operator $key ";
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
        
        $this->sql .= ' ' . ($logic ? : 'WHERE') . " $name IN (" . implode(',', $whereIn) . ") ";
        
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
    
    public function limit(string|int $count, string|int $offset = null) : static
    {
        $this->sql .= " LIMIT $count" . (! is_null($offset) ? ", $offset " : ' ');
        
        return $this;
    }
    
    /**
     * @throws Exception
     */
    public function set(array|object $values) : static
    {
        $values = is_object($values) ? (array) $values : $values;
        $this->validate($values);
        
        if (! empty($this->errors))
            return $this;
        
        unset($values[$this->primaryKey], $values[$this->createdAtKey], $values[$this->updatedAtKey]);
        
        $keys = [];
        foreach ($values as $name => $value) {
            $key = self::BIND_KEY . $this->bindCounter;
            $keys[] = " $name = $key";
            $this->binds[$key] = $value;
            
            $this->bindCounter++;
        }
        
        $this->sql .= " SET " . implode(',', $keys) . " ";
        
        return $this;
    }
    
    public function setFetchMode(int $fetchMode) : static
    {
        $this->fetchMode = $fetchMode;
        
        return $this;
    }
    
    public function reset() : static
    {
        $this->fetchMode = null;
        $this->sql = '';
        $this->bindCounter = 0;
        $this->binds = [];
        $this->errorDataCounter = -1;
        $this->errors = [];
        
        return $this;
    }
    
    public function execute() : void
    {
        try {
            $this->stmt = self::$dbh->prepare($this->sql);
            
            foreach ($this->binds as $key => $value)
                $this->stmt->bindValue(substr($key, 1), $value);
            
            if (! is_null($this->fetchMode))
                $this->stmt->setFetchMode($this->fetchMode);
            
            $this->stmt->execute();
        } catch (PDOException $e) {
            $this->errors['pdo'][] = $e->getMessage();
        }
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
    
    public function getAll(array $primaryKeys = []) : false|array
    {
        if (empty($this->table))
            $this->errors['logic'][] = 'Table key is empty';
        
        if (empty($this->primaryKey))
            $this->errors['logic'][] = 'Primary key is empty';
        
        if (! empty($this->errors))
            return false;
        
        $this->select('*')->from($this->table);
        
        if (! empty($primaryKeys))
            $this->whereIn($this->primaryKey, $primaryKeys);
        
        $this->execute();
        
        return empty($this->errors) ? $this->stmt->fetchAll() : false;
    }
    
    public function getOne(string|int|float $primaryKey)
    {
        if (empty($this->table))
            $this->errors['logic'][] = 'Table key is empty';
        
        if (empty($this->primaryKey))
            $this->errors['logic'][] = 'Primary key is empty';
        
        if (! empty($this->errors))
            return false;
        
        $this->select('*')->from($this->table)->where($this->primaryKey, '=', $primaryKey);
        
        $this->execute();
        
        return empty($this->errors) ? $this->stmt->fetch() : false;
    }
    
    /**
     * @throws Exception
     */
    public function addMany(array $data) : bool
    {
        if (empty($data)) {
            $this->errors['logic'][] = 'There are no data to add';
            return false;
        }
    
        $keysArray = [];
        foreach ($data as $values) {
            $values = is_object($values) ? (array) $values : $values;
            $this->validate($values, false);
            
            unset($values[$this->primaryKey], $values[$this->createdAtKey], $values[$this->updatedAtKey]);
            
            $keys = [];
            foreach ($values as $value) {
                $key = self::BIND_KEY . $this->bindCounter;
                $keys[] = $key;
                $this->binds[$key] = $value;
                
                $this->bindCounter++;
            }
    
            $keysArray[] = " (" . implode(',', $keys) . ")";
        }
        
        if (! empty($this->errors))
            return false;
        
        $names = array_keys(array_slice($data, 0, 1));
        
        $this->sql .= " INSERT INTO {$this->table} (" . implode(',', $names) . ") VALUES " . implode(',', $keysArray) . " ";
        
        $this->execute();
        
        return empty($this->errors);
    }
    
    /**
     * @throws Exception
     */
    public function addOne(array|object $values) : int|string|false
    {
        $values = is_object($values) ? (array) $values : $values;
        $this->validate($values, false);
        
        if (! empty($this->errors))
            return false;
        
        unset($values[$this->primaryKey], $values[$this->createdAtKey], $values[$this->updatedAtKey]);
        
        $names = [];
        $keys = [];
        foreach ($values as $name => $value) {
            $names[] = $name;
            $key = self::BIND_KEY . $this->bindCounter;
            $keys[] = $key;
            $this->binds[$key] = $value;
            
            $this->bindCounter++;
        }
        
        $this->sql .= " INSERT INTO {$this->table} (" . implode(',', $names) . ") VALUES (" . implode(',', $keys) . ") ";
        
        $this->execute();
        
        return empty($this->errors) ? self::$dbh->lastInsertId() : false;
    }
    
    /**
     * @throws Exception
     */
    public function updateMany(array $primaryKeys, array|object $values) : bool
    {
        if (empty($this->table))
            $this->errors['logic'][] = 'Table key is empty';
        
        if (empty($this->primaryKey))
            $this->errors['logic'][] = 'Primary key is empty';
        
        if (! empty($this->errors))
            return false;
        
        $this->sql .= " UPDATE {$this->table} ";
        $this->set($values);
        
        if (! empty($primaryKeys))
            $this->whereIn($this->primaryKey, $primaryKeys);
        
        if (! empty($this->errors))
            return false;
        
        $this->execute();
        
        return empty($this->errors);
    }
    
    /**
     * @throws Exception
     */
    public function updateOne(string|int|float $primaryKey, array|object $values) : bool
    {
        if (empty($this->table))
            $this->errors['logic'][] = 'Table key is empty';
        
        if (empty($this->primaryKey))
            $this->errors['logic'][] = 'Primary key is empty';
        
        if (! empty($this->errors))
            return false;
        
        $this->sql .= " UPDATE {$this->table} ";
        $this->set($values)->where($this->primaryKey, '=', $primaryKey);
        
        if (! empty($this->errors))
            return false;
        
        $this->execute();
        
        return empty($this->errors);
    }
    
    /**
     * @throws Exception
     */
    public function update() : bool
    {
        if (empty($this->table))
            $this->errors['logic'][] = 'Table key is empty';
        
        if (! empty($this->errors))
            return false;
        
        $this->sql = " UPDATE {$this->table} " . $this->sql;
        
        $this->execute();
        
        return empty($this->errors);
    }
    
    /**
     * @throws Exception
     */
    public function deleteMany(array $primaryKeys) : bool
    {
        if (empty($this->table))
            $this->errors['logic'][] = 'Table key is empty';
        
        if (empty($this->primaryKey))
            $this->errors['logic'][] = 'Primary key is empty';
        
        if (! empty($this->errors))
            return false;
        
        $this->sql .= " DELETE ";
        $this->from($this->table);
        
        if (! empty($primaryKeys))
            $this->whereIn($this->primaryKey, $primaryKeys);
        
        $this->execute();
        
        return empty($this->errors);
    }
    
    /**
     * @throws Exception
     */
    public function deleteOne(string|int|float $primaryKey) : bool
    {
        if (empty($this->table))
            $this->errors['logic'][] = 'Table key is empty';
        
        if (empty($this->primaryKey))
            $this->errors['logic'][] = 'Primary key is empty';
        
        if (! empty($this->errors))
            return false;
    
        $this->sql .= " DELETE ";
        $this->from($this->table)->where($this->primaryKey, '=', $primaryKey);
        
        $this->execute();
        
        return empty($this->errors);
    }
    
    /**
     * @throws Exception
     */
    public function delete() : bool
    {
        if (empty($this->table))
            $this->errors['logic'][] = 'Table key is empty';
        
        if (! empty($this->errors))
            return false;
        
        $this->sql = " DELETE {$this->table} " . $this->sql;
        
        $this->execute();
        
        return empty($this->errors);
    }
    
    /**
     * @throws Exception
     */
    public function truncate() : bool
    {
        if (empty($this->table))
            $this->errors['logic'][] = 'Table key is empty';
        
        if (! empty($this->errors))
            return false;
        
        $this->sql .= " TRUNCATE TABLE {$this->table} ";
        
        $this->execute();
        
        return empty($this->errors);
    }
    
    /**
     * @throws Exception
     */
    protected function validate(array $data, bool $onlyPassedData = true) : void
    {
        $this->errorDataCounter++;
        
        if (empty($data)) {
            $this->errors['data'][$this->errorDataCounter][] = 'There is no data to validate';
            return;
        }
        
        if (empty($validationRules = $this->validationRules))
            return;
        
        $validationRules = $onlyPassedData ? array_intersect_key($validationRules, $data) : $validationRules;
        
        // Replace any placeholders (e.g. {id}) in the rules with
        // the value found in $data, if any.
        $validationRules = $this->fillPlaceholders($validationRules, $data);
        
        foreach ($validationRules as $field => $rules) {
            $rules = explode('|', $rules);
            
            if (str_contains($field, '*')) {
                $values = array_filter(array_flatten_with_dots($data), static fn ($key) => preg_match(
                    '/^'
                    . str_replace(['\.\*', '\*\.'], ['\..+', '.+\.'], preg_quote($field, '/'))
                    . '$/',
                    $key
                ), ARRAY_FILTER_USE_KEY);
                // if keys not found
                $values = $values ? : [$field => null];
            } else {
                $values = dot_array_search($field, $data);
            }
            
            $values = $values ?? '';
            
            if ($values === []) {
                // We'll process the values right away if an empty array
                $this->processRules($field, $values, $rules, $data);
                
                continue;
            }
            
            if (str_contains($field, '*')) {
                // Process multiple fields
                foreach ($values as $dotField => $value)
                    $this->processRules($dotField, $value, $rules, $data, $field);
            } else {
                // Process single field
                $this->processRules($field, $values, $rules, $data);
            }
        }
    }
    
    /**
     * Replace any placeholders within the rules with the values that
     * match the 'key' of any properties being set. For example, if
     * we had the following $data array:
     *
     * [ 'id' => 13 ]
     *
     * and the following rule:
     *
     *  'required|is_unique[users,email,id,{id}]'
     *
     * The value of {id} would be replaced with the actual id in the form data:
     *
     *  'required|is_unique[users,email,id,13]'
     */
    protected function fillPlaceholders(array $rules, array $data) : array
    {
        if (empty($rules) || empty($data))
            return $rules;
        
        $replacements = [];
        
        foreach ($data as $key => $value)
            $replacements["{{$key}}"] = $value;
        
        foreach ($rules as &$rule)
            $rule = strtr($rule, $replacements);
        
        return $rules;
    }
    
    /**
     * Runs all of $rules against $field, until one fails, or
     * all of them have been processed. If one fails, it adds
     * the error to $this->errors and moves on to the next,
     * so that we can collect all the first errors.
     *
     * @param string $field
     * @param array|string $value
     * @param array $rules
     * @param array|null $data The array of data to validate.
     * @param string|null $originalField The original asterisk field name like "foo.*.bar".
     * @return bool
     * @throws Exception
     */
    protected function processRules(string $field, array|string $value, array $rules, ? array $data = null, ? string $originalField = null) : bool
    {
        if (is_null($data))
            throw new InvalidArgumentException('You must supply the parameter: data.');
        
        if (in_array('if_exist', $rules, true)) {
            $flattenedData = array_flatten_with_dots($data);
            $ifExistField  = $field;
            
            if (str_contains($field, '.*')) {
                // We'll change the dot notation into a PCRE pattern that can be used later
                $ifExistField = str_replace('\.\*', '\.(?:[^\.]+)', preg_quote($field, '/'));
                $dataIsExisting = false;
                $pattern = sprintf('/%s/u', $ifExistField);
                
                foreach (array_keys($flattenedData) as $item) {
                    if (preg_match($pattern, $item) === 1) {
                        $dataIsExisting = true;
                        break;
                    }
                }
            } else {
                $dataIsExisting = array_key_exists($ifExistField, $flattenedData);
            }
            
            unset($ifExistField, $flattenedData);
            
            if (! $dataIsExisting) {
                // we return early if 'if_exist' is not satisfied. we have nothing to do here.
                return true;
            }
            
            // Otherwise remove the if_exist rule and continue the process
            $rules = array_diff($rules, ['if_exist']);
        }
        
        if (in_array('permit_empty', $rules, true)) {
            if (! in_array('required', $rules, true) && (is_array($value) ? $value === [] : trim($value) === ''))
                return true;
            
            $rules = array_diff($rules, ['permit_empty']);
        }
        
        foreach ($rules as $rule) {
            $isRuleMethodExist = method_exists($this->validationRulesHandler, $rule);
            $param = false;
            
            if (! $isRuleMethodExist && preg_match('/(.*?)\[(.*)\]/', $rule, $match)) {
                $rule  = $match[1];
                $param = $match[2];
                
                $isRuleMethodExist = method_exists($this->validationRulesHandler, $rule);
            }
            
            // Placeholder for custom errors from the rules.
            $error = null;
            
            // If it's not a callable, get out of here.
            if (! $isRuleMethodExist)
                throw new Exception("$rule is not a valid rule.");
            
            $passed = $param === false
                ? $this->validationRulesHandler->$rule($value, $error, $field)
                : $this->validationRulesHandler->$rule($value, $param, $data, $error, $field);
            
            // Set the error message if we didn't survive.
            if ($passed === false) {
                // if the $value is an array, convert it to as string representation
                if (is_array($value))
                    $value = $this->isStringList($value) ? '[' . implode(', ', $value) . ']' : json_encode($value);
                elseif (is_object($value))
                    $value = json_encode($value);
                
                $param = ($param === false) ? '' : $param;
                
                $this->errors['data'][$this->errorDataCounter][$field] = $error ?? $this->getErrorMessage(
                    $rule,
                    $field,
                    $param,
                    (string) $value,
                    $originalField
                );
                
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Is the array a string list 'list<string>'?
     */
    private function isStringList(array $array) : bool
    {
        $expectedKey = 0;
        
        foreach ($array as $key => $val) {
            // Note: also covers PHP array key conversion, e.g. '5' and 5.1 both become 5
            if (! is_int($key))
                return false;
            
            if ($key !== $expectedKey)
                return false;
            
            $expectedKey++;
            
            if (! is_string($val))
                return false;
        }
        
        return true;
    }
    
    /**
     * Attempts to find the appropriate error message
     *
     * @param string|null $value The value that caused the validation to fail.
     */
    protected function getErrorMessage(string $rule, string $field, ? string $param = null, ? string $value = null, ? string $originalField = null) : string
    {
        if (isset($this->validationMessages[$field][$rule]))
            $message = $this->validationMessages[$field][$rule];
        elseif (! is_null($originalField) && isset($this->validationMessages[$originalField][$rule]))
            $message = $this->validationMessages[$originalField][$rule];
        elseif (isset($this->validationMessagesHandler->messages[$rule]))
            $message = $this->validationMessagesHandler->messages[$rule];
        else
            $message = "There is an error with field {field}";
        
        $message = str_replace('{field}', $field, $message);
        $message = str_replace('{param}', $param ?? '', $message);
        $message = str_replace('{value}', $value ?? '', $message);
        
        return $message;
    }
    
    public function errors() : array
    {
        return $this->errors;
    }
    
    public function beginTransaction() : void
    {
        self::$dbh->beginTransaction();
    }
    
    public function submitTransaction() : bool
    {
        try {
            self::$dbh->commit();
        } catch (PDOException $e) {
            self::$dbh->rollBack();
            return false;
        }
        
        return true;
    }
    
    public function rollBackTransaction() : void
    {
        self::$dbh->rollBack();
    }
}