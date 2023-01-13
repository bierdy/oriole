<?php

namespace Oriole\HTTP;

use Exception;

/**
 * Representation of an HTTP response.
 */
class Response
{
    protected static self|null $instance = null;
    
    protected array $headers = [];
    
    /**
     * HTTP status codes
     *
     * @var array
     */
    protected array $statusCodes = [
        // 1xx: Informational
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing', // http://www.iana.org/go/rfc2518
        103 => 'Early Hints', // http://www.ietf.org/rfc/rfc8297.txt
        // 2xx: Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information', // 1.1
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status', // http://www.iana.org/go/rfc4918
        208 => 'Already Reported', // http://www.iana.org/go/rfc5842
        226 => 'IM Used', // 1.1; http://www.ietf.org/rfc/rfc3229.txt
        // 3xx: Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found', // Formerly 'Moved Temporarily'
        303 => 'See Other', // 1.1
        304 => 'Not Modified',
        305 => 'Use Proxy', // 1.1
        306 => 'Switch Proxy', // No longer used
        307 => 'Temporary Redirect', // 1.1
        308 => 'Permanent Redirect', // 1.1; Experimental; http://www.ietf.org/rfc/rfc7238.txt
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
        413 => 'Content Too Large', // https://www.iana.org/assignments/http-status-codes/http-status-codes.xml
        414 => 'URI Too Long', // https://www.iana.org/assignments/http-status-codes/http-status-codes.xml
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => "I'm a teapot", // April's Fools joke; http://www.ietf.org/rfc/rfc2324.txt
        // 419 (Authentication Timeout) is a non-standard status code with unknown origin
        421 => 'Misdirected Request', // http://www.iana.org/go/rfc7540 Section 9.1.2
        422 => 'Unprocessable Content', // https://www.iana.org/assignments/http-status-codes/http-status-codes.xml
        423 => 'Locked', // http://www.iana.org/go/rfc4918
        424 => 'Failed Dependency', // http://www.iana.org/go/rfc4918
        425 => 'Too Early', // https://datatracker.ietf.org/doc/draft-ietf-httpbis-replay/
        426 => 'Upgrade Required',
        428 => 'Precondition Required', // 1.1; http://www.ietf.org/rfc/rfc6585.txt
        429 => 'Too Many Requests', // 1.1; http://www.ietf.org/rfc/rfc6585.txt
        431 => 'Request Header Fields Too Large', // 1.1; http://www.ietf.org/rfc/rfc6585.txt
        451 => 'Unavailable For Legal Reasons', // http://tools.ietf.org/html/rfc7725
        499 => 'Client Closed Request', // http://lxr.nginx.org/source/src/http/ngx_http_request.h#0133
        // 5xx: Server error
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates', // 1.1; http://www.ietf.org/rfc/rfc2295.txt
        507 => 'Insufficient Storage', // http://www.iana.org/go/rfc4918
        508 => 'Loop Detected', // http://www.iana.org/go/rfc5842
        510 => 'Not Extended', // http://www.ietf.org/rfc/rfc2774.txt
        511 => 'Network Authentication Required', // http://www.ietf.org/rfc/rfc6585.txt
        599 => 'Network Connect Timeout Error', // https://httpstatuses.com/599
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
    
    protected function setHeader($name, $value, $replace) : void
    {
        $this->headers[] = [
            'name' => $name,
            'value' => $value,
            'replace' => $replace,
        ];
    }
    
    protected function setStatusCode(int $statusCode) : void
    {
        $this->statusCode = $statusCode;
    }
}