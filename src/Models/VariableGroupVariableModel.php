<?php

namespace Oriole\Models;

class VariableGroupVariableModel extends BaseModel
{
    public string $table = 'oriole_variable_group_variables';
    
    public string $primaryField = 'id';
    
    public array $fields = ['variable_group_id', 'variable_id', 'sort_order'];
    
    public array $validationRules = [
        'variable_group_id' => 'required|numeric',
        'variable_id' => 'required|numeric',
        'sort_order' => 'required|numeric',
    ];
    
    public array $validationMessages = [];
}