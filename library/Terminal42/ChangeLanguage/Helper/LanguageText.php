<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2017, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\Helper;

use Contao\Controller;
use Terminal42\ChangeLanguage\Navigation\NavigationItem;

/**
 * LanguageText stores a list of labels for language keys.
 */
class LanguageText
{
    /**
     * @var array
     */
    private $map = [];

    /**
     * Constructor.
     *
     * @param array $map
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
        return array_key_exists(strtolower($language), $this->map);
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
            $value = Controller::replaceInsertTags($value);
        }

        return $value;
    }

    /**
     * Adds a label for a language.
     *
     * @param string $language
     * @param string $label
     */
    public function set($language, $label)
    {
        $this->map[strtolower($language)] = $label;
    }

    /**
     * Order an array of NavigationItem's by our custom labels.
     *
     * @param NavigationItem[] $items
     */
    public function orderNavigationItems(array &$items)
    {
        if (0 === count($this->map)) {
            return;
        }

        $languages = array_keys($this->map);

        usort($items, function (NavigationItem $a, NavigationItem $b) use ($languages) {
            $key1 = array_search(strtolower($a->getLanguageTag()), $languages, true);
            $key2 = array_search(strtolower($b->getLanguageTag()), $languages, true);

            return ($key1 < $key2) ? -1 : 1;
        });
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
        $config = deserialize($config);

        if (!is_array($config)) {
            return new static();
        }

        $map = [];

        foreach ($config as $text) {
            if (empty($text['value']) || empty($text['label'])) {
                continue;
            }

            $map[$text['value']] = $text['label'];
        }

        return new static($map);
    }
}
