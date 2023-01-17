<?php

namespace Oriole\Controllers;

class HomeController extends BaseController
{
    public function index() : string
    {
        $custom_data = [
            'title' => 'Home',
        ];
        
        $data = array_merge($this->default_data, $custom_data);
        
        return $this->baseView->render('templates/home.php', $data);
    }
}