<?php

namespace Oriole\Controllers;

use Exception;

class VariablesController extends BaseController
{
    public function list()
    {
        $variables = $this->baseModel
            ->select("v.*, COUNT(DISTINCT tv.id) AS templates_count, COUNT(DISTINCT vv.id) AS values_count, l.title AS language_title")
            ->from("{$this->variableModel->table} AS v")
            ->join('left', "{$this->templateVariableModel->table} AS tv", 'v.id = tv.variable_id')
            ->join('left', "{$this->variableValueModel->table} AS vv", 'v.id = vv.variable_id')
            ->join('left', "{$this->languageModel->table} AS l", 'v.language_id = l.id')
            ->groupBy('v.id')
            ->orderBy('v.id')
            ->findAll();
        
        $templates_count = array_reduce($variables, fn($count, $variable) => $count + $variable->templates_count, 0);
        $values_count = array_reduce($variables, fn($count, $variable) => $count + $variable->values_count, 0);
        
        $custom_data = [
            'title' => 'Variables',
            'variables' => $variables,
            'templates_count' => $templates_count,
            'values_count' => $values_count,
        ];
        
        $data = array_merge($this->default_data, $custom_data);
        
        return $this->baseView->render('templates/variables/list.php', $data);
    }
}