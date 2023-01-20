<?php

namespace Oriole\Router;

use Closure;
use Exception;
use LogicException;

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
     *         domain => [
     *             route => [                // original route
     *                 'from' => from,       // route with replaced placeholder
     *                 'handler' => handler,
     *                 'args' => ['$0', '$1', 'some string', '$2'],
     *                 'alias' => alias,
     *             ],
     *         ],
     *     ],
     * ]
     */
    protected array $routes = [
        'get'     => [],
        'post'    => [],
        'head'    => [],
        'put'     => [],
        'delete'  => [],
        'options' => [],
        'trace'   => [],
        'connect' => [],
        'cli'     => [],
    ];
    
    /**
     * An array of all reverse routes.
     *
     * @var array
     *
     * [
     *     froms => [
     *         verb::domain::from => from   // "from" in key = original route, "from" in value = route with replaced placeholder
     *     ],
     *     aliases => [
     *         verb::domain::alias => from  // "from" in key = alias, "from" in value = route with replaced placeholder
     *     ],
     * ]
     */
    protected array $reverseRoutes = [];
    
    /**
     * The name of the current group, if any.
     *
     * @var string
     */
    protected string $group = '';
    
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
    protected ? array $groupOptions = null;
    
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
    
    /**
     * Group a series of routes under a single URL segment.
     *
     * @param string            $name      The name to group/prefix the routes with.
     * @param array|callable ...$params
     */
    public function group(string $name, ...$params) : void
    {
        $oldGroup = $this->group;
        $oldGroupOptions = $this->groupOptions;
        
        $name = trim(strtolower($name), '/ ');
        $this->group = ! empty($name) ? $oldGroup . '/' . $name : $oldGroup;
        
        $callback = array_pop($params);
        
        if (isset($params[0]) && is_array($params[0])) {
            $groupOptions = [
                'namespace' => $params[0]['namespace'] ?? $oldGroupOptions['namespace'] ?? '',
                'domains' => $params[0]['domains'] ?? $oldGroupOptions['domains'] ?? [],
            ];
            $this->groupOptions = $groupOptions;
        }
        
        if (is_callable($callback))
            $callback($this);
        
        $this->group = $oldGroup;
        $this->groupOptions = $oldGroupOptions;
    }
    
    /**
     * Add a single route to the collection for all HTTP Verbs.
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
        $verbs = array_keys($this->routes);
        
        foreach ($verbs as $verb)
            $this->create($verb, $from, $to, $options);
        
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
    public function match(array $verbs, string $from, string|Closure $to, ? array $options = null) : Routes
    {
        foreach ($verbs as $verb)
            $this->create($verb, $from, $to, $options);
        
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
    public function post(string $from, string|Closure $to, ? array $options = null) : Routes
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
        if (! isset($this->routes[$verb]))
            return;
        
        $verb = strtolower($verb);
        
        $prefix = $this->group;
        
        $from = $from === '/' ? $from : trim($from, '/ ');
        $from = esc(strip_tags($prefix . '/' . $from));
        $from = $from === '/' ? $from : trim($from, '/ ');
        $from = strtolower($from);
        
        $from_ = $from;
        foreach ($this->placeholders as $tag => $pattern)
            $from_ = str_ireplace(':' . $tag, $pattern, $from_);
        
        $alias = $options['as'] ?? '';
        
        $options = array_merge($this->groupOptions ?? [], $options ?? []);
        
        $args = [];
        
        if (is_string($to)) {
            $to = trim($to, ' ');
            
            if (! empty($options['namespace']))
                $to = '\\' . trim($options['namespace'], '\\ ') . '\\' . trim($to, '\\');
            
            $toArray = explode('/', $to);
            $to = array_shift($toArray);
            
            $args = $toArray;
        }
        
        if (! empty($options['domains']))
            $options['domains'] = is_string($options['domains']) ? [$options['domains']] : (is_array($options['domains']) ? $options['domains'] : ['*']);
        else
            $options['domains'] = ['*'];
        
        foreach ($options['domains'] as $domain) {
            $this->routes[$verb][$domain][$from] = [
                'from' => $from_,
                'handler' => $to,
                'args' => $args,
                'alias' => $alias,
            ];
            
            $this->reverseRoutes['froms']["$verb::$domain::$from"] = $from_;
            
            if (! empty($alias)) {
                if (isset($this->reverseRoutes['aliases']["$verb::$domain::$alias"]))
                    throw new LogicException("Alias \"$alias\" is already in use.");
                
                $this->reverseRoutes['aliases']["$verb::$domain::$alias"] = $from_;
            }
        }
    }
    
    public function getRoutes() : array
    {
        return $this->routes;
    }
    
    public function getReverseRoutes() : array
    {
        return $this->reverseRoutes;
    }
}