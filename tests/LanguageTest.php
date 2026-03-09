<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Terminal42\ChangeLanguage\Language;

final class LanguageTest extends TestCase
{
    #[DataProvider('languagesProvider')]
    public function testConvertLocaleIdToLanguageTag(string $localeId, string $languageTag): void
    {
        $this->assertSame($languageTag, Language::toLanguageTag($localeId));
    }

    #[DataProvider('languagesProvider')]
    public function testConvertLanguageTagToLocaleId(string $localeId, string $languageTag): void
    {
        $this->assertSame($localeId, Language::toLocaleID($languageTag));
    }

    #[DataProvider('invalidLanguagesProvider')]
    public function testInvalidLanguage(string $language): void
    {
        $this->expectException('InvalidArgumentException');

        Language::normalize($language, '-');
    }

    /**
     * @return iterable<array<int, string>>
     */
    public static function languagesProvider(): iterable
    {
        yield ['en', 'en'];
        yield ['de', 'de'];
        yield ['en_US', 'en-US'];
        yield ['de_DE', 'de-DE'];
        yield ['de_CH', 'de-CH'];
    }

    /**
     * @return iterable<array<int, string>>
     */
    public static function invalidLanguagesProvider(): iterable
    {
        yield [''];
        yield ['-'];
        yield ['en-'];
        yield ['en_'];
        yield ['cn-Hant'];
        yield ['cn-Hant-TW'];
    }
}
