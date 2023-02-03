<?php

namespace Oriole\Models;

class VariableGroupVariableModel extends BaseModel
{
    public string $table = 'oriole_variable_group_variables';
    
    public string $primaryKey = 'id';
    
    public array $validationRules = [
        'variable_group_id' => 'required',
        'variable_id' => 'required',
        'sort_order' => 'required|numeric',
    ];
    
    public array $validationMessages = [];
}