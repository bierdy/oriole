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
        $prefixedName = $this->cookieConfig['prefix'] . $name;
        
        $expires =  ! is_null($expires) ?  $expires :  $this->cookieConfig['expires'];
        $path =     ! is_null($path) ?     $path :     $this->cookieConfig['path'];
        $domain =   ! is_null($domain) ?   $domain :   $this->cookieConfig['domain'];
        $secure =   ! is_null($secure) ?   $secure :   $this->cookieConfig['secure'];
        $httpOnly = ! is_null($httpOnly) ? $httpOnly : $this->cookieConfig['httpOnly'];
        $sameSite = ! is_null($sameSite) ? $sameSite : $this->cookieConfig['sameSite'];
        
        $this->cookies[] = [
            'name' => $prefixedName,
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
        $prefixedName = $this->cookieConfig['prefix'] . $name;
        
        $path =   ! is_null($path) ?   $path :   $this->cookieConfig['path'];
        $domain = ! is_null($domain) ? $domain : $this->cookieConfig['domain'];
        
        foreach ($this->cookies as $key => $cookie) {
            if (
                $cookie['name'] === $prefixedName
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
    
    /**
     * Perform a redirect to a new URL, in two flavors: header or location.
     *
     * @param string $uri The URI to redirect to
     * @param string $method
     * @param int $code The type of redirection, defaults to 302
     *
     * @return Response
     */
    public function redirect(string $uri, string $method = 'auto', int $code = 302) : static
    {
        $request = new Request();
        $serverSoftware = strtolower($request->getServer('SERVER_SOFTWARE') ? : '');
        $serverProtocol = strtolower($request->getServer('SERVER_PROTOCOL') ? : '');
        $requestMethod = $request->getRequestMethod();
        $method = strtolower($method);
        
        // IIS environment likely? Use 'refresh' for better compatibility
        if ($method === 'auto' && ! empty($serverSoftware) && str_contains($serverSoftware, 'microsoft-iis'))
            $method = 'refresh';
        
        // override status code for HTTP/1.1 & higher
        // reference: http://en.wikipedia.org/wiki/Post/Redirect/Get
        if (! empty($serverProtocol) && ! empty($requestMethod) && (float) $serverProtocol >= 1.1 && $method !== 'refresh')
            $code = $requestMethod !== 'get' ? 303 : ($code === 302 ? 307 : $code);
        
        switch ($method) {
            case 'refresh' :
                $this->setHeader('Refresh', '0;url=' . $uri);
                break;
            
            default :
                $this->setHeader('Location', $uri);
                break;
        }
        
        $this->setStatusCode($code);
    
        return $this;
    }
}