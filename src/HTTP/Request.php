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
     * Get value/values from global variables (like $_GET or $_SERVER)
     *
     * @param string $type
     * @param string|null $name
     * @param int|null $filter
     * @param null $flags
     * @return array|string|null
     */
    protected function getGlobal(string $type, ? string $name = null, ? int $filter = null, $flags = null) : array|string|null
    {
        $filter ??= FILTER_DEFAULT;
        $flags = is_array($flags) ? $flags : (is_numeric($flags) ? (int) $flags : 0);
        
        if (! is_null($name)) {
            if (is_null($value = self::$globals[$type][$name] ?? null))
                return null;
            
            return filter_var($value, $filter, $flags);
        }
        
        $values = self::$globals[$type] ?? [];
        foreach ($values as &$value)
            $value = filter_var($value, $filter, $flags);
        
        return $values;
    }
    
    protected function getHttpScheme() : string
    {
        $requestScheme = strtolower($this->getServer('REQUEST_SCHEME') ? : '');
        $https = strtolower($this->getServer('HTTPS') ? : '');
        $serverPort = $this->getServer('SERVER_PORT') ? : null;
        
        return $requestScheme ? : (($https === 'on') || ($serverPort == 443) ? 'https' : 'http');
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
        $scheme = $this->getHttpScheme();
        $httpHost = strtolower($this->getServer('HTTP_HOST') ? : '');
        
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
        $scheme = $this->getHttpScheme();
        $adminDomain = strtolower($oriole->getConfig('app', 'adminDomain') ? : $this->getServer('HTTP_HOST') ? : '');
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
        $scheme = $this->getHttpScheme();
        $publicDomain = strtolower($oriole->getConfig('app', 'publicDomain') ? : $this->getServer('HTTP_HOST') ? : '');
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