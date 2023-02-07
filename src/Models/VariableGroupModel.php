<?php

namespace Oriole\Models;

use Exception;

class VariableGroupModel extends BaseModel
{
    public string $table = 'oriole_variable_groups';
    
    public string $primaryField = 'id';
    
    public array $fields = ['title'];
    
    public array $validationRules = [
        'title' => 'required',
    ];
    
    public array $validationMessages = [];
    
    /**
     * @throws Exception
     */
    public function deleteOneVariableGroup(string|int|float $primaryField) : bool
    {
        $variableGroupVariableModel = new VariableGroupVariableModel();
        $templateVariableGroupModel = new TemplateVariableGroupModel();
        
        $this->beginTransaction();
        
        $this->deleteOne($primaryField);
        
        $variableGroupVariableModel->where('variable_group_id', '=', $primaryField)->delete();
        $this->errors = array_merge_recursive($this->errors, $variableGroupVariableModel->errors());
        
        $template_variable_group = $templateVariableGroupModel
            ->select('*')
            ->from($templateVariableGroupModel->table)
            ->where('variable_group_id', '=', $primaryField)
            ->findOne();
        
        $templateVariableGroupModel->reset()->deleteOne($template_variable_group->id);
        $this->errors = array_merge_recursive($this->errors, $templateVariableGroupModel->errors());
        
        $template_variable_groups_ = $templateVariableGroupModel
            ->reset()
            ->select('*')
            ->where('template_id', '=', $template_variable_group->template_id)
            ->orderBy('sort_order')
            ->findAll();
        
        foreach($template_variable_groups_ as $key_ => $template_variable_group_) {
            $templateVariableGroupModel->reset()->updateOne($template_variable_group_->id, ['sort_order' => $key_]);
            $this->errors = array_merge_recursive($this->errors, $templateVariableGroupModel->errors());
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