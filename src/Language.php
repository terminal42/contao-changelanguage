<?php

namespace Terminal42\ChangeLanguage;

class Language
{
    /**
     * Normalizes a language representation by splitting language and country with given delimiter.
     *
     * @param string $language
     * @param string $delimiter
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function normalize($language, $delimiter)
    {
        if (!preg_match('#^([a-z]{2})((-|_)([A-Z]{2}))?$#i', $language, $matches)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a supported language format.', $language));
        }

        return strtolower($matches[1]).(isset($matches[4]) ? ($delimiter.strtoupper($matches[4])) : '');
    }

    /**
     * Returns the language formatted as IETF Language Tag (BCP 47)
     * Example: en, en-US, de-CH.
     *
     * @see http://www.w3.org/International/articles/language-tags/
     *
     * @param string $language
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function toLanguageTag($language)
    {
        return static::normalize($language, '-');
    }

    /**
     * Returns the language formatted as ICU Locale ID
     * Example: en, en_US, de_CH.
     *
     * @see http://userguide.icu-project.org/locale
     *
     * @param string $language
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function toLocaleID($language)
    {
        return static::normalize($language, '_');
    }
}
