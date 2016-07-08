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
    public function testGenerateOneParameters()
    {
        $bag = new UrlParameterBag(['foo' => 'bar']);

        $this->assertEquals('foo/bar', $bag->generateParameters());
    }

    public function testGenerateMultipleParameters()
    {
        $bag = new UrlParameterBag(['foo' => 'bar', 'bar' => 'baz']);

        $this->assertEquals('foo/bar/bar/baz', $bag->generateParameters());
    }

    public function testGenerateSingleAutoItemParameter()
    {
        $bag = new UrlParameterBag(['auto_item' => 'foobar']);

        $this->assertEquals('foobar', $bag->generateParameters());
    }

    public function testGenerateMultipleWithAutoItem()
    {
        $bag = new UrlParameterBag(['foo' => 'bar', 'auto_item' => 'baz']);

        $this->assertEquals('baz/foo/bar', $bag->generateParameters());
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
