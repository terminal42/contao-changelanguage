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
}
