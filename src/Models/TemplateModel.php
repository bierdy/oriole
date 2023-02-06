<?php

namespace Oriole\Models;

class TemplateModel extends BaseModel
{
    public string $table = 'oriole_templates';
    
    public string $primaryField = 'id';
    
    public array $fields = ['title', 'icon', 'template_handler', 'is_active', 'is_unique'];
    
    public array $validationRules = [
        'title' => 'required|is_unique[oriole_templates.title,id,{id}]',
        'template_handler' => 'permit_empty|is_class_exist|is_method_exist',
    ];
    
    public array $validationMessages = [];
    
    /*
     * Get non-unique templates plus unique templates that are not assigned on any resource.
     */
    public function getAvailableTemplates() : array
    {
        $resourceModel = new ResourceModel();
        
        return $this
            ->select("{$this->table}.*")
            ->from($this->table)
            ->whereNotIn('id', function ($templateModel) use ($resourceModel) {
                $templateModel
                    ->select('r.template_id')
                    ->from($resourceModel->table . ' AS r')
                    ->join('left', "{$this->table} AS t", 't.id = r.template_id')
                    ->where('t.is_unique', '=', 1);
            })
            ->findAll();
    }
}