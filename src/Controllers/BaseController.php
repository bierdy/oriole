<?php

namespace Oriole\Controllers;

use Oriole\Oriole;
use Oriole\HTTP\Request;
use Oriole\HTTP\Response;
use Oriole\Models\BaseModel;
use Oriole\Models\ResourceModel;
use Oriole\Models\TemplateModel;
use Oriole\Models\TemplateVariableModel;
use Oriole\Models\LanguageModel;
use Oriole\Models\VariableModel;
use Oriole\Models\VariableValueModel;
use Oriole\Models\VariableGroupVariableModel;
use Oriole\Views\BaseView;

class BaseController
{
    protected Oriole|null $oriole = null;
    protected Request|null $request = null;
    protected Response|null $response = null;
    protected array|null $appConfig = null;
    protected array|null $cookieConfig = null;
    protected BaseModel|null $baseModel = null;
    protected ResourceModel|null $resourceModel = null;
    protected TemplateModel|null $templateModel = null;
    protected TemplateVariableModel|null $templateVariableModel = null;
    protected LanguageModel|null $languageModel = null;
    protected VariableModel|null $variableModel = null;
    protected VariableValueModel|null $variableValueModel = null;
    protected VariableGroupVariableModel|null $variableGroupVariableModel = null;
    protected BaseView|null $baseView = null;
    protected array $default_data = [];
    
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->oriole = new Oriole();
        $this->request = new Request();
        $this->response = Response::getInstance();
        $this->appConfig = $this->oriole->getConfig('app');
        $this->cookieConfig = $this->oriole->getConfig('cookie');
        $this->baseModel = new BaseModel();
        $this->resourceModel = new ResourceModel();
        $this->templateModel = new TemplateModel();
        $this->templateVariableModel = new TemplateVariableModel();
        $this->languageModel = new LanguageModel();
        $this->variableModel = new VariableModel();
        $this->variableValueModel = new VariableValueModel();
        $this->variableGroupVariableModel = new VariableGroupVariableModel();
        $this->baseView = new BaseView();
        
        $this->default_data = [
            'title' => '',
            //'resources_tree' => $this->resourceModel->getResourcesTree(),
            'appConfig' => json_encode(array_merge($this->appConfig, ['currentBaseURL' => $this->request->getCurrentBaseURL()])),
            'cookieConfig' => json_encode($this->cookieConfig),
            'publicBaseURL' => $this->request->getPublicBaseURL(),
            'headerMenu' => [
                'templates' => [
                    'title' => 'Templates',
                    'link' => route_by_alias('templates_list'),
                    'active' => url_is(route_by_alias('templates_list') . '*'),
                ],
                'variables' => [
                    'title' => 'Variables',
                    'link' => route_by_alias('variables_list'),
                    'active' => url_is(route_by_alias('variables_list') . '*'),
                ],
                'languages' => [
                    'title' => 'Languages',
                    'link' => route_by_alias('languages_list'),
                    'active' => url_is(route_by_alias('languages_list') . '*'),
                ],
            ],
        ];
    }
}