<?php

namespace Oriole\Controllers;

use Exception;

class TemplatesController extends BaseController
{
    public function list()
    {
        $templates = $this->baseModel
            ->select("t.*, COUNT(DISTINCT r.id) AS resources_count")
            ->from("{$this->templateModel->table} AS t")
            ->join('left', "{$this->resourceModel->table} AS r", 't.id = r.template_id')
            ->groupBy('t.id')
            ->orderBy('t.id')
            ->findAll();
    
        $resources_count = array_reduce($templates, fn($count, $template) => $count + $template->resources_count, 0);
        
        $custom_data = [
            'title' => 'Templates',
            'templates' => $templates,
            'resources_count' => $resources_count,
        ];
        
        $data = array_merge($this->default_data, $custom_data);
        
        return $this->baseView->render('templates/templates/list.php', $data);
    }
}