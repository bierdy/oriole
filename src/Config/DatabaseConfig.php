<?php

namespace Oriole\Config;

class DatabaseConfig extends BaseConfig
{
    protected string $hostname = '';
    protected string $username = '';
    protected string $password = '';
    protected string $database = '';
    protected string $charset = 'utf8mb4';
    protected string $collation = 'utf8mb4_unicode_ci';
    protected int $port = 3306;
}