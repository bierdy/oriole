<?php

namespace Oriole\Models;

class TemplateVariableModel extends BaseModel
{
    public string $table = 'oriole_template_variables';
    
    public string $primaryField = 'id';
    
    public array $validationRules = [
        'template_id' => 'required|numeric',
        'variable_id' => 'required|numeric',
    ];
    
    public array $validationMessages = [];
}