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
        if (defined('CONFIG_PATH') && is_file(CONFIG_PATH . 'Routes.php'))
            require_once CONFIG_PATH . 'Routes.php';
        
        require_once ORIOLE_CONFIG_PATH . 'Routes.php';
    }
}