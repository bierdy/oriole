<?php

namespace Oriole\Models;

class TemplateVariableModel extends BaseModel
{
    public string $table = 'oriole_template_variables';
    
    public string $primaryKey = 'id';
    
    public array $validationRules = [
        'template_id' => 'required',
        'variable_id' => 'required',
    ];
    
    public array $validationMessages = [];
}