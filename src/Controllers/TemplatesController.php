<?php

namespace Oriole\Controllers;

use Exception;

class TemplatesController extends BaseController
{
    public function list()
    {
        $templates = $this->baseModel
            ->select("t.*, COUNT(DISTINCT r.id) AS resources_count")
            ->from("{$this->templateModel->table} AS t")
            ->join('left', "{$this->resourceModel->table} AS r", 't.id = r.template_id')
            ->groupBy('t.id')
            ->orderBy('t.id')
            ->findAll();
    
        $resources_count = array_reduce($templates, fn($count, $template) => $count + $template->resources_count, 0);
        
        $custom_data = [
            'title' => 'Templates',
            'templates' => $templates,
            'resources_count' => $resources_count,
        ];
        
        $data = array_merge($this->default_data, $custom_data);
        
        return $this->baseView->render('templates/templates/list.php', $data);
    }
    
    /**
     * @throws Exception
     */
    public function add()
    {
        $requestMethod = $this->request->getRequestMethod();
        $post = $this->request->getPost();
        
        if ($requestMethod === 'post') {
            if (($id = $this->templateModel->addOne($post)) === false) {
                $message = 'Validation errors:';
                $errors = $this->templateModel->errors();
            } else {
                setOrioleCookie('message', 'The template was successfully created.');
                return $this->response->redirect(route_by_alias('edit_template', $id));
            }
        }
        
        $custom_data = [
            'title' => 'Add template',
            'post' => $post,
            'message' => $message ?? '',
            'errors' => $errors ?? [],
        ];
        $data = array_merge($this->default_data, $custom_data);
        
        return $this->baseView->render('templates/templates/add.php', $data);
    }
    
    /**
     * @throws Exception
     */
    public function edit(int $id = 0)
    {
        $requestMethod = $this->request->getRequestMethod();
        $post = $this->request->getPost();
        
        if ($requestMethod === 'post') {
            $post_variables = [];
            
            if (! empty($post['variables']))
                foreach($post['variables'] as $post_variable_id => $post_variable)
                    $post_variables[] = [
                        'template_id' => $id,
                        'variable_id' => $post_variable_id,
                        'template_variable_id' => $post_variable['template_variable_id'],
                        'sort_order' => $post_variable['sort_order'],
                        'checked' => ! empty($post_variable['checked']),
                        'variable_group_id' => $post_variable['variable_group_id'],
                        'variable_group_id_original' => ! empty($post_variable['variable_group_id_original']) ? $post_variable['variable_group_id_original'] : '',
                    ];
            
            $post_template_variable_groups = [];
            
            if (! empty($post['template_variable_groups']))
                foreach($post['template_variable_groups'] as $post_template_variable_group)
                    $post_template_variable_groups[] = [
                        'variable_group_id' => $post_template_variable_group['id'],
                        'sort_order' => $post_template_variable_group['sort_order'],
                    ];
            
            $this->templateModel->validate($post);
            $errors = array_merge([], $this->templateModel->errors());
            
            if (! empty($errors)) {
                $message = 'Validation errors:';
            } else {
                $this->templateModel->reset()->updateOne($id, $post);
                foreach($post_variables as $post_variable) {
                    $checked = $post_variable['checked'];
                    $template_variable_id = $post_variable['template_variable_id'];
                    
                    if ($checked && $template_variable_id)
                        $this->templateVariableModel->updateOne($template_variable_id, $post_variable);
                    elseif ($checked)
                        $this->templateVariableModel->addOne($post_variable);
                    elseif ($template_variable_id)
                        $this->templateVariableModel->deleteOne($template_variable_id);
                    
                    $variable_group_variable = $this->variableGroupVariableModel
                        ->select('*')
                        ->from($this->variableGroupVariableModel->table)
                        ->where('variable_group_id', '=', $post_variable['variable_group_id_original'])
                        ->andWhere('variable_id', '=', $post_variable['variable_id'])
                        ->findOne();
                    
                    $this->variableGroupVariableModel->reset();
                    
                    if ($checked && $post_variable['variable_group_id'] && $variable_group_variable !== false)
                        $this->variableGroupVariableModel->updateOne($variable_group_variable->id, $post_variable);
                    elseif ($checked && $post_variable['variable_group_id'])
                        $this->variableGroupVariableModel->addOne($post_variable);
                    elseif ($variable_group_variable !== false)
                        $this->variableGroupVariableModel->deleteOne($variable_group_variable->id);
                }
                
                foreach($post_template_variable_groups as $post_template_variable_group)
                    $this->templateVariableGroupModel
                        ->set(['sort_order' => $post_template_variable_group['sort_order']])
                        ->where('variable_group_id', '=', $post_template_variable_group['variable_group_id'])
                        ->update();
                
                setOrioleCookie('message', 'The template was successfully updated.');
                return $this->response->redirect(route_by_alias('edit_template', $id));
            }
        }
        
        $template = $this->templateModel->reset()->getOne($id);
        $variables = $this->variableModel->reset()->select('*')->from($this->variableModel->table)->orderBy('title')->findAll();
        $template_variables = $this->templateVariableModel->reset()->select('*')->from($this->templateVariableModel->table)->where('template_id', '=', $id)->findAll();
        
        $template_variable_groups = $this->baseModel
            ->reset()
            ->select('vg.*, tvg.sort_order AS sort_order')
            ->from("{$this->variableGroupModel->table} AS vg")
            ->join('inner', "{$this->templateVariableGroupModel->table} AS tvg", 'vg.id = tvg.variable_group_id')
            ->where('tvg.template_id', '=', $id)
            ->orderBy('sort_order')
            ->findAll();
        
        $variable_group_variables = ! empty($template_variables) && ! empty($template_variable_groups) ? $this->variableGroupVariableModel
            ->reset()
            ->select('*')
            ->from($this->variableGroupVariableModel->table)
            ->whereIn('variable_group_id', array_column($template_variable_groups, 'id'))
            ->andWhereIn('variable_id', array_column($template_variables, 'variable_id'))
            ->orderBy('sort_order')
            ->findAll() : [];
        
        $custom_data = [
            'title' => 'Edit template "' . $template->title . '"',
            'post' => $post,
            'template' => $template,
            'variables' => ! empty($variables) ? array_combine(array_column($variables, 'id'), $variables) : [],
            'template_variables' => ! empty($template_variables) ? array_combine(array_column($template_variables, 'variable_id'), $template_variables) : [],
            'template_variable_groups' => $template_variable_groups,
            'variable_group_variables' => $variable_group_variables,
            'message' => getOrioleCookie('message', true) ?? $message ?? '',
            'errors' => $errors ?? [],
        ];
        $data = array_merge($this->default_data, $custom_data);
        
        return $this->baseView->render('templates/templates/edit.php', $data);
    }
    
    /**
     * @throws Exception
     */
    public function activate($id)
    {
        $this->templateModel->updateOne($id, ['is_active' => 1]);
        
        return $this->response->redirect(route_by_alias('templates_list'));
    }
    
    /**
     * @throws Exception
     */
    public function deactivate($id)
    {
        $this->templateModel->updateOne($id, ['is_active' => 0]);
        
        return $this->response->redirect(route_by_alias('templates_list'));
    }
    
    /**
     * @throws Exception
     */
    public function delete($id)
    {
        $this->templateModel->deleteOneTemplate($id);
        
        return $this->response->redirect(route_by_alias('templates_list'));
    }
}