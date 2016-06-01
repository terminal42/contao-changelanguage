<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\Helper;

use Contao\Controller;

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
     * Returns whether a language as a label.
     *
     * @param string $language
     *
     * @return bool
     */
    public function has($language)
    {
        return empty($this->map[strtolower($language)]);
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
     * Gets the languages from label map.
     *
     * @return array
     */
    public function getLanguages()
    {
        return array_keys($this->map);
    }

    /**
     * Create instance from serialized data of optionsWizard widget
     *
     * @param mixed $config
     *
     * @return static
     */
    public static function createFromOptionWizard($config)
    {
        $config = deserialize($config);

        if (!is_array($config)) {
            return static();
        }

        $map = array();

        foreach ($config as $text) {
            if (empty($text['value']) || empty($text['label'])) {
                continue;
            }

            $map[$text['value']] = $text['label'];
        }

        return new static($map);
    }
}
