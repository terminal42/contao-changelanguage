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

    public function __construct(array $map = [])
    {
        foreach ($map as $k => $v) {
            $this->map[strtolower($k)] = (string) $v;
        }
    }

    /**
     * Returns whether a language has a label.
     */
    public function has(string $language): bool
    {
        return \array_key_exists(strtolower($language), $this->map);
    }

    /**
     * Gets the label for a language, optionally replacing insert tags.
     */
    public function get(string $language, bool $replaceInsertTags = true): string
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
     */
    public function set(string $language, string $label): void
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

                return $key1 <=> $key2;
            },
        );
    }

    /**
     * Create instance from serialized data of optionsWizard widget.
     */
    public static function createFromOptionWizard($config): self
    {
        $config = StringUtil::deserialize($config);

        if (!\is_array($config)) {
            return new static();
        }

        $map = [];

        foreach ($config as $text) {
            // Backwards compatibility with Multicolumnwizard data
            if (isset($text['value'], $text['label'])) {
                if (empty($text['label']) || empty($text['value'])) {
                    continue;
                }

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
