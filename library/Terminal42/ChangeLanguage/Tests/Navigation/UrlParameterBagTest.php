<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\Tests\Navigation;

use Terminal42\ChangeLanguage\Navigation\UrlParameterBag;

class UrlParameterBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @inheritDoc
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
        $this->assertEquals('bar', $bag->getUrlAttribute('foo'));

        $bag->removeUrlAttribute('foo');

        $this->assertFalse($bag->hasUrlAttribute('foo'));

        $bag->setUrlAttributes(['foo' => 'bar']);

        $this->assertTrue($bag->hasUrlAttribute('foo'));
        $this->assertEquals(['foo' => 'bar'], $bag->getUrlAttributes());
    }

    public function testQueryParameterGettersAndSetters()
    {
        $bag = new UrlParameterBag();

        $this->assertFalse($bag->hasQueryParameter('foo'));

        $bag->setQueryParameter('foo', 'bar');

        $this->assertTrue($bag->hasQueryParameter('foo'));
        $this->assertEquals('bar', $bag->getQueryParameter('foo'));

        $bag->removeQueryParameter('foo');

        $this->assertFalse($bag->hasQueryParameter('foo'));

        $bag->setQueryParameters(['foo' => 'bar']);

        $this->assertTrue($bag->hasQueryParameter('foo'));
        $this->assertEquals(['foo' => 'bar'], $bag->getQueryParameters());
    }

    public function testGenerateOneParameters()
    {
        $bag = new UrlParameterBag(['foo' => 'bar']);

        $this->assertEquals('/foo/bar', $bag->generateParameters());
    }

    public function testGenerateMultipleParameters()
    {
        $bag = new UrlParameterBag(['foo' => 'bar', 'bar' => 'baz']);

        $this->assertEquals('/foo/bar/bar/baz', $bag->generateParameters());
    }

    public function testGenerateSingleAutoItemParameter()
    {
        $GLOBALS['TL_AUTO_ITEM'] = ['foo'];
        $bag = new UrlParameterBag(['foo' => 'bar']);

        $this->assertEquals('/bar', $bag->generateParameters());
    }

    public function testGenerateMultipleWithAutoItem()
    {
        $GLOBALS['TL_AUTO_ITEM'] = ['bar'];
        $bag = new UrlParameterBag(['foo' => 'bar', 'bar' => 'baz']);

        $this->assertEquals('/baz/foo/bar', $bag->generateParameters());
    }

    public function testIgnoresAutoItemIfDisabled()
    {
        $GLOBALS['TL_CONFIG']['useAutoItem'] = false;
        $GLOBALS['TL_AUTO_ITEM'] = ['foo'];
        $bag = new UrlParameterBag(['foo' => 'bar']);

        $this->assertEquals('/foo/bar', $bag->generateParameters());
    }

    public function testReturnsNullOnEmptyParameters()
    {
        $bag = new UrlParameterBag();

        $this->assertEquals(null, $bag->generateParameters());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExceptionOnAutoItemKey()
    {
        $bag = new UrlParameterBag(['auto_item' => 'baz']);

        $bag->generateParameters();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testExceptionOnMulitpleAutoItems()
    {
        $GLOBALS['TL_AUTO_ITEM'] = ['foo', 'bar'];
        $bag = new UrlParameterBag(['foo' => 'bar', 'bar' => 'baz']);

        $bag->generateParameters();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnConstructNonScalarParameter()
    {
        new UrlParameterBag(['foo' => (object) ['bar']]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnSettingNonScalarParameter()
    {
        $bag = new UrlParameterBag();

        $bag->setUrlAttribute('foo', (object) ['bar']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnSettingNonScalarParameters()
    {
        $bag = new UrlParameterBag();

        $bag->setUrlAttributes(['foo' => (object) ['bar']]);
    }

    public function testGenerateSingleQuery()
    {
        $bag = new UrlParameterBag([], ['foo' => 'bar']);

        $this->assertEquals('foo=bar', $bag->generateQueryString());
    }

    public function testGenerateMultipleQuery()
    {
        $bag = new UrlParameterBag([], ['foo' => 'bar', 'bar' => 'baz']);

        $this->assertEquals('foo=bar&bar=baz', $bag->generateQueryString());
    }

    public function testReturnsNullOnEmptyQuery()
    {
        $bag = new UrlParameterBag();

        $this->assertEquals(null, $bag->generateQueryString());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnConstructNonScalarQuery()
    {
        new UrlParameterBag([], ['foo' => (object) ['bar']]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnSettingNonScalarQuery()
    {
        $bag = new UrlParameterBag();

        $bag->setQueryParameter('foo', (object) ['bar']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionOnSettingNonScalarQuerys()
    {
        $bag = new UrlParameterBag();

        $bag->setQueryParameters(['foo' => (object) ['bar']]);
    }
}
