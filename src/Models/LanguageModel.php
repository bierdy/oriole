<?php

namespace Oriole\Models;

class LanguageModel extends BaseModel
{
    public string $table = 'oriole_languages';
    
    public string $primaryKey = 'id';
    
    public array $validationRules = [
        'title' => 'required|is_unique[languages.title,id,{id}]',
        'code' => 'is_unique[languages.code,id,{id}]',
        'icon' => 'required',
        'order' => 'required|numeric',
    ];
    
    public array $validationMessages = [];
}