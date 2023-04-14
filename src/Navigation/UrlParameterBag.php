<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Navigation;

class UrlParameterBag
{
    private array $attributes;
    private array $query;

    /**
     * @param array $attributes Route parameters (e.g. items=foobar in /alias/items/foobar.html)
     * @param array $query      The URL query parameters
     */
    public function __construct(array $attributes = [], array $query = [])
    {
        $this->validateScalar($attributes);
        $this->validateScalar($query);

        $this->attributes = $attributes;
        $this->query = $query;
    }

    public function getUrlAttributes(): array
    {
        return $this->attributes;
    }

    public function setUrlAttributes(array $attributes): void
    {
        $this->validateScalar($attributes);

        $this->attributes = $attributes;
    }

    public function hasUrlAttribute(string $name): bool
    {
        return \array_key_exists($name, $this->attributes);
    }

    public function getUrlAttribute(string $name)
    {
        return $this->hasUrlAttribute($name) ? $this->attributes[$name] : null;
    }

    public function setUrlAttribute(string $name, $value): void
    {
        $this->validateScalar($value);

        $this->attributes[$name] = $value;
    }

    public function removeUrlAttribute(string $name): void
    {
        unset($this->attributes[$name]);
    }

    public function getQueryParameters(): array
    {
        return $this->query;
    }

    public function setQueryParameters(array $query): void
    {
        $this->validateScalar($query);

        $this->query = $query;
    }

    public function hasQueryParameter(string $name): bool
    {
        return \array_key_exists($name, $this->query);
    }

    public function getQueryParameter(string $name)
    {
        return $this->hasQueryParameter($name) ? $this->query[$name] : null;
    }

    public function setQueryParameter(string $name, $value): void
    {
        $this->validateScalar($value);

        $this->query[$name] = $value;
    }

    public function removeQueryParameter(string $name): void
    {
        unset($this->query[$name]);
    }

    /**
     * Generates parameter string to generate a Contao url.
     *
     * @throws \RuntimeException
     */
    public function generateParameters(): ?string
    {
        $params = '';
        $auto_item = null;
        $attributes = $this->attributes;

        if (0 === \count($attributes)) {
            return null;
        }

        if (isset($attributes['auto_item'])) {
            throw new \RuntimeException('Do not set auto_item parameter');
        }

        if ($GLOBALS['TL_CONFIG']['useAutoItem'] ?? true) {
            $auto_item = array_intersect_key($attributes, array_flip((array) ($GLOBALS['TL_AUTO_ITEM'] ?? [])));

            switch (\count($auto_item)) {
                case 0:
                    $auto_item = null;
                    break;

                case 1:
                    unset($attributes[key($auto_item)]);
                    $auto_item = current($auto_item);
                    break;

                default:
                    throw new \RuntimeException('You must not have more than one auto_item parameter');
            }
        }

        if (0 !== \count($attributes)) {
            array_walk(
                $attributes,
                static function (&$v, $k): void {
                    $v = $k.'/'.$v;
                }
            );

            $params = '/'.implode('/', $attributes);
        }

        if (null !== $auto_item) {
            $params = '/'.$auto_item.$params;
        }

        return $params;
    }

    /**
     * Generates a query string or returns null if empty.
     */
    public function generateQueryString(): ?string
    {
        if (0 === \count($this->query)) {
            return null;
        }

        return http_build_query($this->query);
    }

    /**
     * Makes sure the given value is scalar or an array of scalar values.
     *
     * @param mixed $value
     */
    private function validateScalar($value): void
    {
        if (\is_array($value)) {
            foreach ($value as $v) {
                $this->validateScalar($v);
            }

            return;
        }

        if (!\is_scalar($value)) {
            throw new \InvalidArgumentException('URL can only contain (array of) scalar values');
        }
    }
}
