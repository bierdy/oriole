<?php

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