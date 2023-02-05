<?php

namespace Oriole\Models;

class TemplateModel extends BaseModel
{
    public string $table = 'oriole_templates';
    
    public string $primaryKey = 'id';
    
    public array $validationRules = [
        'title' => 'required|is_unique[oriole_templates.title,id,{id}]',
        'route_handler' => 'permit_empty|is_class_exist|is_method_exist',
    ];
    
    public array $validationMessages = [];
}