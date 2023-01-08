<?php

namespace Oriole\Config;

class App extends BaseConfig
{
    protected string $backRootPath = 'admin';
    
    protected string $frontRootPath = '/';
    
    protected string $backDomain = '';
    
    protected string $frontDomain = '';
    
    protected string $resourceUrlSeparator = '-';
    
    protected string $resourceUrlCopyPostfix = 'copy';
    
    protected string $resourceUrlEmpty = 'empty';
    
    protected int|null $resource404Id = null;
}