<?php

namespace Oriole\Models;

class VariableValueModel extends BaseModel
{
    public string $table = 'oriole_variable_values';
    
    public string $primaryField = 'id';
    
    public array $fields = ['resource_id', 'variable_id', 'variable_value', 'sort_order'];
    
    public array $validationRules = [
        'resource_id' => 'required|numeric',
        'variable_id' => 'required|numeric',
        'sort_order' => 'required|numeric',
    ];
    
    public array $validationMessages = [];
}