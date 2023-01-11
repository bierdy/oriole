<?php

namespace Oriole;

use Oriole\HTTP\Request;
use Oriole\Router\Router;
use Oriole\Router\Routes;
use App\Config\App;
use App\Config\Cookie;
use LogicException;

class Oriole
{
    /**
     * Initiated flag
     *
     * @var bool
     */
    protected static bool $is_initiated = false;
    
    /**
     * Configs
     *
     * @var array
     *
     * [
     *     'app' => [
     *         'adminRootPath' => 'admin',
     *         'publicRootPath' => '/',
     *         'adminDomain' => '',
     *     ],
     * ]
     */
    protected static array $configs = [];
    
    /**
     * Init Oriole application
     *
     * @return void
     */
    public function init() : void
    {
        if (self::$is_initiated === true)
            return;
        
        require_once 'Common.php';
        
        $this->defineConstants();
        $this->setConfigs();
        $this->setRoutes();
        
        self::$is_initiated = true;
    }
    
    /**
     * Run Oriole application
     *
     * @return void
     */
    public function run() : void
    {
        $routes = Routes::getInstance();
        $request = new Request();
    
        $router = new Router($routes, $request);
    
        if (! $router->defineRoute())
            throw new LogicException('Can\'t find current route.');
        
        $router->runHandler();
    }
    
    /**
     * Define constants
     *
     * @return void
     */
    protected function defineConstants() : void
    {
        define('ORIOLE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
        define('ORIOLE_CONFIG_PATH', ORIOLE_PATH . 'Config' . DIRECTORY_SEPARATOR);
    }
    
    /**
     * Set configs
     *
     * @return void
     */
    protected function setConfigs(): void
    {
        if (! class_exists('\App\Config\App'))
            throw new LogicException('App config class is not defined.');
        
        if (! is_subclass_of('\App\Config\App', '\Oriole\Config\App'))
            throw new LogicException('App config class is not extended from Oriole app config class.');
        
        self::$configs['app'] = (new App)->getProperties();
        
        if (class_exists('\App\Config\Cookie'))
            self::$configs['cookie'] = (new Cookie)->getProperties();
        else
            self::$configs['cookie'] = (new Config\Cookie)->getProperties();
    }
    
    public function getConfig(string $group, string $name = '') : mixed
    {
        if (empty($name))
            return self::$configs[$group] ?? '';
        
        return self::$configs[$group][$name] ?? '';
    }
    
    /**
     * Set routes
     *
     * @return void
     */
    protected function setRoutes(): void
    {
        (new Config\Routes)->setRoutes();
        
        if (class_exists('\App\Config\Routes')) {
            if (! is_subclass_of('\App\Config\Routes', '\Oriole\Config\AbstractRoutes'))
                throw new LogicException('Routes config class is not extended from Oriole abstract routes config class.');
            
            (new \App\Config\Routes)->setRoutes();
        }
    }
}