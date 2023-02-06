<?php

namespace Oriole\Models;

class LanguageModel extends BaseModel
{
    public string $table = 'oriole_languages';
    
    public string $primaryField = 'id';
    
    public array $validationRules = [
        'title'      => 'required|is_unique[oriole_languages.title,id,{id}]',
        'alias'      => 'is_unique[oriole_languages.alias,id,{id}]|alpha_dash',
        'icon'       => 'required',
        'sort_order' => 'required|numeric',
    ];
    
    public array $validationMessages = [];
}