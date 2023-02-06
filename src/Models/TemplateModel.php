<?php

namespace Oriole\Models;

use Exception;

class TemplateModel extends BaseModel
{
    public string $table = 'oriole_templates';
    
    public string $primaryField = 'id';
    
    public array $fields = ['title', 'icon', 'template_handler', 'is_active', 'is_unique'];
    
    public array $validationRules = [
        'title' => 'required|is_unique[oriole_templates.title,id,{id}]',
        'template_handler' => 'permit_empty|is_class_exist|is_method_exist',
    ];
    
    public array $validationMessages = [];
    
    /*
     * Get non-unique templates plus unique templates that are not assigned on any resource.
     */
    public function getAvailableTemplates() : array
    {
        $resourceModel = new ResourceModel();
        
        return $this
            ->select("{$this->table}.*")
            ->from($this->table)
            ->whereNotIn('id', function ($templateModel) use ($resourceModel) {
                $templateModel
                    ->select('r.template_id')
                    ->from($resourceModel->table . ' AS r')
                    ->join('left', "{$this->table} AS t", 't.id = r.template_id')
                    ->where('t.is_unique', '=', 1);
            })
            ->findAll();
    }
    
    /**
     * @throws Exception
     */
    public function deleteOneTemplate(string|int|float $primaryField) : bool
    {
        $templateVariableModel = new TemplateVariableModel();
        $templateVariableGroupModel = new TemplateVariableGroupModel();
        $variableGroupModel = new VariableGroupModel();
        $variableGroupVariableModel = new VariableGroupVariableModel();
        
        $this->beginTransaction();
        
        $this->deleteOne($primaryField);
        
        $templateVariableModel->where('template_id', '=', $primaryField)->delete();
        $this->errors = array_merge_recursive($this->errors, $templateVariableModel->errors());
        
        $template_variable_groups = $templateVariableGroupModel->select('*')->from($templateVariableGroupModel->table)->where('template_id', '=', $primaryField)->findAll();
        foreach($template_variable_groups as $template_variable_group) {
            $templateVariableGroupModel->reset()->deleteOne($template_variable_group->id);
            $this->errors = array_merge_recursive($this->errors, $templateVariableGroupModel->errors());
            
            $variableGroupModel->deleteOne($template_variable_group->variable_group_id);
            $this->errors = array_merge_recursive($this->errors, $variableGroupModel->errors());
            
            $variableGroupVariableModel->where('variable_group_id', '=', $template_variable_group->variable_group_id)->delete();
            $this->errors = array_merge_recursive($this->errors, $variableGroupVariableModel->errors());
        }
        
        $this->errors = array_diff($this->errors, array('', ' ', null, 0, array()));
        
        if (! empty($this->errors)) {
            $this->rollBackTransaction();
            return false;
        }
        
        if ($this->submitTransaction())
            return true;
        
        return false;
    }
}