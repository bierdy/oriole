<?php

namespace Oriole\Router;

use Oriole\HTTP\Request;
use Closure;
use Exception;
use LogicException;
use InvalidArgumentException;
use ArgumentCountError;

class Router
{
    protected static self|null $instance = null;
    
    /**
     * A Routes instance.
     *
     * @var Routes
     */
    protected static Routes $routes;
    
    /**
     * A Request instance.
     */
    protected static Request $request;
    
    /**
     * The handler that was matched for this request.
     *
     * @var string|Closure|null
     */
    protected string|Closure|null $handler = null;
    
    /**
     * An array of handler arguments.
     *
     * @var array
     */
    protected array $args = [];
    
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
    final private function __construct()
    {
        self::$routes = Routes::getInstance();
        self::$request = new Request();
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
        if (self::$instance === null)
            self::$instance = new self;
        
        return self::$instance;
    }
    
    public function defineRoute() : bool
    {
        $routes = self::$routes->getRoutes();
        
        $requestMethod = self::$request->getServer('REQUEST_METHOD');
        $requestMethod = strtolower($requestMethod);
        
        $httpHost = self::$request->getServer('HTTP_HOST');
        $httpHost = strtolower($httpHost);
        
        $requestURI = self::$request->getServer('REQUEST_URI');
        $requestURI = urldecode(parse_url($requestURI, PHP_URL_PATH));
        $requestURI = $requestURI === '/' ? $requestURI : trim($requestURI, '/ ');
        
        if (
            $this->findRoute($routes, $requestMethod, $httpHost, $requestURI)
            || $this->findRoute($routes, $requestMethod, '*', $requestURI)
        )
            return true;
        
        return false;
    }
    
    protected function findRoute(array $routes, string $verb, string $domain, string $requestURI) : bool
    {
        if (empty($routes[$verb][$domain]))
            return false;
        
        foreach ($routes[$verb][$domain] as $route) {
            $from = $route['from'];
            
            if (preg_match('#^' . $from . '$#u', $requestURI, $matches)) {
                array_shift($matches);
                
                $this->handler = $route['handler'];
                $this->args = $route['args'];
                $this->params = $matches;
                
                return true;
            }
        }
        
        return false;
    }
    
    public function runHandler()
    {
        $handler = $this->handler;
        $args = $this->args;
        $params = $this->params;
        
        // If it's a function let's run it
        if (is_callable($handler) && get_class($handler) === 'Closure')
            return $handler(...$params);
        
        // Otherwise it must be a string like Class::method or Class::method/$0/$1
        if (! is_string($handler))
            throw new LogicException('$this->handler is not a string');
        
        $handlerArray = explode('::', $handler);
        $controller = ! empty($handlerArray[0]) ? $handlerArray[0] : '';
        
        if (! class_exists($controller))
            throw new LogicException("Class \"$controller\" is not exist");
        
        $method = ! empty($handlerArray[1]) ? $handlerArray[1] : '';
        
        if (! method_exists($controller, $method))
            throw new LogicException("Method \"$method\" does not exist in the class \"$controller\"");
        
        foreach ($args as &$arg)
            if (preg_match('#^\$(\d*)$#u', $arg, $matches)) {
                array_shift($matches);
                $arg = $params[(int) $matches[0]] ?? null;
            }
        
        return (new $controller)->{$method}(...$args);
    }
    
    public function getReverseRoute(string $type, string $key, ...$params) : string
    {
        $verb = ! empty($params['verb']) ? strtolower($params['verb']) : 'get';
        $domain = ! empty($params['domain']) ? strtolower($params['domain']) : strtolower(self::$request->getServer('HTTP_HOST'));
        $domains = [$domain, '*'];
        
        unset($params['verb'], $params['domain']);
    
        $reverseRoutes = self::$routes->getReverseRoutes();
        
        foreach ($domains as $domain) {
            if (is_null($from = $reverseRoutes[$type]["$verb::$domain::$key"] ?? null))
                continue;
    
            return $this->fillReverseRouteParams($from, $params);
        }
        
        return '';
    }
    
    protected function fillReverseRouteParams(string $from, ? array $params = null) : string
    {
        preg_match_all('/\(([^)]+)\)/', $from, $matches);
        
        if (empty($matches[0]))
            return '/' . $from;
        
        foreach ($matches[0] as $index => $pattern) {
            if (! isset($params[$index]))
                throw new ArgumentCountError('Too few arguments to method fillReverseRouteParams.');
            
            if (! preg_match('#^' . $pattern . '$#u', $params[$index]))
                throw new InvalidArgumentException('A parameter does not match the expected type.');
            
            $pos = strpos($from, $pattern);
            $from = substr_replace($from, $params[$index], $pos, strlen($pattern));
        }
        
        return '/' . $from;
    }
}