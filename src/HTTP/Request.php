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
     * Get value from $_SERVER
     *
     * @param string $name
     * @param int|null $filter
     * @param null $flags
     * @return string|null
     */
    public function getServer(string $name, ? int $filter = null, $flags = null) : ? string
    {
        return $this->getGlobal('server', $name, $filter, $flags);
    }
    
    /**
     * Get value from $_GET
     *
     * @param string $name
     * @param int|null $filter
     * @param null $flags
     * @return string|null
     */
    public function getGet(string $name, ? int $filter = null, $flags = null) : ? string
    {
        return $this->getGlobal('get', $name, $filter, $flags);
    }
    
    /**
     * Get value from global variable (like $_GET or $_SERVER)
     *
     * @param string $type
     * @param string $name
     * @param int|null $filter
     * @param $flags
     * @return string|null
     */
    protected function getGlobal(string $type, string $name, ? int $filter = null, $flags = null) : ? string
    {
        if (is_null($value = self::$globals[$type][$name] ?? null))
            return $value;
    
        $filter ??= FILTER_DEFAULT;
        $flags = is_array($flags) ? $flags : (is_numeric($flags) ? (int) $flags : 0);
    
        return filter_var($value, $filter, $flags);
    }
    
    protected function getHttpScheme() : string
    {
        $requestScheme = $this->getServer('REQUEST_SCHEME') ? : null;
        $https = $this->getServer('HTTPS') ? : null;
        $serverPort = $this->getServer('SERVER_PORT') ? : null;
        
        return $requestScheme ? : (($https === 'on') || ($serverPort == 443) ? 'https' : 'http');
    }
    
    /**
     * Get current base url of the current request
     *
     * @return string
     */
    public function getCurrentBaseURL() : string
    {
        $httpHost = $this->getServer('HTTP_HOST') ? : '';
        $scheme = $this->getHttpScheme();
        
        return $scheme . '://' . $httpHost . '/';
    }
    
    /**
     * Get admin base url or current base url
     * of the current request if admin domain
     * is empty
     *
     * @return string
     */
    public function getAdminBaseURL() : string
    {
        $oriole = new Oriole();
        $adminDomain = $oriole->getConfig('app', 'adminDomain') ? : $this->getServer('HTTP_HOST') ? : '';
        $adminBasePath = $oriole->getConfig('app', 'adminBasePath');
        $adminBasePath = $adminBasePath === '/' ? '' : $adminBasePath;
        $scheme = $this->getHttpScheme();
        
        return $scheme . '://' . $adminDomain . '/' . $adminBasePath;
    }
    
    /**
     * Get public base url or current base url
     * of the current request if public domain
     * is empty
     *
     * @return string
     */
    public function getPublicBaseURL() : string
    {
        $oriole = new Oriole();
        $publicDomain = $oriole->getConfig('app', 'publicDomain') ? : $this->getServer('HTTP_HOST') ? : '';
        $publicBasePath = $oriole->getConfig('app', 'publicBasePath');
        $publicBasePath = $publicBasePath === '/' ? '' : $publicBasePath;
        $scheme = $this->getHttpScheme();
        
        return $scheme . '://' . $publicDomain . '/' . $publicBasePath;
    }
}