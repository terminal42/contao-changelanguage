<?php

namespace Terminal42\ChangeLanguage\Tests\Navigation;

use Terminal42\ChangeLanguage\Navigation\UrlParameterBag;

class UrlParameterBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $GLOBALS['TL_CONFIG']['useAutoItem'] = true;
        unset($GLOBALS['TL_AUTO_ITEM']);
    }

    public function testUrlAttributeGetterAndSetter()
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

    public function testQueryParameterGettersAndSetters()
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

    public function testGenerateOneParameters()
    {
        $bag = new UrlParameterBag(['foo' => 'bar']);

        $this->assertSame('/foo/bar', $bag->generateParameters());
    }

    public function testGenerateMultipleParameters()
    {
        $bag = new UrlParameterBag(['foo' => 'bar', 'bar' => 'baz']);

        $this->assertSame('/foo/bar/bar/baz', $bag->generateParameters());
    }

    public function testGenerateSingleAutoItemParameter()
    {
        $GLOBALS['TL_AUTO_ITEM'] = ['foo'];
        $bag = new UrlParameterBag(['foo' => 'bar']);

        $this->assertSame('/bar', $bag->generateParameters());
    }

    public function testGenerateMultipleWithAutoItem()
    {
        $GLOBALS['TL_AUTO_ITEM'] = ['bar'];
        $bag = new UrlParameterBag(['foo' => 'bar', 'bar' => 'baz']);

        $this->assertSame('/baz/foo/bar', $bag->generateParameters());
    }

    public function testIgnoresAutoItemIfDisabled()
    {
        $GLOBALS['TL_CONFIG']['useAutoItem'] = false;
        $GLOBALS['TL_AUTO_ITEM'] = ['foo'];
        $bag = new UrlParameterBag(['foo' => 'bar']);

        $this->assertSame('/foo/bar', $bag->generateParameters());
    }

    public function testReturnsNullOnEmptyParameters()
    {
        $bag = new UrlParameterBag();

        $this->assertNull($bag->generateParameters());
    }

    public function testExceptionOnAutoItemKey()
    {
        $this->setExpectedException('RuntimeException');

        $bag = new UrlParameterBag(['auto_item' => 'baz']);

        $bag->generateParameters();
    }

    public function testExceptionOnMulitpleAutoItems()
    {
        $this->setExpectedException('RuntimeException');

        $GLOBALS['TL_AUTO_ITEM'] = ['foo', 'bar'];
        $bag = new UrlParameterBag(['foo' => 'bar', 'bar' => 'baz']);

        $bag->generateParameters();
    }

    public function testExceptionOnConstructNonScalarParameter()
    {
        $this->setExpectedException('InvalidArgumentException');

        new UrlParameterBag(['foo' => (object) ['bar']]);
    }

    public function testExceptionOnSettingNonScalarParameter()
    {
        $this->setExpectedException('InvalidArgumentException');

        $bag = new UrlParameterBag();

        $bag->setUrlAttribute('foo', (object) ['bar']);
    }

    public function testExceptionOnSettingNonScalarParameters()
    {
        $this->setExpectedException('InvalidArgumentException');

        $bag = new UrlParameterBag();

        $bag->setUrlAttributes(['foo' => (object) ['bar']]);
    }

    public function testGenerateSingleQuery()
    {
        $bag = new UrlParameterBag([], ['foo' => 'bar']);

        $this->assertSame('foo=bar', $bag->generateQueryString());
    }

    public function testGenerateMultipleQuery()
    {
        $bag = new UrlParameterBag([], ['foo' => 'bar', 'bar' => 'baz']);

        $this->assertSame('foo=bar&bar=baz', $bag->generateQueryString());
    }

    public function testGenerateArrayQuery()
    {
        $bag = new UrlParameterBag([], ['foo' => ['bar', 'baz']]);

        $this->assertSame(rawurlencode('foo[0]').'=bar&'.rawurlencode('foo[1]').'=baz', $bag->generateQueryString());
    }

    public function testReturnsNullOnEmptyQuery()
    {
        $bag = new UrlParameterBag();

        $this->assertNull($bag->generateQueryString());
    }

    public function testExceptionOnConstructNonScalarQuery()
    {
        $this->setExpectedException('InvalidArgumentException');

        new UrlParameterBag([], ['foo' => (object) ['bar']]);
    }

    public function testExceptionOnSettingNonScalarQuery()
    {
        $this->setExpectedException('InvalidArgumentException');

        $bag = new UrlParameterBag();

        $bag->setQueryParameter('foo', (object) ['bar']);
    }

    public function testExceptionOnSettingNonScalarQuerys()
    {
        $this->setExpectedException('InvalidArgumentException');

        $bag = new UrlParameterBag();

        $bag->setQueryParameters(['foo' => (object) ['bar']]);
    }
}
