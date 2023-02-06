<?php

namespace Oriole\Models;

class TemplateVariableGroupModel extends BaseModel
{
    public string $table = 'oriole_template_variable_groups';
    
    public string $primaryKey = 'id';
    
    public array $validationRules = [
        'template_id' => 'required|numeric',
        'variable_group_id' => 'required|numeric',
        'sort_order' => 'required|numeric',
    ];
    
    public array $validationMessages = [];
}