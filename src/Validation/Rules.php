<?php

namespace Oriole\Validation;

use Oriole\Models\BaseModel;
use DateTime;
use InvalidArgumentException;

/**
 * Validation Rules.
 */
class Rules
{
    /**
     * The value does not match another field in $data.
     *
     * @param array $data Other field/value pairs
     */
    public function differs(? string $str, string $field, array $data) : bool
    {
        if (str_contains($field, '.')) {
            return $str !== dot_array_search($field, $data);
        }
        
        return array_key_exists($field, $data) && $str !== $data[$field];
    }
    
    /**
     * Equals the static value provided.
     */
    public function equals(? string $str, string $val) : bool
    {
        return $str === $val;
    }
    
    /**
     * Returns true if $str is $val characters long.
     * $val = "5" (one) | "5,8,12" (multiple values)
     */
    public function exact_length(? string $str, string $val) : bool
    {
        $val = explode(',', $val);
        
        foreach ($val as $tmp) {
            if (is_numeric($tmp) && (int) $tmp === mb_strlen($str ?? '')) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Greater than
     */
    public function greater_than(? string $str, string $min) : bool
    {
        return is_numeric($str) && $str > $min;
    }
    
    /**
     * Equal to or Greater than
     */
    public function greater_than_equal_to(? string $str, string $min) : bool
    {
        return is_numeric($str) && $str >= $min;
    }
    
    /**
     * Checks the database to see if the given value exist.
     * Can ignore records by field/value to filter (currently
     * accept only one filter).
     *
     * Example:
     *    is_not_unique[table.field,where_field,where_value]
     *    is_not_unique[menu.id,active,1]
     */
    public function is_not_unique(? string $str, string $field, array $data) : bool
    {
        [$field, $whereField, $whereValue] = array_pad(explode(',', $field), 3, null);
        
        sscanf($field, '%[^.].%[^.]', $table, $field);
    
        $row = (new BaseModel())->select('1')->from($table)->where($field, '=', $str);
        
        if (! empty($whereField) && ! empty($whereValue) && ! preg_match('/^\{(\w+)$/', $whereValue))
            $row = $row->andWhere($whereField, '=', $whereValue);
        
        return $row->limit(1)->findOne() !== false;
    }
    
    /**
     * Value should be within an array of values
     */
    public function in_list(? string $value, string $list) : bool
    {
        $list = array_map('trim', explode(',', $list));
        
        return in_array($value, $list, true);
    }
    
    /**
     * Checks the database to see if the given value is unique. Can
     * ignore a single record by field/value to make it useful during
     * record updates.
     *
     * Example:
     *    is_unique[table.field,ignore_field,ignore_value]
     *    is_unique[users.email,id,5]
     */
    public function is_unique(? string $str, string $field, array $data) : bool
    {
        [$field, $ignoreField, $ignoreValue] = array_pad(explode(',', $field), 3, null);
        
        sscanf($field, '%[^.].%[^.]', $table, $field);
        
        $row = (new BaseModel())->select('1')->from($table)->where($field, '=', $str);
        
        if (! empty($ignoreField) && ! empty($ignoreValue) && ! preg_match('/^\{(\w+)$/', $ignoreValue))
            $row = $row->andWhere($ignoreField, '!=', $ignoreValue);
        
        return $row->limit(1)->findOne() === false;
    }
    
    /**
     * Less than
     */
    public function less_than(? string $str, string $max) : bool
    {
        return is_numeric($str) && $str < $max;
    }
    
    /**
     * Equal to or Less than
     */
    public function less_than_equal_to(? string $str, string $max) : bool
    {
        return is_numeric($str) && $str <= $max;
    }
    
    /**
     * Matches the value of another field in $data.
     *
     * @param array $data Other field/value pairs
     */
    public function matches(? string $str, string $field, array $data) : bool
    {
        if (str_contains($field, '.')) {
            return $str === dot_array_search($field, $data);
        }
        
        return array_key_exists($field, $data) && $str === $data[$field];
    }
    
    /**
     * Returns true if $str is $val or fewer characters in length.
     */
    public function max_length(? string $str, string $val) : bool
    {
        return is_numeric($val) && $val >= mb_strlen($str ?? '');
    }
    
    /**
     * Returns true if $str is at least $val length.
     */
    public function min_length(? string $str, string $val) : bool
    {
        return is_numeric($val) && $val <= mb_strlen($str ?? '');
    }
    
    /**
     * Does not equal the static value provided.
     *
     * @param string|null $str
     * @param string $val
     * @return bool
     */
    public function not_equals(? string $str, string $val) : bool
    {
        return $str !== $val;
    }
    
    /**
     * Value should not be within an array of values.
     *
     * @param string|null $value
     * @param string $list
     * @return bool
     */
    public function not_in_list(? string $value, string $list) : bool
    {
        return ! $this->in_list($value, $list);
    }
    
    /**
     * @param float|object|array|bool|int|string|null $str
     */
    public function required(float|object|array|bool|int|string $str = null) : bool
    {
        if ($str === null) {
            return false;
        }
        
        if (is_object($str)) {
            return true;
        }
        
        if (is_array($str)) {
            return $str !== [];
        }
        
        return trim((string) $str) !== '';
    }
    
    /**
     * The field is required when any of the other required fields are present
     * in the data.
     *
     * Example (field is required when the password field is present):
     *
     *     required_with[password]
     *
     * @param string|null $str
     * @param string|null $fields List of fields that we should check if present
     * @param array       $data   Complete list of fields from the form
     */
    public function required_with(string $str = null, ? string $fields = null, array $data = []) : bool
    {
        if ($fields === null || empty($data)) {
            throw new InvalidArgumentException('You must supply the parameters: fields, data.');
        }
        
        // If the field is present we can safely assume that
        // the field is here, no matter whether the corresponding
        // search field is present or not.
        $fields  = explode(',', $fields);
        $present = $this->required($str ?? '');
        
        if ($present) {
            return true;
        }
        
        // Still here? Then we fail this test if
        // any of the fields are present in $data
        // as $fields is the lis
        $requiredFields = [];
        
        foreach ($fields as $field) {
            if ((array_key_exists($field, $data) && ! empty($data[$field])) || (str_contains($field, '.') && ! empty(dot_array_search($field, $data)))) {
                $requiredFields[] = $field;
            }
        }
        
        return empty($requiredFields);
    }
    
    /**
     * The field is required when all the other fields are present
     * in the data but not required.
     *
     * Example (field is required when the id or email field is missing):
     *
     *     required_without[id,email]
     *
     * @param string|null $str
     * @param string|null $otherFields The param fields of required_without[].
     * @param string|null $field       This rule param fields aren't present, this field is required.
     */
    public function required_without(string $str = null, ? string $otherFields = null, array $data = [], ? string $error = null, ? string $field = null) : bool
    {
        if ($otherFields === null || empty($data)) {
            throw new InvalidArgumentException('You must supply the parameters: otherFields, data.');
        }
        
        // If the field is present we can safely assume that
        // the field is here, no matter whether the corresponding
        // search field is present or not.
        $otherFields = explode(',', $otherFields);
        $present     = $this->required($str ?? '');
        
        if ($present) {
            return true;
        }
        
        // Still here? Then we fail this test if
        // any of the fields are not present in $data
        foreach ($otherFields as $otherField) {
            if ((!str_contains($otherField, '.')) && (! array_key_exists($otherField, $data) || empty($data[$otherField]))) {
                return false;
            }
            if (str_contains($otherField, '.')) {
                if ($field === null) {
                    throw new InvalidArgumentException('You must supply the parameters: field.');
                }
                
                $fieldData       = dot_array_search($otherField, $data);
                $fieldSplitArray = explode('.', $field);
                $fieldKey        = $fieldSplitArray[1];
                
                if (is_array($fieldData)) {
                    return ! empty(dot_array_search($otherField, $data)[$fieldKey]);
                }
                $nowField      = str_replace('*', $fieldKey, $otherField);
                $nowFieldValue = dot_array_search($nowField, $data);
                
                return null !== $nowFieldValue;
            }
        }
        
        return true;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
     * Alpha
     */
    public function alpha(? string $str = null) : bool
    {
        return ctype_alpha($str ?? '');
    }
    
    /**
     * Alpha with spaces.
     *
     * @param string|null $value Value.
     *
     * @return bool True if alpha with spaces, else false.
     */
    public function alpha_space(? string $value = null) : bool
    {
        if ($value === null) {
            return true;
        }
        
        // @see https://regex101.com/r/LhqHPO/1
        return (bool) preg_match('/\A[A-Z ]+\z/i', $value);
    }
    
    /**
     * Alphanumeric with underscores and dashes
     *
     * @see https://regex101.com/r/XfVY3d/1
     */
    public function alpha_dash(? string $str = null) : bool
    {
        if ($str === null) {
            return false;
        }
        
        return preg_match('/\A[a-z0-9_-]+\z/i', $str) === 1;
    }
    
    /**
     * Alphanumeric, spaces, and a limited set of punctuation characters.
     * Accepted punctuation characters are: ~ tilde, ! exclamation,
     * # number, $ dollar, % percent, & ampersand, * asterisk, - dash,
     * _ underscore, + plus, = equals, | vertical bar, : colon, . period
     * ~ ! # $ % & * - _ + = | : .
     *
     * @param string|null $str
     *
     * @return bool
     *
     * @see https://regex101.com/r/6N8dDY/1
     */
    public function alpha_numeric_punct(? string $str = null) : bool
    {
        if ($str === null) {
            return false;
        }
        
        return preg_match('/\A[A-Z0-9 ~!#$%\-_+=|:.]+\z/i', $str) === 1;
    }
    
    /**
     * Alphanumeric
     */
    public function alpha_numeric(? string $str = null) : bool
    {
        return ctype_alnum($str ?? '');
    }
    
    /**
     * Alphanumeric w/ spaces
     */
    public function alpha_numeric_space(? string $str = null) : bool
    {
        // @see https://regex101.com/r/0AZDME/1
        return (bool) preg_match('/\A[A-Z0-9 ]+\z/i', $str ?? '');
    }
    
    /**
     * Any type of string
     *
     * Note: we specifically do NOT type hint $str here so that
     * it doesn't convert numbers into strings.
     *
     * @param string|null $str
     * @return bool
     */
    public function string(? string $str = null) : bool
    {
        return is_string($str);
    }
    
    /**
     * Decimal number
     */
    public function decimal(? string $str = null) : bool
    {
        // @see https://regex101.com/r/HULifl/2/
        return (bool) preg_match('/\A[-+]?\d*\.?\d+\z/', $str ?? '');
    }
    
    /**
     * String of hexadecimal characters
     */
    public function hex(? string $str = null) : bool
    {
        return ctype_xdigit($str ?? '');
    }
    
    /**
     * Integer
     */
    public function integer(? string $str = null) : bool
    {
        return (bool) preg_match('/\A[\-+]?\d+\z/', $str ?? '');
    }
    
    /**
     * Is a Natural number  (0,1,2,3, etc.)
     */
    public function is_natural(? string $str = null) : bool
    {
        return ctype_digit($str ?? '');
    }
    
    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.)
     */
    public function is_natural_no_zero(? string $str = null) : bool
    {
        return $str !== '0' && ctype_digit($str ?? '');
    }
    
    /**
     * Numeric
     */
    public function numeric(? string $str = null) : bool
    {
        // @see https://regex101.com/r/bb9wtr/2
        return (bool) preg_match('/\A[\-+]?\d*\.?\d+\z/', $str ?? '');
    }
    
    /**
     * Compares value against a regular expression pattern.
     */
    public function regex_match(? string $str, string $pattern) : bool
    {
        if (!str_starts_with($pattern, '/')) {
            $pattern = "/{$pattern}/";
        }
        
        return (bool) preg_match($pattern, $str ?? '');
    }
    
    /**
     * Validates that the string is a valid timezone as per the
     * timezone_identifiers_list function.
     *
     * @see http://php.net/manual/en/datetimezone.listidentifiers.php
     *
     * @param string|null $str
     * @return bool
     */
    public function timezone(? string $str = null) : bool
    {
        return in_array($str ?? '', timezone_identifiers_list(), true);
    }
    
    /**
     * Valid Base64
     *
     * Tests a string for characters outside the Base64 alphabet
     * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
     *
     * @param string|null $str
     * @return bool
     */
    public function valid_base64(? string $str = null) : bool
    {
        if ($str === null) {
            return false;
        }
        
        return base64_encode(base64_decode($str, true)) === $str;
    }
    
    /**
     * Valid JSON
     *
     * @param string|null $str
     * @return bool
     */
    public function valid_json(? string $str = null) : bool
    {
        json_decode($str ?? '');
        
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Checks for a correctly formatted email address
     *
     * @param string|null $str
     * @return bool
     */
    public function valid_email(? string $str = null) : bool
    {
        // @see https://regex101.com/r/wlJG1t/1/
        if (function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46') && preg_match('#\A([^@]+)@(.+)\z#', $str ?? '', $matches)) {
            $str = $matches[1] . '@' . idn_to_ascii($matches[2], 0, INTL_IDNA_VARIANT_UTS46);
        }
        
        return (bool) filter_var($str, FILTER_VALIDATE_EMAIL);
    }
    
    /**
     * Validate a comma-separated list of email addresses.
     *
     * Example:
     *     valid_emails[one@example.com,two@example.com]
     *
     * @param string|null $str
     * @return bool
     */
    public function valid_emails(? string $str = null) : bool
    {
        foreach (explode(',', $str ?? '') as $email) {
            $email = trim($email);
            
            if ($email === '') {
                return false;
            }
            
            if ($this->valid_email($email) === false) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate an IP address (human-readable format or binary string - inet_pton)
     *
     * @param string|null $which IP protocol: 'ipv4' or 'ipv6'
     */
    public function valid_ip(? string $ip = null, ? string $which = null) : bool
    {
        if (empty($ip)) {
            return false;
        }
    
        $option = match (strtolower($which ?? '')) {
            'ipv4' => FILTER_FLAG_IPV4,
            'ipv6' => FILTER_FLAG_IPV6,
            default => 0,
        };
        
        return filter_var($ip, FILTER_VALIDATE_IP, $option) !== false || (! ctype_print($ip) && filter_var(inet_ntop($ip), FILTER_VALIDATE_IP, $option) !== false);
    }
    
    /**
     * Checks a string to ensure it is (loosely) a URL.
     *
     * Warning: this rule will pass basic strings like
     * "banana"; use valid_url_strict for a stricter rule.
     */
    public function valid_url(? string $str = null) : bool
    {
        if (empty($str)) {
            return false;
        }
        
        if (preg_match('/\A(?:([^:]*)\:)?\/\/(.+)\z/', $str, $matches)) {
            if (! in_array($matches[1], ['http', 'https'], true)) {
                return false;
            }
            
            $str = $matches[2];
        }
        
        $str = 'http://' . $str;
        
        return filter_var($str, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Checks a URL to ensure it's formed correctly.
     *
     * @param string|null $validSchemes comma separated list of allowed schemes
     */
    public function valid_url_strict(? string $str = null, ? string $validSchemes = null) : bool
    {
        if (empty($str)) {
            return false;
        }
        
        // parse_url() may return null and false
        $scheme       = strtolower((string) parse_url($str, PHP_URL_SCHEME));
        $validSchemes = explode(',', strtolower($validSchemes ?? 'http,https'));
        
        return in_array($scheme, $validSchemes, true)
            && filter_var($str, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Checks for a valid date and matches a given date format
     */
    public function valid_date(? string $str = null, ? string $format = null) : bool
    {
        if ($str === null) {
            return false;
        }
        
        if (empty($format)) {
            return strtotime($str) !== false;
        }
        
        $date   = DateTime::createFromFormat($format, $str);
        $errors = DateTime::getLastErrors();
        
        if ($date === false) {
            return false;
        }
        
        // PHP 8.2 or later.
        if ($errors === false) {
            return true;
        }
        
        return $errors['warning_count'] === 0 && $errors['error_count'] === 0;
    }
}