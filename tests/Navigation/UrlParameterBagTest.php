<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Tests\Navigation;

use PHPUnit\Framework\TestCase;
use Terminal42\ChangeLanguage\Navigation\UrlParameterBag;

class UrlParameterBagTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['TL_CONFIG']['useAutoItem'] = true;
        unset($GLOBALS['TL_AUTO_ITEM']);
    }

    public function testUrlAttributeGetterAndSetter(): void
    {
        $bag = new UrlParameterBag();

        $this->assertFalse($bag->hasUrlAttribute('foo'));

        $bag->setUrlAttribute('foo', 'bar');

        $this->assertTrue($bag->hasUrlAttribute('foo'));
        $this->assertSame('bar', $bag->getUrlAttribute('foo'));

        $bag->removeUrlAttribute('foo');

        $this->assertFalse($bag->hasUrlAttribute('foo'));

        $bag->setUrlAttributes(['foo' => 'bar']);

        $this->assertTrue($bag->hasUrlAttribute('foo'));
        $this->assertSame(['foo' => 'bar'], $bag->getUrlAttributes());
    }

    public function testQueryParameterGettersAndSetters(): void
    {
        $bag = new UrlParameterBag();

        $this->assertFalse($bag->hasQueryParameter('foo'));

        $bag->setQueryParameter('foo', 'bar');

        $this->assertTrue($bag->hasQueryParameter('foo'));
        $this->assertSame('bar', $bag->getQueryParameter('foo'));

        $bag->removeQueryParameter('foo');

        $this->assertFalse($bag->hasQueryParameter('foo'));

        $bag->setQueryParameters(['foo' => 'bar']);

        $this->assertTrue($bag->hasQueryParameter('foo'));
        $this->assertSame(['foo' => 'bar'], $bag->getQueryParameters());
    }

    public function testGenerateOneParameters(): void
    {
        $bag = new UrlParameterBag(['foo' => 'bar']);

        $this->assertSame('/foo/bar', $bag->generateParameters());
    }

    public function testGenerateMultipleParameters(): void
    {
        $bag = new UrlParameterBag(['foo' => 'bar', 'bar' => 'baz']);

        $this->assertSame('/foo/bar/bar/baz', $bag->generateParameters());
    }

    public function testGenerateSingleAutoItemParameter(): void
    {
        $GLOBALS['TL_AUTO_ITEM'] = ['foo'];
        $bag = new UrlParameterBag(['foo' => 'bar']);

        $this->assertSame('/bar', $bag->generateParameters());
    }

    public function testGenerateMultipleWithAutoItem(): void
    {
        $GLOBALS['TL_AUTO_ITEM'] = ['bar'];
        $bag = new UrlParameterBag(['foo' => 'bar', 'bar' => 'baz']);

        $this->assertSame('/baz/foo/bar', $bag->generateParameters());
    }

    public function testIgnoresAutoItemIfDisabled(): void
    {
        $GLOBALS['TL_CONFIG']['useAutoItem'] = false;
        $GLOBALS['TL_AUTO_ITEM'] = ['foo'];
        $bag = new UrlParameterBag(['foo' => 'bar']);

        $this->assertSame('/foo/bar', $bag->generateParameters());
    }

    public function testReturnsNullOnEmptyParameters(): void
    {
        $bag = new UrlParameterBag();

        $this->assertNull($bag->generateParameters());
    }

    public function testExceptionOnMultipleAutoItems(): void
    {
        $this->expectException('RuntimeException');

        $GLOBALS['TL_AUTO_ITEM'] = ['foo', 'bar'];
        $bag = new UrlParameterBag(['foo' => 'bar', 'bar' => 'baz']);

        $bag->generateParameters();
    }

    public function testExceptionOnConstructNonScalarParameter(): void
    {
        $this->expectException('InvalidArgumentException');

        /** @phpstan-ignore argument.type */
        new UrlParameterBag(['foo' => (object) ['bar']]);
    }

    public function testExceptionOnSettingNonScalarParameter(): void
    {
        $this->expectException('InvalidArgumentException');

        $bag = new UrlParameterBag();

        /** @phpstan-ignore argument.type */
        $bag->setUrlAttribute('foo', (object) ['bar']);
    }

    public function testExceptionOnSettingNonScalarParameters(): void
    {
        $this->expectException('InvalidArgumentException');

        $bag = new UrlParameterBag();

        /** @phpstan-ignore argument.type */
        $bag->setUrlAttributes(['foo' => (object) ['bar']]);
    }

    public function testGenerateSingleQuery(): void
    {
        $bag = new UrlParameterBag([], ['foo' => 'bar']);

        $this->assertSame('foo=bar', $bag->generateQueryString());
    }

    public function testGenerateMultipleQuery(): void
    {
        $bag = new UrlParameterBag([], ['foo' => 'bar', 'bar' => 'baz']);

        $this->assertSame('foo=bar&bar=baz', $bag->generateQueryString());
    }

    public function testGenerateArrayQuery(): void
    {
        $bag = new UrlParameterBag([], ['foo' => ['bar', 'baz']]);

        $this->assertSame(rawurlencode('foo[0]').'=bar&'.rawurlencode('foo[1]').'=baz', $bag->generateQueryString());
    }

    public function testReturnsNullOnEmptyQuery(): void
    {
        $bag = new UrlParameterBag();

        $this->assertNull($bag->generateQueryString());
    }

    public function testExceptionOnConstructNonScalarQuery(): void
    {
        $this->expectException('InvalidArgumentException');

        /** @phpstan-ignore argument.type */
        new UrlParameterBag([], ['foo' => (object) ['bar']]);
    }

    public function testExceptionOnSettingNonScalarQuery(): void
    {
        $this->expectException('InvalidArgumentException');

        $bag = new UrlParameterBag();

        /** @phpstan-ignore argument.type */
        $bag->setQueryParameter('foo', (object) ['bar']);
    }

    public function testExceptionOnSettingNonScalarQuerys(): void
    {
        $this->expectException('InvalidArgumentException');

        $bag = new UrlParameterBag();

        /** @phpstan-ignore argument.type */
        $bag->setQueryParameters(['foo' => (object) ['bar']]);
    }
}
