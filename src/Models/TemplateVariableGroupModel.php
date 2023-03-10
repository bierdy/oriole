<?php

namespace Oriole\Models;

class TemplateVariableGroupModel extends BaseModel
{
    public string $table = 'oriole_template_variable_groups';
    
    public string $primaryField = 'id';
    
    public array $fields = ['template_id', 'variable_group_id', 'sort_order'];
    
    public array $validationRules = [
        'template_id' => 'required|numeric',
        'variable_group_id' => 'required|numeric',
        'sort_order' => 'required|numeric',
    ];
    
    public array $validationMessages = [];
}