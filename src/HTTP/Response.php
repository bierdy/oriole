<?php

namespace Oriole\HTTP;

use Oriole\Oriole;
use Exception;

/**
 * Representation of an HTTP response.
 */
class Response
{
    protected static self|null $instance = null;
    
    protected array $cookieConfig;
    
    protected array $headers = [];
    
    protected array $cookies = [];
    
    protected mixed $body = null;
    
    protected int $statusCode = 200;
    
    /**
     * Constructor
     */
    final private function __construct()
    {
        $this->cookieConfig = (new Oriole)->getConfig('cookie');
    }
    final protected function __clone()
    {
        
    }
    
    /**
     * @throws Exception
     */
    final public function __wakeup()
    {
        throw new Exception('Cannot unserialize a singleton.');
    }
    
    public static function getInstance() : self
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    
    public function setHeader(string $name, string|int|float $value, bool $replace = true) : void
    {
        $this->headers[] = [
            'name' => $name,
            'value' => $value,
            'replace' => $replace,
        ];
    }
    
    public function removeHeader(string $name, string|int|float $value = null) : void
    {
        foreach ($this->headers as $key => $header)
            if (
                $header['name'] === $name
                && (is_null($value) || $header['value'] === $value)
            )
                unset($this->headers[$key]);
    }
    
    protected function sendHeaders() : void
    {
        foreach ($this->headers as $header)
            header($header['name'] . ': ' . $header['value'], $header['replace']);
    }
    
    public function setCookie(string $name, string $value, int $expires = null, string $path = null, string $domain = null, bool $secure = null, bool $httpOnly = null, string $sameSite = null) : void
    {
        $name = $this->cookieConfig['prefix'] . $name;
        
        $expires =  ! is_null($expires) ?  $expires :  $this->cookieConfig['expires'];
        $path =     ! is_null($path) ?     $path :     $this->cookieConfig['path'];
        $domain =   ! is_null($domain) ?   $domain :   $this->cookieConfig['domain'];
        $secure =   ! is_null($secure) ?   $secure :   $this->cookieConfig['secure'];
        $httpOnly = ! is_null($httpOnly) ? $httpOnly : $this->cookieConfig['httpOnly'];
        $sameSite = ! is_null($sameSite) ? $sameSite : $this->cookieConfig['sameSite'];
        
        $this->cookies[] = [
            'name' => $name,
            'value' => $value,
            'options' => [
                'expires' => $expires,
                'path' => $path,
                'domain' => $domain,
                'secure' => $secure,
                'httponly' => $httpOnly,
                'samesite' => $sameSite,
            ],
        ];
    }
    
    public function removeCookie(string $name, string $path = null, string $domain = null) : void
    {
        $name = $this->cookieConfig['prefix'] . $name;
        
        $path =   ! is_null($path) ?   $path :   $this->cookieConfig['path'];
        $domain = ! is_null($domain) ? $domain : $this->cookieConfig['domain'];
        
        foreach ($this->cookies as $key => $cookie) {
            if (
                $cookie['name'] === $name
                && $cookie['options']['path'] === $path
                && $cookie['options']['domain'] === $domain
            )
                unset($this->cookies[$key]);
        }
        
        $this->setCookie($name, '', time() - 3600, $path, $domain);
    }
    
    protected function sendCookies() : void
    {
        foreach ($this->cookies as $cookie)
            setcookie($cookie['name'], $cookie['value'], $cookie['options']);
    }
    
    public function setStatusCode(int $statusCode) : void
    {
        $this->statusCode = $statusCode;
    }
    
    public function setBody($body) : void
    {
        $this->body = $body;
    }
    
    protected function sendBody() : void
    {
        echo $this->body;
    }
    
    public function send() : void
    {
        http_response_code($this->statusCode);
        $this->sendHeaders();
        $this->sendCookies();
        $this->sendBody();
    }
}