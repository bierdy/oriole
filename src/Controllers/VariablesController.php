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
    
    /**
     * @throws Exception
     */
    public function add()
    {
        $requestMethod = $this->request->getRequestMethod();
        $post = $this->request->getPost();
        
        unset($post['submit']);
        
        if ($requestMethod === 'post') {
            if (($id = $this->variableModel->addOne($post)) === false) {
                $message = 'Validation errors:';
                $errors = $this->variableModel->errors();
            } else {
                setOrioleCookie('message', 'The variable was successfully created.');
                return $this->response->redirect(route_by_alias('edit_variable', $id));
            }
        }
        
        $languages = $this->languageModel->getAll();
        
        $custom_data = [
            'title' => 'Add variable',
            'post' => $post,
            'languages_options' => ! empty($languages) ? ['0' => 'Empty'] + array_combine(array_column($languages, 'id'), array_column($languages, 'title')) : ['' => 'Languages not found'],
            'message' => $message ?? '',
            'errors' => $errors ?? [],
        ];
        $data = array_merge($this->default_data, $custom_data);
        
        return $this->baseView->render('templates/variables/add.php', $data);
    }
    
    /**
     * @throws Exception
     */
    public function edit(int $id = 0)
    {
        $requestMethod = $this->request->getRequestMethod();
        $post = $this->request->getPost();
        
        unset($post['submit']);
        
        if ($requestMethod === 'post') {
            if ($this->variableModel->updateOne($id, $post) === false) {
                $message = 'Validation errors:';
                $errors = $this->variableModel->errors();
            } else {
                setOrioleCookie('message', 'The variable was successfully updated.');
                return $this->response->redirect(route_by_alias('edit_variable', $id));
            }
        }
        
        $this->variableModel->reset();
        $variable = $this->variableModel->getOne($id);
        $languages = $this->languageModel->getAll();
        
        $custom_data = [
            'title' => 'Edit variable "' . $variable->title . '"',
            'post' => $post,
            'variable' => $variable,
            'languages_options' => ! empty($languages) ? ['0' => 'Empty'] + array_combine(array_column($languages, 'id'), array_column($languages, 'title')) : ['' => 'Languages not found'],
            'message' => getOrioleCookie('message', true) ?? $message ?? '',
            'errors' => $errors ?? [],
        ];
        $data = array_merge($this->default_data, $custom_data);
        
        return $this->baseView->render('templates/variables/edit.php', $data);
    }
    
    /**
     * @throws Exception
     */
    public function activate($id)
    {
        $this->variableModel->updateOne($id, ['is_active' => 1]);
        
        return $this->response->redirect(route_by_alias('variables_list'));
    }
    
    /**
     * @throws Exception
     */
    public function deactivate($id)
    {
        $this->variableModel->updateOne($id, ['is_active' => 0]);
        
        return $this->response->redirect(route_by_alias('variables_list'));
    }
}