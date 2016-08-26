<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\Tests;

use Terminal42\ChangeLanguage\Language;

class LanguageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider languagesProvider
     */
    public function testConvertLocaleIdToLanguageTag($localeId, $languageTag)
    {
        $this->assertEquals($languageTag, Language::toLanguageTag($localeId));
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testConvertLanguageTagToLocaleId($localeId, $languageTag)
    {
        $this->assertEquals($localeId, Language::toLocaleID($languageTag));
    }

    /**
     * @dataProvider invalidLanguagesProvider
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidLanguage($language)
    {
        Language::normalize($language, '-');
    }

    public function languagesProvider()
    {
        return [
            ['en', 'en'],
            ['de', 'de'],
            ['en_US', 'en-US'],
            ['de_DE', 'de-DE'],
            ['de_CH', 'de-CH'],
        ];
    }

    public function invalidLanguagesProvider()
    {
        return [
            [''],
            ['-'],
            ['en-'],
            ['en_'],
            ['cn-Hant'],
            ['cn-Hant-TW'],
        ];
    }
}
