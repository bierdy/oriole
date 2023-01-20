<?php

namespace Oriole\Models;

class VariableModel extends BaseModel
{
    public string $table = 'oriole_variables';
    
    public string $primaryKey = 'id';
    
    public array $validationRules = [
        'title' => 'required|is_unique[variables.title,id,{id}]',
        'name' => 'required|is_unique[variables.name,id,{id}]|alpha_dash',
        'class' => 'required|is_class_exist|class_is_not_implement_interface[Wagtail\Variables\VariableInterface]',
        'options' => 'permit_empty|valid_json',
        'template' => 'required',
        'validation_rules' => 'permit_empty|valid_json',
    ];
    
    public array $validationMessages = [];
}