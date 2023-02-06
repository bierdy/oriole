<?php

namespace Oriole\Models;

class VariableGroupModel extends BaseModel
{
    public string $table = 'oriole_variable_groups';
    
    public string $primaryKey = 'id';
    
    public array $validationRules = [
        'title' => 'required',
    ];
    
    public array $validationMessages = [];
}