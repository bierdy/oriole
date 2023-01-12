<?php

namespace Oriole\HTTP;

use Oriole\Oriole;

/**
 * Representation of an HTTP request.
 */
class Request
{
    /**
     * Constructor
     */
    public function __construct()
    {
    
    }
    
    /**
     * Get value from $_SERVER
     *
     * @param string $name
     * @return string|null
     */
    public function getServer(string $name) : string|null
    {
        return $_SERVER[$name] ?? null;
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