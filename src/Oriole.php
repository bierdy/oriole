<?php

namespace Oriole;

class Oriole
{
    /**
     * Init Oriole application
     *
     * @return void
     */
    public function init() : void
    {
        require_once 'Common.php';
        
        $this->defineConstants();
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
    
    protected function defineConstants() : void
    {
        define('ORIOLE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
        define('ORIOLE_CONFIG_PATH', ORIOLE_PATH . 'Config' . DIRECTORY_SEPARATOR);
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