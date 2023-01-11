<?php

namespace Oriole\HTTP;

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
}