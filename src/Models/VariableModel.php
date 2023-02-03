<?php

namespace Oriole\Models;

class VariableModel extends BaseModel
{
    public string $table = 'oriole_variables';
    
    public string $primaryKey = 'id';
    
    public array $validationRules = [
        'title' => 'required|is_unique[oriole_variables.title,id,{id}]',
        'alias' => 'required|is_unique[oriole_variables.alias,id,{id}]|alpha_dash',
        'variable_handler' => 'required|is_class_exist|class_is_not_implement_interface[Oriole\Variables\VariableInterface]',
        'settings' => 'permit_empty|valid_json',
        'variable_view' => 'required',
        'validation_rules' => 'permit_empty|valid_json',
    ];
    
    public array $validationMessages = [];
}