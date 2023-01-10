<?php

namespace Oriole\Router;

use Closure;
use LogicException;
use Oriole\HTTP\Request;

class Router
{
    /**
     * A Routes instance.
     *
     * @var Routes
     */
    protected Routes $routes;
    
    /**
     * A Request instance.
     */
    protected Request $request;
    
    /**
     * The handler that was matched for this request.
     *
     * @var string|Closure|null
     */
    protected string|Closure|null $handler = null;
    
    /**
     * An array of binds that were collected,
     * so they can be sent to closure routes.
     *
     * @var array
     */
    protected array $params = [];
    
    /**
     * Constructor
     */
    public function __construct(Routes $routes, Request $request)
    {
        $this->routes = $routes;
        $this->request = $request;
    }
    
    public function defineRoute() : bool
    {
        $routes = $this->routes->getRoutes();
        
        $requestMethod = $this->request->getServer('REQUEST_METHOD');
        $requestMethod = strtolower($requestMethod);
        
        $httpHost = $this->request->getServer('HTTP_HOST');
        $httpHost = strtolower($httpHost);
        
        $requestURI = $this->request->getServer('REQUEST_URI');
        $requestURI = urldecode($requestURI);
        $requestURI = $requestURI === '/' ? $requestURI : trim($requestURI, '/ ');
        
        if (
            $this->findRoute($routes, $requestMethod, $httpHost, $requestURI)
            || $this->findRoute($routes, $requestMethod, '*', $requestURI)
            || $this->findRoute($routes, '*', $httpHost, $requestURI)
            || $this->findRoute($routes, '*', '*', $requestURI)
        )
            return true;
        
        return false;
    }
    
    protected function findRoute(array $routes, string $requestMethod, string $httpHost, string $requestURI) : bool
    {
        if (empty($routes[$requestMethod][$httpHost]))
            return false;
        
        foreach ($routes[$requestMethod][$httpHost] as $routeKey => $routeValue) {
            $routeKey = $routeKey === '/' ? $routeKey : trim($routeKey, '/ ');
            
            if (preg_match('#^' . $routeKey . '$#u', $requestURI, $matches)) {
                array_shift($matches);
                
                $this->handler = $routeValue['handler'];
                $this->params = $matches;
                
                return true;
            }
        }
        
        return false;
    }
    
    public function runHandler()
    {
        $handler = $this->handler;
        $params = $this->params;
        
        // If it's a function let's run it
        if (is_callable($handler) && get_class($handler) === 'Closure')
            return $handler(...$params);
        
        // Otherwise it must be a string like Class::method or Class::method/$0/$1
        if (! is_string($handler))
            throw new LogicException('$this->handler is not a string');
        
        $handlerArray = explode('::', $handler);
        $controller = ! empty($handlerArray[0]) && is_string($handlerArray[0]) ? $handlerArray[0] : '';
        
        if (! class_exists($controller))
            throw new LogicException("Class \"$controller\" is not exist");
        
        $methodArray = explode('/', $handlerArray[1]);
        $method = ! empty($methodArray[0]) && is_string($methodArray[0]) ? array_shift($methodArray) : '';
        
        if (! method_exists($controller, $method))
            throw new LogicException("Method \"$method\" does not exist in the class \"$controller\"");
        
        $args = $methodArray;
        foreach ($args as &$arg)
            if (preg_match('#^\$(\d*)$#u', $arg, $matches)) {
                array_shift($matches);
                $arg = $params[(int) $matches[0]] ?? null;
            }
        
        return (new $controller)->{$method}(...$args);
    }
}