<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Helper;

use Contao\StringUtil;
use Contao\System;
use Terminal42\ChangeLanguage\Navigation\NavigationItem;

/**
 * LanguageText stores a list of labels for language keys.
 */
class LanguageText
{
    private array $map = [];

    /**
     * Constructor.
     */
    public function __construct(array $map = [])
    {
        foreach ($map as $k => $v) {
            $this->map[strtolower($k)] = $v;
        }
    }

    /**
     * Returns whether a language has a label.
     *
     * @param string $language
     *
     * @return bool
     */
    public function has($language)
    {
        return \array_key_exists(strtolower($language), $this->map);
    }

    /**
     * Gets the label for a language, optionally replacing insert tags.
     *
     * @param string $language
     * @param bool   $replaceInsertTags
     *
     * @return string
     */
    public function get($language, $replaceInsertTags = true)
    {
        $language = strtolower($language);

        if (empty($this->map[$language])) {
            return strtoupper($language);
        }

        $value = $this->map[$language];

        if ($replaceInsertTags) {
            $value = System::getContainer()->get('contao.insert_tag.parser')->replace($value);
        }

        return $value;
    }

    /**
     * Adds a label for a language.
     *
     * @param string $language
     * @param string $label
     */
    public function set($language, $label): void
    {
        $this->map[strtolower($language)] = $label;
    }

    /**
     * Order an array of NavigationItem's by our custom labels.
     *
     * @param array<NavigationItem> $items
     */
    public function orderNavigationItems(array &$items): void
    {
        if (0 === \count($this->map)) {
            return;
        }

        $languages = array_keys($this->map);

        usort(
            $items,
            static function (NavigationItem $a, NavigationItem $b) use ($languages) {
                $key1 = array_search(strtolower($a->getLanguageTag()), $languages, true);
                $key2 = array_search(strtolower($b->getLanguageTag()), $languages, true);

                return $key1 < $key2 ? -1 : 1;
            }
        );
    }

    /**
     * Create instance from serialized data of optionsWizard widget.
     *
     * @param mixed $config
     *
     * @return static
     */
    public static function createFromOptionWizard($config)
    {
        $config = StringUtil::deserialize($config);

        if (!\is_array($config)) {
            return new static();
        }

        $map = [];

        foreach ($config as $text) {
            // Backwards compatibility with Multicolumnwizard data
            if (isset($text['value'], $text['label'])) {
                $map[$text['value']] = $text['label'];
                continue;
            }

            if (empty($text['key']) || empty($text['value'])) {
                continue;
            }

            $map[$text['key']] = $text['value'];
        }

        return new static($map);
    }
}
