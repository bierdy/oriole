<?php

namespace Oriole\Models;

class VariableGroupModel extends BaseModel
{
    public string $table = 'oriole_variable_groups';
    
    public string $primaryField = 'id';
    
    public array $validationRules = [
        'title' => 'required',
    ];
    
    public array $validationMessages = [];
}