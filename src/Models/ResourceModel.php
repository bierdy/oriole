<?php

namespace Oriole\Models;

class ResourceModel extends BaseModel
{
    public string $table = 'oriole_resources';
    
    public string $primaryField = 'id';
    
    public array $validationRules = [
        'title' => 'required|is_unique[oriole_resources.title,id,{id}]',
        'parent_id' => 'required|numeric',
        'template_id' => 'required|numeric',
        'sort_order' => 'required|numeric',
    ];
    
    public array $validationMessages = [];
}