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
}
