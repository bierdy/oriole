<?php

namespace Oriole\Controllers;

use Exception;

class VariableGroupsController extends BaseController
{
    /**
     * @throws Exception
     */
    public function add(int $template_id = 0)
    {
        $requestMethod = $this->request->getRequestMethod();
        $post = $this->request->getPost();
        
        if ($requestMethod === 'post') {
            if (($id = $this->variableGroupModel->addOne($post)) === false) {
                $message = 'Validation errors:';
                $errors = $this->variableGroupModel->errors();
            } elseif ($this->templateVariableGroupModel->addOne(array_merge($post, ['variable_group_id' => $id])) === false) {
                $message = 'Validation errors:';
                $errors = $this->templateVariableGroupModel->errors();
                
                $this->variableGroupModel->reset()->deleteOne($id);
            } else {
                setOrioleCookie('message', 'The variable group was successfully created.');
                return $this->response->redirect(route_by_alias('edit_template', $template_id));
            }
        }
        
        $template_variable_groups_count = count(
            $this->templateVariableGroupModel
                ->reset()
                ->select('*')
                ->from($this->templateVariableGroupModel->table)
                ->where('template_id', '=', $template_id)
                ->findAll()
        );
        
        $custom_data = [
            'title' => 'Add variable group',
            'post' => $post,
            'template_id' => $template_id,
            'template_variable_groups_count' => $template_variable_groups_count,
            'message' => $message ?? '',
            'errors' => $errors ?? [],
        ];
        $data = array_merge($this->default_data, $custom_data);
        
        return $this->baseView->render('templates/variable_groups/add.php', $data);
    }
}