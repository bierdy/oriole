<?php

namespace Oriole\Config;

class BaseConfig
{
    public function getProperties() : array
    {
        return get_object_vars($this);
    }
}