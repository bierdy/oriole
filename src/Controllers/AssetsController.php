<?php

namespace Oriole\Controllers;

use Oriole\HTTP\Response;
use Oriole\Views\BaseView;

class AssetsController
{
    protected Response|null $response = null;
    protected BaseView|null $baseView = null;
    
    public function __construct()
    {
        $this->response = Response::getInstance();
        $this->baseView = new BaseView();
    }
    public function get($file_path, $file_name, $file_ext) : string
    {
        $file_path = trim($file_path, '/ ');
        $file_path = ! empty($file_path) ? $file_path . '/' : '';
        
        if (! method_exists($this, $file_ext))
            return '';
        
        $this->response->removeHeader('Content-Type');
        $this->{$file_ext}();
        
        return $this->baseView->render($file_path . $file_name . '.' . $file_ext);
    }
    
    protected function css() : void
    {
        $this->response->setHeader('Content-Type', 'text/css; charset=UTF-8');
    }
    
    protected function csv() : void
    {
        $this->response->setHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
    
    protected function html() : void
    {
        $this->response->setHeader('Content-Type', 'text/html; charset=UTF-8');
    }
    
    protected function js() : void
    {
        $this->response->setHeader('Content-Type', 'text/javascript; charset=UTF-8');
    }
    
    protected function txt() : void
    {
        $this->response->setHeader('Content-Type', 'text/plain; charset=UTF-8');
    }
    
    protected function xml() : void
    {
        $this->response->setHeader('Content-Type', 'text/xml; charset=UTF-8');
    }
    
    protected function jpg() : void
    {
        $this->response->setHeader('Content-Type', 'image/jpeg; charset=UTF-8');
    }
    
    protected function jpeg() : void
    {
        $this->response->setHeader('Content-Type', 'image/jpeg; charset=UTF-8');
    }
    
    protected function png() : void
    {
        $this->response->setHeader('Content-Type', 'image/png; charset=UTF-8');
    }
    
    protected function gif() : void
    {
        $this->response->setHeader('Content-Type', 'image/gif; charset=UTF-8');
    }
    
    protected function webp() : void
    {
        $this->response->setHeader('Content-Type', 'image/webp; charset=UTF-8');
    }
    
    protected function json() : void
    {
        $this->response->setHeader('Content-Type', 'application/json; charset=UTF-8');
    }
    
    protected function pdf() : void
    {
        $this->response->setHeader('Content-Type', 'application/pdf; charset=UTF-8');
    }
}