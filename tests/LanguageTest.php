<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Tests;

use PHPUnit\Framework\TestCase;
use Terminal42\ChangeLanguage\Language;

class LanguageTest extends TestCase
{
    /**
     * @dataProvider languagesProvider
     */
    public function testConvertLocaleIdToLanguageTag(string $localeId, string $languageTag): void
    {
        $this->assertSame($languageTag, Language::toLanguageTag($localeId));
    }

    /**
     * @dataProvider languagesProvider
     */
    public function testConvertLanguageTagToLocaleId(string $localeId, string $languageTag): void
    {
        $this->assertSame($localeId, Language::toLocaleID($languageTag));
    }

    /**
     * @dataProvider invalidLanguagesProvider
     */
    public function testInvalidLanguage(string $language): void
    {
        $this->expectException('InvalidArgumentException');

        Language::normalize($language, '-');
    }

    public function languagesProvider(): \Generator
    {
        yield ['en', 'en'];
        yield ['de', 'de'];
        yield ['en_US', 'en-US'];
        yield ['de_DE', 'de-DE'];
        yield ['de_CH', 'de-CH'];
    }

    public function invalidLanguagesProvider(): \Generator
    {
        yield [''];
        yield ['-'];
        yield ['en-'];
        yield ['en_'];
        yield ['cn-Hant'];
        yield ['cn-Hant-TW'];
    }
}
