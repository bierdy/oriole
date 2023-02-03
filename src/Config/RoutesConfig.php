<?php

namespace Oriole\Config;

class RoutesConfig extends AbstractRoutesConfig
{
    public function setRoutes() : void
    {
        $appConfig = $this->appConfig;
        $routes = $this->routes;
        
        $routes->group($appConfig['adminBasePath'], ['namespace' => 'Oriole\Controllers', 'domains' => $appConfig['adminDomain']], function($routes)
        {
            $routes->get('', 'HomeController::index', ['as' => 'admin_home']);
            
            $routes->group('templates', function($routes)
            {
                $routes->get('', 'TemplatesController::list', ['as' => 'templates_list']);
                $routes->match(['get', 'post'], 'add', 'TemplatesController::add');
                $routes->match(['get', 'post'], 'edit/(:num)', 'TemplatesController::edit/$0');
                $routes->get('activate/(:num)', 'TemplatesController::activate/$0');
                $routes->get('deactivate/(:num)', 'TemplatesController::deactivate/$0');
                $routes->get('delete/(:num)', 'TemplatesController::delete/$0');
                $routes->get('delete-all', 'TemplatesController::deleteAll');
            });
            
            $routes->group('variables', function($routes)
            {
                $routes->get('', 'VariablesController::list', ['as' => 'variables_list']);
                $routes->match(['get', 'post'], 'add', 'VariablesController::add', ['as' => 'add_variable']);
                $routes->match(['get', 'post'], 'edit/(:num)', 'VariablesController::edit/$0', ['as' => 'edit_variable']);
                $routes->get('activate/(:num)', 'VariablesController::activate/$0', ['as' => 'activate_variable']);
                $routes->get('deactivate/(:num)', 'VariablesController::deactivate/$0', ['as' => 'deactivate_variable']);
                $routes->get('delete/(:num)', 'VariablesController::delete/$0', ['as' => 'delete_variable']);
                $routes->get('delete-all', 'VariablesController::deleteAll', ['as' => 'delete_all_variables']);
                $routes->get('delete-value/(:num)', 'VariablesController::deleteValue/$0', ['as' => 'delete_variable_value']);
            });
            
            $routes->group('resources', function($routes)
            {
                $routes->match(['get', 'post'], 'add/(:num)', 'ResourcesController::add/$0');
                $routes->match(['get', 'post'], 'edit/(:num)', 'ResourcesController::edit/$0');
                $routes->get('activate/(:num)', 'ResourcesController::activate/$0');
                $routes->get('deactivate/(:num)', 'ResourcesController::deactivate/$0');
                $routes->get('delete/(:num)', 'ResourcesController::delete/$0');
                $routes->get('set-template/(:num)/(:num)', 'ResourcesController::setTemplate/$0/$1');
                $routes->get('set-parent/(:num)/(:num)', 'ResourcesController::setParent/$0/$1');
                $routes->get('set-order/(:num)/(:num)', 'ResourcesController::setOrder/$0/$1');
            });
            
            $routes->group('languages', function($routes)
            {
                $routes->get('', 'LanguagesController::list', ['as' => 'languages_list']);
                $routes->match(['get', 'post'], 'add', 'LanguagesController::add', ['as' => 'add_language']);
                $routes->match(['get', 'post'], 'edit/(:num)', 'LanguagesController::edit/$0', ['as' => 'edit_language']);
                $routes->get('activate/(:num)', 'LanguagesController::activate/$0', ['as' => 'activate_language']);
                $routes->get('deactivate/(:num)', 'LanguagesController::deactivate/$0', ['as' => 'deactivate_language']);
                $routes->get('delete/(:num)', 'LanguagesController::delete/$0', ['as' => 'delete_language']);
                $routes->get('delete-all', 'LanguagesController::deleteAll', ['as' => 'delete_all_languages']);
                $routes->get('set-default/(:num)', 'LanguagesController::setDefault/$0', ['as' => 'set_default_language']);
            });
            
            $routes->group('variable-groups', function($routes)
            {
                $routes->match(['get', 'post'], 'add/(:num)', 'VariableGroupsController::add/$0');
                $routes->match(['get', 'post'], 'edit/(:num)', 'VariableGroupsController::edit/$0');
                $routes->get('delete/(:num)', 'VariableGroupsController::delete/$0');
            });
            
            $routes->get('get-assets/(:any)/(:segment)/(:segment)', 'AssetsController::get/$0/$1/$2', ['as' => 'get_assets']);
        });
    }
}