<?php

namespace Oriole\Config;

class Cookie extends BaseConfig
{
    protected string $prefix = 'oriole_';
    protected int $expires = 365;
    protected string $path = '/';
    protected string $domain = '';
    protected bool $secure = false;
    protected bool $httpOnly = false;
    protected string $sameSite = 'Lax';
}