<?php

namespace Oriole\HTTP;

use Oriole\Oriole;

/**
 * Representation of an HTTP request.
 */
class Request
{
    /**
     * Stores values we've retrieved from
     * PHP globals.
     *
     * @var array
     */
    protected static array $globals = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        if (empty(self::$globals))
            $this->populateGlobals();
    }
    
    /**
     * Saves a copy of the current state of one of several PHP globals,
     * so we can retrieve them later.
     */
    protected function populateGlobals() : void
    {
        self::$globals['server']  = $_SERVER  ?? [];
        self::$globals['get']     = $_GET     ?? [];
        self::$globals['post']    = $_POST    ?? [];
        self::$globals['files']   = $_FILES   ?? [];
        self::$globals['cookie']  = $_COOKIE  ?? [];
        self::$globals['session'] = $_SESSION ?? [];
        self::$globals['request'] = $_REQUEST ?? [];
        self::$globals['env']     = $_ENV     ?? [];
    }
    
    /**
     * Get value/values from $_SERVER
     *
     * @param string|null $name
     * @param int|null $filter
     * @param null $flags
     * @return array|string|null
     */
    public function getServer(? string $name = null, ? int $filter = null, $flags = null) : array|string|null
    {
        return $this->getGlobal('server', $name, $filter, $flags);
    }
    
    /**
     * Get value/values from $_GET
     *
     * @param string|null $name
     * @param int|null $filter
     * @param null $flags
     * @return array|string|null
     */
    public function getGet(? string $name = null, ? int $filter = null, $flags = null) : array|string|null
    {
        return $this->getGlobal('get', $name, $filter, $flags);
    }
    
    /**
     * Get value/values from $_POST
     *
     * @param string|null $name
     * @param int|null $filter
     * @param null $flags
     * @return array|string|null
     */
    public function getPost(? string $name = null, ? int $filter = null, $flags = null) : array|string|null
    {
        return $this->getGlobal('post', $name, $filter, $flags);
    }
    
    /**
     * Get value/values from $_COOKIE
     *
     * @param string|null $name
     * @param int|null $filter
     * @param null $flags
     * @return array|string|null
     */
    public function getCookie(? string $name = null, ? int $filter = null, $flags = null) : array|string|null
    {
        $oriole = new Oriole();
        $prefix = $oriole->getConfig('cookie', 'prefix');
        $name = ! is_null($name) ? $prefix . $name : null;
        
        return $this->getGlobal('cookie', $name, $filter, $flags);
    }
    
    /**
     * Get value/values from global variables (like $_GET or $_SERVER)
     *
     * @param string $method
     * @param string|null $name
     * @param int|null $filter
     * @param null $flags
     * @return array|string|null
     */
    protected function getGlobal(string $method, ? string $name = null, ? int $filter = null, $flags = null) : array|string|null
    {
        $method = strtolower($method);
        
        // Null filters cause null values to return.
        $filter ??= FILTER_DEFAULT;
        $flags = is_array($flags) ? $flags : (is_numeric($flags) ? (int) $flags : 0);
        
        // Return all values when $name is null
        if (is_null($name)) {
            $values = [];
            
            foreach (self::$globals[$method] as $key => $value)
                $values[$key] = is_array($value) ? $this->getGlobal($method, $key, $filter, $flags) : filter_var($value, $filter, $flags);
            
            return $values;
        }
        
        // Does the index contain array notation?
        if (($count = preg_match_all('/(?:^[^\[]+)|\[[^]]*\]/', $name, $matches)) > 1) {
            $value = self::$globals[$method];
            
            for ($i = 0; $i < $count; $i++) {
                $key = trim($matches[0][$i], '[]');
                
                if ($key === '') // Empty notation will return the value as array
                    break;
                
                if (isset($value[$key]))
                    $value = $value[$key];
                else
                    return null;
            }
        }
        
        if (! isset($value))
            $value = self::$globals[$method][$name] ?? null;
        
        if (is_array($value) && ($filter !== FILTER_DEFAULT || ((is_numeric($flags) && $flags !== 0) || is_array($flags) && $flags !== []))) {
            // Iterate over array and append filter and flags
            array_walk_recursive($value, static function (&$val) use ($filter, $flags) {
                $val = filter_var($val, $filter, $flags);
            });
            
            return $value;
        }
        
        // Cannot filter these types of data automatically...
        if (is_array($value) || is_object($value) || $value === null)
            return $value;
        
        return filter_var($value, $filter, $flags);
    }
    
    public function getRequestScheme() : string
    {
        $requestScheme = strtolower($this->getServer('REQUEST_SCHEME') ? : '');
        $https = strtolower($this->getServer('HTTPS') ? : '');
        $serverPort = $this->getServer('SERVER_PORT') ? : null;
        
        return $requestScheme ? : (($https === 'on') || ($serverPort == 443) ? 'https' : 'http');
    }
    
    public function getRequestMethod() : string
    {
        $requestMethod = $this->getServer('REQUEST_METHOD') ? : '';
        
        return strtolower($requestMethod);
    }
    
    public function getHttpHost() : string
    {
        $httpHost = $this->getServer('HTTP_HOST') ? : '';
        
        return strtolower($httpHost);
    }
    
    /**
     * Get current base url of the current request
     *
     * for example, https://domain.com
     *
     * @return string
     */
    public function getCurrentBaseURL() : string
    {
        $scheme = $this->getRequestScheme();
        $httpHost = $this->getHttpHost();
        
        return $scheme . '://' . $httpHost;
    }
    
    /**
     * Get admin base url or current base url
     * of the current request if admin domain
     * is empty
     *
     * for example, https://domain.com/admin
     *
     * @return string
     */
    public function getAdminBaseURL() : string
    {
        $oriole = new Oriole();
        $scheme = $this->getRequestScheme();
        $adminDomain = strtolower($oriole->getConfig('app', 'adminDomain') ? : $this->getHttpHost());
        $adminBasePath = strtolower($oriole->getConfig('app', 'adminBasePath'));
        $adminBasePath = trim($adminBasePath, '/ ');
        
        return rtrim($scheme . '://' . $adminDomain . '/' . $adminBasePath, '/');
    }
    
    /**
     * Get public base url or current base url
     * of the current request if public domain
     * is empty
     *
     * for example, https://domain.com
     *
     * @return string
     */
    public function getPublicBaseURL() : string
    {
        $oriole = new Oriole();
        $scheme = $this->getRequestScheme();
        $publicDomain = strtolower($oriole->getConfig('app', 'publicDomain') ? : $this->getHttpHost());
        $publicBasePath = strtolower($oriole->getConfig('app', 'publicBasePath'));
        $publicBasePath = trim($publicBasePath, '/ ');
        
        return rtrim($scheme . '://' . $publicDomain . '/' . $publicBasePath, '/');
    }
    
    /**
     * Get current uri of the current request
     *
     * for example, admin/templates/21
     *
     * @return string
     */
    public function getCurrentURI() : string
    {
        $requestURI = strtolower($this->getServer('REQUEST_URI') ? : '');
        $requestURI = urldecode(parse_url($requestURI, PHP_URL_PATH));
        
        return $requestURI === '/' ? $requestURI : trim($requestURI, '/ ');
    }
    
    /**
     * Get current url of the current request
     *
     * for example, https://domain.com/templates/21
     *
     * @return string
     */
    public function getCurrentURL() : string
    {
        $currentBaseURL = $this->getCurrentBaseURL();
        $currentBaseURI = $this->getCurrentURI();
        
        return rtrim($currentBaseURL . '/' . $currentBaseURI, '/');
    }
}