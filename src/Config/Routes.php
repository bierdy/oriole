<?php

namespace Oriole\Config;

class Routes extends AbstractRoutes
{
    public function setRoutes() : void
    {
        $appConfig = $this->appConfig;
        $routes = $this->routes;
        
        $routes->group($appConfig['backRootPath'], ['namespace' => 'Wagtail\Controllers\Back', 'domains' => $appConfig['backDomain']], function($routes)
        {
            $routes->get('', 'Home::index');
            
            $routes->group('templates', function($routes)
            {
                $routes->get('', 'Templates::list');
                $routes->match(['get', 'post'], 'add', 'Templates::add');
                $routes->match(['get', 'post'], 'edit/(:num)', 'Templates::edit/$0');
                $routes->get('activate/(:num)', 'Templates::activate/$0');
                $routes->get('deactivate/(:num)', 'Templates::deactivate/$0');
                $routes->get('delete/(:num)', 'Templates::delete/$0');
                $routes->get('delete-all', 'Templates::deleteAll');
            });
            
            $routes->group('variables', function($routes)
            {
                $routes->get('', 'Variables::list');
                $routes->match(['get', 'post'], 'add', 'Variables::add');
                $routes->match(['get', 'post'], 'edit/(:num)', 'Variables::edit/$0');
                $routes->get('activate/(:num)', 'Variables::activate/$0');
                $routes->get('deactivate/(:num)', 'Variables::deactivate/$0');
                $routes->get('delete/(:num)', 'Variables::delete/$0');
                $routes->get('delete-all', 'Variables::deleteAll');
                $routes->get('delete-value/(:num)', 'Variables::deleteValue/$0');
            });
            
            $routes->group('resources', function($routes)
            {
                $routes->match(['get', 'post'], 'add/(:num)', 'Resources::add/$0');
                $routes->match(['get', 'post'], 'edit/(:num)', 'Resources::edit/$0');
                $routes->get('activate/(:num)', 'Resources::activate/$0');
                $routes->get('deactivate/(:num)', 'Resources::deactivate/$0');
                $routes->get('delete/(:num)', 'Resources::delete/$0');
                $routes->get('set-template/(:num)/(:num)', 'Resources::setTemplate/$0/$1');
                $routes->get('set-parent/(:num)/(:num)', 'Resources::setParent/$0/$1');
                $routes->get('set-order/(:num)/(:num)', 'Resources::setOrder/$0/$1');
            });
            
            $routes->group('languages', function($routes)
            {
                $routes->get('', 'Languages::list');
                $routes->match(['get', 'post'], 'add', 'Languages::add');
                $routes->match(['get', 'post'], 'edit/(:num)', 'Languages::edit/$0');
                $routes->get('activate/(:num)', 'Languages::activate/$0');
                $routes->get('deactivate/(:num)', 'Languages::deactivate/$0');
                $routes->get('delete/(:num)', 'Languages::delete/$0');
                $routes->get('delete-all', 'Languages::deleteAll');
                $routes->get('set-default/(:num)', 'Languages::setDefault/$0');
            });
            
            $routes->group('variable-groups', function($routes)
            {
                $routes->match(['get', 'post'], 'add/(:num)', 'VariableGroups::add/$0');
                $routes->match(['get', 'post'], 'edit/(:num)', 'VariableGroups::edit/$0');
                $routes->get('delete/(:num)', 'VariableGroups::delete/$0');
            });
            
            $routes->get('get-assets', 'Assets::get');
        });
    }
}