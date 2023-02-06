<?php

namespace Oriole\Models;

use Exception;

class VariableModel extends BaseModel
{
    public string $table = 'oriole_variables';
    
    public string $primaryField = 'id';
    
    public array $fields = ['title', 'alias', 'is_active', 'variable_handler', 'variable_view', 'settings', 'validation_rules', 'language_id'];
    
    public array $validationRules = [
        'title' => 'required|is_unique[oriole_variables.title,id,{id}]',
        'alias' => 'required|is_unique[oriole_variables.alias,id,{id}]|alpha_dash',
        'variable_handler' => 'required|is_class_exist|class_is_not_implement_interface[Oriole\Variables\VariableInterface]',
        'settings' => 'permit_empty|valid_json',
        'variable_view' => 'required',
        'validation_rules' => 'permit_empty|valid_json',
    ];
    
    public array $validationMessages = [];
    
    /**
     * @throws Exception
     */
    public function deleteOneVariable(string|int|float $primaryField) : bool
    {
        $templateVariableModel = new TemplateVariableModel();
        $variableValueModel = new VariableValueModel();
        $variableGroupVariableModel = new VariableGroupVariableModel();
        
        $this->beginTransaction();
        
        $this->deleteOne($primaryField);
        
        $templateVariableModel->where('variable_id', '=', $primaryField)->delete();
        $this->errors = array_merge_recursive($this->errors, $templateVariableModel->errors());
        
        $variableValueModel->where('variable_id', '=', $primaryField)->delete();
        $this->errors = array_merge_recursive($this->errors, $variableValueModel->errors());
        
        $variable_group_variables = $variableGroupVariableModel->select('*')->from($variableGroupVariableModel->table)->where('variable_id', '=', $primaryField)->findAll();
        
        if (! empty($variable_group_variables)) {
            foreach($variable_group_variables as $variable_group_variable) {
                $variableGroupVariableModel->reset()->deleteOne($variable_group_variable->id);
                $this->errors = array_merge_recursive($this->errors, $variableGroupVariableModel->errors());
                
                $variable_group_variables_ = $variableGroupVariableModel
                    ->reset()
                    ->select('*')
                    ->from($variableGroupVariableModel->table)
                    ->where('variable_group_id', '=', $variable_group_variable->variable_group_id)
                    ->orderBy('sort_order ASC')
                    ->findAll();
                
                foreach($variable_group_variables_ as $key_ => $variable_group_variable_) {
                    $variableGroupVariableModel->reset()->updateOne($variable_group_variable_->id, ['sort_order' => $key_]);
                    $this->errors = array_merge_recursive($this->errors, $variableGroupVariableModel->errors());
                }
            }
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
    
    /**
     * @throws Exception
     */
    public function deleteManyVariables(array $primaryFields) : bool
    {
        if (empty($primaryFields)) {
            if (empty($this->primaryField)) {
                $this->errors['logic'][] = 'Primary key is empty';
                
                return false;
            }
    
            $primaryFields = array_column($this->getAll(), $this->primaryField);
        }
        
        foreach ($primaryFields as $primaryField) {
            if ($this->deleteOneVariable($primaryField) === false)
                return false;
        }
        
        return true;
    }
    
    /**
     * @throws Exception
     */
    public function deleteOneVariableValue(int $value_id) : bool
    {
        $variableValueModel = new VariableValueModel();
        $resourceModel = new ResourceModel();
        
        $variable_value = $variableValueModel->getOne($value_id);
        $resource = $resourceModel->getOne($variable_value->resource_id);
        $variable = $this->getOne($variable_value->variable_id);
        $variable_handler = new $variable->variable_handler([], $resource, $variable);
        
        if (method_exists($variable_handler, 'deleteValue')) {
            $variable_handler->deleteValue($value_id);
        } else {
            $variableValueModel->reset()->deleteOne($value_id);
            setOrioleCookie('message', 'The value of "' . ucfirst(mb_strtolower($variable->title)) . '" variable was successfully deleted.');
        }
        
        return true;
    }
}