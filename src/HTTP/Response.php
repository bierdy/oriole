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
    
    /**
     * HTTP status codes
     *
     * @var array
     */
    protected array $statusCodes = [
        // 1xx: Informational
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        // 2xx: Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        // 3xx: Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy', // No longer used
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // 4xx: Client error
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Content Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => "I'm a teapot", // April's Fools joke;
        // 419 (Authentication Timeout) is a non-standard status code with unknown origin
        421 => 'Misdirected Request',
        422 => 'Unprocessable Content',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        499 => 'Client Closed Request',
        // 5xx: Server error
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        599 => 'Network Connect Timeout Error',
    ];
    
    /**
     * The current status code for this response.
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @var int
     */
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
    
    protected function setHeader(string $name, string|int|float $value, bool $replace = true) : void
    {
        $this->headers[] = [
            'name' => $name,
            'value' => $value,
            'replace' => $replace,
        ];
    }
    
    protected function removeHeader(string $name, string|int|float $value = null) : void
    {
        foreach ($this->headers as $key => $header)
            if (
                $header['name'] === $name
                && (is_null($value) || $header['value'] === $value)
            )
                unset($this->headers[$key]);
    }
    
    protected function setCookie(string $name, string $value, int $expires = null, string $path = null, string $domain = null, bool $secure = null, bool $httpOnly = null, string $sameSite = null) : void
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
    
    protected function removeCookie(string $name, string $path = null, string $domain = null) : void
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
    
    protected function setStatusCode(int $statusCode) : void
    {
        $this->statusCode = $statusCode;
    }
}