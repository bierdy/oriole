<?php

namespace Oriole;

use Oriole\HTTP\Request;
use Oriole\HTTP\Response;
use Oriole\Router\Router;
use Oriole\Router\Routes;
use App\Config\App;
use App\Config\Database;
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
     *         'adminBasePath' => 'admin',
     *         'publicBasePath' => '/',
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
        ob_start();
        
        $routes = Routes::getInstance();
        $request = new Request();
        $response = Response::getInstance();
        
        $router = new Router($routes, $request);
        
        if (! $router->defineRoute())
            throw new LogicException('Can\'t find current route.');
        
        $body = $router->runHandler();
        
        if (! is_null($body))
            $response->setBody($body);
        else
            $response->setBody(ob_get_contents());
    
        ob_end_clean();
        
        $response->send();
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
            throw new LogicException('App config class is not extended from Oriole App config class.');
        
        self::$configs['app'] = (new App)->getProperties();
        
        
        
        if (! class_exists('\App\Config\Database'))
            throw new LogicException('Database config class is not defined.');
        
        if (! is_subclass_of('\App\Config\Database', '\Oriole\Config\Database'))
            throw new LogicException('Database config class is not extended from Oriole Database config class.');
        
        self::$configs['database'] = (new Database)->getProperties();
        
        
        
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