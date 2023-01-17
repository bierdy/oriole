<?php

namespace Oriole\Config;

use Oriole\Oriole;
use Oriole\Router\Routes;

abstract class AbstractRoutesConfig extends BaseConfig
{
    protected array $appConfig;
    
    protected Routes $routes;
    
    final public function __construct()
    {
        $this->appConfig = (new Oriole)->getConfig('app');
        $this->routes = Routes::getInstance();
    }
    
    abstract public function setRoutes() : void;
}