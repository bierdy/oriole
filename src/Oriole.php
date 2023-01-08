<?php

namespace Oriole;

class Oriole
{
    /**
     * Run Oriole application
     *
     * @return void
     */
    public function run() : void
    {
        require_once 'Common.php';
        
        $this->defineConstants();
        $this->addRoutes();
    }
    
    protected function defineConstants() : void
    {
        define('ORIOLE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
        define('ORIOLE_CONFIG_PATH', ORIOLE_PATH . 'Config' . DIRECTORY_SEPARATOR);
    }
    
    /**
     * Add routes
     *
     * @return void
     */
    protected function addRoutes(): void
    {
        if (defined('CONFIG_PATH') && is_file(CONFIG_PATH . 'Routes.php'))
            require_once CONFIG_PATH . 'Routes.php';
        
        require_once ORIOLE_CONFIG_PATH . 'Routes.php';
    }
}