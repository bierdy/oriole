<?php

namespace Oriole\Models;

class LanguageModel extends BaseModel
{
    public string $table = 'oriole_languages';
    
    public string $primaryKey = 'id';
    
    public array $validationRules = [
        'title'      => 'required|is_unique[languages.title,id,{id}]',
        'alias'      => 'is_unique[languages.alias,id,{id}]',
        'icon'       => 'required',
        'sort_order' => 'required|numeric',
    ];
    
    public array $validationMessages = [];
}