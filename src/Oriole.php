<?php

namespace Oriole;

use LogicException;

class Oriole
{
    /**
     * Configs
     *
     * @var array
     *
     * [
     *     'app' => [
     *         'backRootPath' => 'admin',
     *         'frontRootPath' => '/',
     *         'backDomain' => '',
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
        require_once 'Common.php';
        
        $this->defineConstants();
        $this->setConfigs();
        $this->setRoutes();
    }
    
    /**
     * Run Oriole application
     *
     * @return void
     */
    public function run() : void
    {
        
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
        
        self::$configs['app'] = (new \App\Config\App)->getProperties();
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
        (new \Oriole\Config\Routes)->setRoutes();
        
        if (class_exists('\App\Config\Routes')) {
            if (! is_subclass_of('\App\Config\Routes', '\Oriole\Config\AbstractRoutes'))
                throw new LogicException('Routes config class is not extended from Oriole abstract routes config class.');
            
            (new \App\Config\Routes)->setRoutes();
        }
    }
}