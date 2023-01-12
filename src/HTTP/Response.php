<?php

namespace Oriole\HTTP;

use Exception;

/**
 * Representation of an HTTP response.
 */
class Response
{
    protected static self|null $instance = null;
    
    /**
     * Constructor
     */
    final private function __construct()
    {
    
    }
    final protected function __clone()
    {
        
    }
    
    /**
     * @throws Exception
     */
    final public function __wakeup()
    {
        throw new Exception('Cannot unserialize a singleton.');
    }
    
    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
}