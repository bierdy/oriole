<?php

namespace Oriole;

use Oriole\HTTP\Request;
use Oriole\HTTP\Response;
use Oriole\Router\Router;
use Oriole\Router\Routes;
use App\Config\RoutesConfig;
use App\Config\AppConfig;
use App\Config\DatabaseConfig;
use App\Config\CookieConfig;
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
    
        $router = Router::getInstance();
        $response = Response::getInstance();
        
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
     * Set configs
     *
     * @return void
     */
    protected function setConfigs(): void
    {
        if (! class_exists('\App\Config\AppConfig'))
            throw new LogicException('AppConfig class is not defined.');
        
        if (! is_subclass_of('\App\Config\AppConfig', '\Oriole\Config\AppConfig'))
            throw new LogicException('AppConfig class is not extended from Oriole AppConfig class.');
        
        self::$configs['app'] = (new AppConfig)->getProperties();
        
        
        
        if (! class_exists('\App\Config\DatabaseConfig'))
            throw new LogicException('DatabaseConfig class is not defined.');
        
        if (! is_subclass_of('\App\Config\DatabaseConfig', '\Oriole\Config\DatabaseConfig'))
            throw new LogicException('DatabaseConfig class is not extended from Oriole DatabaseConfig class.');
        
        self::$configs['database'] = (new DatabaseConfig)->getProperties();
        
        
        
        if (class_exists('\App\Config\CookieConfig'))
            self::$configs['cookie'] = (new CookieConfig)->getProperties();
        else
            self::$configs['cookie'] = (new Config\CookieConfig)->getProperties();
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
        (new Config\RoutesConfig)->setRoutes();
        
        if (class_exists('\App\Config\RoutesConfig')) {
            if (! is_subclass_of('\App\Config\RoutesConfig', '\Oriole\Config\AbstractRoutesConfig'))
                throw new LogicException('RoutesConfig class is not extended from Oriole AbstractRoutesConfig class.');
            
            (new RoutesConfig)->setRoutes();
        }
    }
}