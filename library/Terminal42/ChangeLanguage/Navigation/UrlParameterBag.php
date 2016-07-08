<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\Navigation;

use Contao\Input;

class UrlParameterBag
{
    /**
     * @var array
     */
    private $attributes;

    /**
     * @var array
     */
    private $query;

    /**
     * Constructor.
     *
     * @param array $attributes Route parameters (e.g. items=foobar in /alias/items/foobar.html)
     * @param array $query      The URL query parameters
     */
    public function __construct(array $attributes = [], array $query = [])
    {
        $this->attributes = $attributes;
        $this->query      = $query;
    }

    /**
     * @return array
     */
    public function getUrlAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     */
    public function setUrlAttributes(array $attributes)
    {
        $this->validateScalar($attributes);

        $this->attributes = $attributes;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasUrlAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getUrlAttribute($name)
    {
        return $this->hasUrlAttribute($name) ? $this->attributes[$name] : null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setUrlAttribute($name, $value)
    {
        $this->validateScalar($value);

        $this->attributes[$name] = $value;
    }

    /**
     * @return array
     */
    public function getQueryParameters()
    {
        return $this->query;
    }

    /**
     * @param array $query
     */
    public function setQueryParameters(array $query)
    {
        $this->validateScalar($query);

        $this->query = $query;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasQueryParameter($name)
    {
        return array_key_exists($name, $this->query);
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function getQueryParameter($name)
    {
        return $this->hasQueryParameter($name) ? $this->query[$name] : null;
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setQueryParameter($name, $value)
    {
        $this->validateScalar($value);

        $this->query[$name] = $value;
    }

    /**
     * Generates parameter string to generate a Contao url.
     *
     * @return null|string
     */
    public function generateParameters()
    {
        $params     = '';
        $attributes = $this->attributes;

        if (0 === count($attributes)) {
            return null;
        }

        $auto_item = array_key_exists('auto_item', $attributes) ? $attributes['auto_item'] : null;
        unset($attributes['auto_item']);

        if (0 !== count($attributes)) {
            array_walk(
                $attributes,
                function (&$v, $k) {
                    $v = $k . '/' . $v;
                }
            );

            $params = '/' . implode('/', $attributes);
        }

        if (null !== $auto_item) {
            $params = '/' . $auto_item . $params;
        }

        return $params;
    }

    /**
     * Generates a query string or returns null if empty.
     *
     * @return null|string
     */
    public function generateQueryString()
    {
        if (0 === count($this->query)) {
            return null;
        }

        return http_build_query($this->query);
    }

    /**
     * @param bool $skipQueryParameters
     *
     * @return static
     */
    public static function createFromGlobals($skipQueryParameters = true)
    {
        $attributes = [];
        $query      = [];

        parse_str($_SERVER['QUERY_STRING'], $currentQuery);

        foreach ($_GET as $k => $value) {
            $value = Input::get($k, false, true);
            $isQuery = array_key_exists($k, $currentQuery);

            // the current page language is set in $_GET
            if (empty($value) || 'language' === $k || ($skipQueryParameters && $isQuery)) {
                continue;
            }

            if ($isQuery) {
                $query[$k] = $value;
            } else {
                $attributes[$k] = $value;
            }
        }

        return new static($attributes, $query);
    }

    /**
     * Makes sure the given value is scalar or an array of scalar values.
     *
     * @param mixed $value
     */
    private function validateScalar($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $this->validateScalar($v);
            }

            return;
        }

        if (!is_scalar($value)) {
            throw new \InvalidArgumentException('URL can only contain (array of) scalar values');
        }
    }
}
