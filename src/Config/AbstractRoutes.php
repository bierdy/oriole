<?php

namespace Oriole\Config;

use Oriole\Oriole;
use Oriole\Router\Routes as RouterRoutes;

abstract class AbstractRoutes extends BaseConfig
{
    protected array $appConfig;
    
    protected RouterRoutes $routes;
    
    final public function __construct()
    {
        $this->appConfig = (new Oriole)->getConfig('app');
        $this->routes = RouterRoutes::getInstance();
    }
    
    abstract public function setRoutes() : void;
}