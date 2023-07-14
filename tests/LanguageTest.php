<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Tests;

use PHPUnit\Framework\TestCase;
use Terminal42\ChangeLanguage\Language;

class LanguageTest extends TestCase
{
    /**
     * @param mixed $localeId
     * @param mixed $languageTag
     *
     * @dataProvider languagesProvider
     */
    public function testConvertLocaleIdToLanguageTag($localeId, $languageTag): void
    {
        $this->assertSame($languageTag, Language::toLanguageTag($localeId));
    }

    /**
     * @param mixed $localeId
     * @param mixed $languageTag
     *
     * @dataProvider languagesProvider
     */
    public function testConvertLanguageTagToLocaleId($localeId, $languageTag): void
    {
        $this->assertSame($localeId, Language::toLocaleID($languageTag));
    }

    /**
     * @param mixed $language
     *
     * @dataProvider invalidLanguagesProvider
     */
    public function testInvalidLanguage($language): void
    {
        $this->expectException('InvalidArgumentException');

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
