<?php

use Oriole\HTTP\Request;
use Oriole\Router\Router;
use Laminas\Escaper\Escaper;

// Common functions

if (! function_exists('esc')) {
    /**
     * Performs simple auto-escaping of data for security reasons.
     * Might consider making this more complex at a later date.
     *
     * If $data is a string, then it simply escapes and returns it.
     * If $data is an array, then it loops over it, escaping each
     * 'value' of the key/value pairs.
     *
     * @param array|string $data
     * @phpstan-param 'html'|'js'|'css'|'url'|'attr'|'raw' $context
     * @param string $context
     * @param string|null $encoding Current encoding for escaping.
     *                              If not UTF-8, we convert strings from this encoding
     *                              pre-escaping and back to this encoding post-escaping.
     *
     * @return array|string
     *
     * @throws InvalidArgumentException
     */
    function esc(array|string $data, string $context = 'html', ? string $encoding = null) : array|string
    {
        if (is_array($data)) {
            foreach ($data as &$value) {
                $value = esc($value, $context);
            }
        }
        
        if (is_string($data)) {
            $context = strtolower($context);
            
            // Provide a way to NOT escape data since
            // this could be called automatically by
            // the View library.
            if ($context === 'raw') {
                return $data;
            }
            
            if (! in_array($context, ['html', 'js', 'css', 'url', 'attr'], true)) {
                throw new InvalidArgumentException('Invalid escape context provided.');
            }
            
            $method = $context === 'attr' ? 'escapeHtmlAttr' : 'escape' . ucfirst($context);
            
            static $escaper;
            if (! $escaper) {
                $escaper = new Escaper($encoding);
            }
            
            if ($encoding && $escaper->getEncoding() !== $encoding) {
                $escaper = new Escaper($encoding);
            }
            
            $data = $escaper->{$method}($data);
        }
        
        return $data;
    }
}

if (! function_exists('route_by_route')) {
    /**
     * @param string $route
     * @param int|string ...$params One or more parameters to be passed to the route
     * @return string
     */
    function route_by_route(string $route, ...$params) : string
    {
        return Router::getInstance()->getReverseRoute('froms', $route, ...$params);
    }
}

if (! function_exists('route_by_alias')) {
    /**
     * @param string $route
     * @param int|string ...$params One or more parameters to be passed to the route
     * @return string
     */
    function route_by_alias(string $route, ...$params) : string
    {
        return Router::getInstance()->getReverseRoute('aliases', $route, ...$params);
    }
}

if (! function_exists('url_is')) {
    /**
     * Determines if current url path contains
     * the given path. It may contain a wildcard (*)
     * which will allow any valid character.
     *
     * Example:
     *   if (url_is('admin*')) ...
     */
    function url_is(string $path) : bool
    {
        $request = new Request();
        
        // Set up our regex to allow wildcards
        $path        = '/' . trim(str_replace('*', '(\S)*', $path), '/ ');
        $currentPath = '/' . trim($request->getCurrentURI(), '/ ');
        
        return (bool) preg_match("|^$path$|", $currentPath);
    }
}

if (! function_exists('stringify_attributes')) {
    /**
     * Stringify attributes for use in HTML tags.
     *
     * Helper function used to convert a string, array, or object
     * of attributes to a string.
     *
     * @param object|array|string $attributes string, array, object that can be cast to array
     */
    function stringify_attributes(object|array|string $attributes, bool $js = false) : string
    {
        $atts = '';
        
        if (empty($attributes))
            return $atts;
        
        if (is_string($attributes))
            return ' ' . $attributes;
        
        $attributes = (array) $attributes;
        
        foreach ($attributes as $key => $val)
            $atts .= ($js) ? $key . '=' . esc($val, 'js') . ',' : ' ' . $key . '="' . esc($val) . '"';
        
        return rtrim($atts, ',');
    }
}

if (! function_exists('anchor')) {
    /**
     * Anchor Link
     *
     * Creates an anchor based on the local URL.
     *
     * @param array|string        $uri        URI string or array of URI segments
     * @param string              $title      The link title
     * @param object|array|string $attributes Any attributes
     */
    function anchor(array|string $uri = '', string $title = '', object|array|string $attributes = '') : string
    {
        $uri = trim($uri);
        $uri = $uri === '/' ? $uri : rtrim($uri, '/');
    
        $title = $title === '' ? $uri : $title;
        
        if ($attributes !== '')
            $attributes = stringify_attributes($attributes);
        
        return '<a href="' . $uri . '"' . $attributes . '>' . $title . '</a>';
    }
}

if (! function_exists('form_open')) {
    /**
     * Form Declaration
     *
     * Creates the opening portion of the form.
     *
     * @param string       $action     the URI segments of the form destination
     * @param array|string $attributes a key/value a pair of attributes, or string representation
     * @param array        $hidden     a key/value pair hidden data
     */
    function form_open(string $action = '', array|string $attributes = [], array $hidden = []) : string
    {
        $request = new Request();
        
        if (! $action)
            $action = $request->getCurrentURL();
        
        $attributes = stringify_attributes($attributes);
        
        if (stripos($attributes, 'method=') === false)
            $attributes .= ' method="post"';
        
        if (stripos($attributes, 'accept-charset=') === false)
            $attributes .= ' accept-charset="UTF-8"';
        
        $form = '<form action="' . $action . '"' . $attributes . ">" . PHP_EOL;
        
        foreach ($hidden as $name => $value)
            $form .= form_hidden($name, $value);
        
        return $form;
    }
}

if (! function_exists('form_close')) {
    /**
     * Form Close Tag
     */
    function form_close(string $extra = ''): string
    {
        return '</form>' . $extra;
    }
}

if (! function_exists('form_hidden')) {
    /**
     * Hidden Input Field
     *
     * Generates hidden fields. You can pass a simple key/value string or
     * an associative array with multiple values.
     *
     * @param array|string $name  Field name or associative array to create multiple fields
     * @param array|string $value Field value
     */
    function form_hidden(array|string $name, array|string $value = '', bool $recursing = false) : string
    {
        static $form;
        
        if ($recursing === false)
            $form = PHP_EOL;
        
        if (is_array($name)) {
            foreach ($name as $key => $val)
                form_hidden($key, $val, true);
            
            return $form;
        }
        
        if (! is_array($value))
            $form .= form_input($name, $value, '', 'hidden');
        else {
            foreach ($value as $k => $v) {
                $k = is_int($k) ? '' : $k;
                form_hidden($name . '[' . $k . ']', $v, true);
            }
        }
        
        return $form;
    }
}

if (! function_exists('form_input')) {
    /**
     * Text Input Field. If 'type' is passed in the $type field, it will be
     * used as the input type, for making 'email', 'phone', etc. input fields.
     *
     * @param array|string $data
     * @param string $value
     * @param object|array|string $extra string, array, object that can be cast to array
     * @param string $type
     * @return string
     */
    function form_input(array|string $data = '', string $value = '', object|array|string $extra = '', string $type = 'text') : string
    {
        $defaults = [
            'type'  => $type,
            'name'  => is_array($data) ? '' : $data,
            'value' => $value,
        ];
        
        return '<input ' . parse_form_attributes($data, $defaults) . stringify_attributes($extra) . " />" . PHP_EOL;
    }
}

if (! function_exists('parse_form_attributes')) {
    /**
     * Parse the form attributes
     *
     * Helper function used by some form helpers
     *
     * @param array|string $attributes List of attributes
     * @param array        $default    Default values
     */
    function parse_form_attributes(array|string $attributes, array $default) : string
    {
        if (is_array($attributes)) {
            foreach (array_keys($default) as $key) {
                if (isset($attributes[$key])) {
                    $default[$key] = $attributes[$key];
                    unset($attributes[$key]);
                }
            }
            if (! empty($attributes))
                $default = array_merge($default, $attributes);
        }
        
        $att = '';
        
        foreach ($default as $key => $val) {
            if (! is_bool($val)) {
                if ($key === 'value')
                    $val = esc($val);
                elseif ($key === 'name' && ! strlen($default['name']))
                    continue;
                
                $att .= $key . '="' . $val . '"' . ($key === array_key_last($default) ? '' : ' ');
            } else
                $att .= $key . ' ';
        }
        
        return $att;
    }
}