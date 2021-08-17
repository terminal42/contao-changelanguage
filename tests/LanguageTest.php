<?php

namespace Terminal42\ChangeLanguage\Tests;

use Terminal42\ChangeLanguage\Language;

class LanguageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $localeId
     * @param mixed $languageTag
     *
     * @dataProvider languagesProvider
     */
    public function testConvertLocaleIdToLanguageTag($localeId, $languageTag)
    {
        $this->assertSame($languageTag, Language::toLanguageTag($localeId));
    }

    /**
     * @param mixed $localeId
     * @param mixed $languageTag
     *
     * @dataProvider languagesProvider
     */
    public function testConvertLanguageTagToLocaleId($localeId, $languageTag)
    {
        $this->assertSame($localeId, Language::toLocaleID($languageTag));
    }

    /**
     * @param mixed $language
     *
     * @dataProvider invalidLanguagesProvider
     */
    public function testInvalidLanguage($language)
    {
        $this->setExpectedException('InvalidArgumentException');

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
