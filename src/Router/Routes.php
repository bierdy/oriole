<?php

namespace Oriole\Router;

use Closure;
use InvalidArgumentException;

class Routes
{
    protected static self|null $instance = null;
    
    /**
     * Defined placeholders that can be used.
     *
     * @var array<string,string>
     */
    protected array $placeholders = [
        'any'      => '.*',           // Will match all characters from that point to the end of the URI. This may include multiple URI segments.
        'segment'  => '[^/]+',        // Will match any character except for a forward slash (/) restricting the result to a single segment.
        'alphanum' => '[a-zA-Z0-9]+', // Will match any string of alphabetic characters or integers, or any combination of the two.
        'num'      => '[0-9]+',       // Will match any integer.
        'alpha'    => '[a-zA-Z]+',    // Will match any string of alphabetic characters.
        'hash'     => '[^/]+',        // Is the same as (:segment), but can be used to easily see which routes use hashed ids.
    ];
    
    /**
     * An array of all routes and their mappings.
     *
     * @var array
     *
     * [
     *     verb => [
     *         routeName => [
     *             'route' => [
     *                  routeKey(or from) => handler
     *              ],
     *             'options' => [
     *                  'domains' => [
     *                      'example.com',
     *                      'example.org',
     *                      'example.us',
     *                  ],
     *             ],
     *         ]
     *     ],
     * ]
     */
    protected array $routes = [
        '*'       => [],
        'options' => [],
        'get'     => [],
        'head'    => [],
        'post'    => [],
        'put'     => [],
        'delete'  => [],
        'trace'   => [],
        'connect' => [],
        'cli'     => [],
    ];
    
    /**
     * The name of the current group, if any.
     *
     * @var string|null
     */
    protected ? string $group;
    
    /**
     * Stores copy of current options being
     * applied during creation.
     *
     * @var array|null
     *
     * [
     *     'namespace' => '\Example\Namespace',
     *     'domains' => [
     *         'example.com',
     *         'example.org',
     *         'example.us',
     *     ],
     * ]
     */
    protected ? array $groupOptions;
    
    /**
     * Constructor
     */
    final private function __construct()
    {
    
    }
    final protected function __clone()
    {
    
    }
    final public function __wakeup()
    {
    
    }
    
    public static function getInstance() : static
    {
        if (static::$instance === null) {
            static::$instance = new static;
        }
        
        return static::$instance;
    }
    
    /**
     * Group a series of routes under a single URL segment.
     *
     * @param string            $name      The name to group/prefix the routes with.
     * @param array|callable ...$params
     */
    public function group(string $name, ...$params) : void
    {
        $oldGroup        = $this->group;
        $oldGroupOptions = $this->groupOptions;
        
        $this->group = $name ? trim($oldGroup . '/' . $name, '/') : $oldGroup;
        
        $callback = array_pop($params);
        
        if (isset($params[0]) && is_array($params[0])) {
            $groupOptions = [
                'namespace' => $params[0]['namespace'] ?? '',
                'domains' => $params[0]['domains'] ?? [],
            ];
            $this->groupOptions = $groupOptions;
        }
        
        if (is_callable($callback))
            $callback($this);
        
        $this->group        = $oldGroup;
        $this->groupOptions = $oldGroupOptions;
    }
    
    /**
     * Adds a single route to the collection.
     *
     * Example:
     *      $routes->add('news', 'Posts::index');
     *
     * @param string $from
     * @param string|Closure $to
     * @param array|null $options
     * @return Routes
     */
    public function add(string $from, string|Closure $to, ? array $options = null) : Routes
    {
        $this->create('*', $from, $to, $options);
        
        return $this;
    }
    
    /**
     * Specifies a single route to match for multiple HTTP Verbs.
     *
     * Example:
     *  $route->match( ['get', 'post'], 'users/(:num)', 'users/$1);
     *
     * @param array $verbs
     * @param string $from
     * @param string|Closure $to
     * @param array|null $options
     * @return Routes
     */
    public function match(array $verbs = [], string $from = '', string|Closure $to = '', ? array $options = null) : Routes
    {
        if (empty($from) || empty($to))
            throw new InvalidArgumentException('You must supply the parameters: from, to.');
        
        foreach ($verbs as $verb) {
            $verb = strtolower($verb);
            $this->create($verb, $from, $to, $options);
        }
        
        return $this;
    }
    
    /**
     * Specifies a route that is only available to GET requests.
     *
     * @param string $from
     * @param string|Closure $to
     * @param array|null $options
     * @return Routes
     */
    public function get(string $from, string|Closure $to, ? array $options = null) : Routes
    {
        $this->create('get', $from, $to, $options);
        
        return $this;
    }
    
    /**
     * Specifies a route that is only available to POST requests.
     *
     * @param string $from
     * @param string|Closure $to
     * @param array|null $options
     * @return Routes
     */
    public function post(string $from, string|Closure $to, ?array $options = null): Routes
    {
        $this->create('post', $from, $to, $options);
        
        return $this;
    }
    
    /**
     * Does the heavy lifting of creating an actual route. You must specify
     * the request method(s) that this route will work for.
     *
     * @param string $verb
     * @param string $from
     * @param string|Closure $to
     * @param array|null $options
     */
    protected function create(string $verb, string $from, string|Closure $to, ? array $options = null) : void
    {
        $name = $options['as'] ?? $from;
        
        $prefix = $this->group === null ? '' : $this->group . '/';
        $from = esc(strip_tags($prefix . $from));
        
        if ($from !== '/')
            $from = trim($from, '/');
        
        $options = array_merge($this->groupOptions ?? [], $options ?? []);
        
        foreach ($this->placeholders as $tag => $pattern)
            $from = str_ireplace(':' . $tag, $pattern, $from);
        
        if (! empty($options['namespace']) && is_string($to))
            $to = '\\' . trim($options['namespace'], '\\') . '\\' . ltrim($to, '\\');
        
        if (! empty($options['domains'])) {
            if (is_string($options['domains']))
                $domains = [$options['domains']];
            elseif (is_array($options['domains']))
                $domains = $options['domains'];
            else
                $domains = [];
        } else {
            $domains = [];
        }
        
        $this->routes[$verb][$name] = [
            'route' => [$from => $to],
            'options' => [
                'domains' => $domains,
            ],
        ];
    }
}