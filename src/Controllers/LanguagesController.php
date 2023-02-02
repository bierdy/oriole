<?php

namespace Oriole\Controllers;

class LanguagesController extends BaseController
{
    public function list()
    {
        $languages = $this->baseModel
            ->select("l.*, COUNT(DISTINCT v.id) AS variables_count")
            ->from("{$this->languageModel->table} AS l")
            ->join('left', "{$this->variableModel->table} AS v", 'l.id = v.language_id')
            ->groupBy('l.id')
            ->orderBy('l.id')
            ->findAll();
        
        $variables_count = array_reduce($languages, fn($count, $language) => $count + $language->variables_count, 0);
        
        $custom_data = [
            'title' => 'Languages',
            'languages' => $languages,
            'variables_count' => $variables_count,
        ];
        $data = array_merge($this->default_data, $custom_data);
        
        return $this->baseView->render('templates/languages/list.php', $data);
    }
}