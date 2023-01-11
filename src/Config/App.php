<?php

namespace Oriole\Config;

class App extends BaseConfig
{
    protected string $adminRootPath = 'admin';
    
    protected string $publicRootPath = '/';
    
    protected string $adminDomain = '';
    
    protected string $publicDomain = '';
    
    protected string $resourceUrlSeparator = '-';
    
    protected string $resourceUrlCopyPostfix = 'copy';
    
    protected string $resourceUrlEmpty = 'empty';
    
    protected int|null $resource404Id = null;
}