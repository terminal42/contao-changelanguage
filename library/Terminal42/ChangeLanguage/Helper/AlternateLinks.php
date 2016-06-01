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

use Contao\FrontendTemplate;

/**
 * AlternateLinks is a helper class to handle <link rel="alternate"> in the page header.
 *
 * @package Terminal42\ChangeLanguage\Helper
 */
class AlternateLinks
{
    /**
     * @var array
     */
    private $links = [];

    /**
     * Returns whether a link already exists for a language.
     *
     * @param string $language
     *
     * @return bool
     */
    public function has($language)
    {
        return array_key_exists($this->normalize($language), $this->links);
    }

    /**
     * Adds or replaces a link for a language
     *
     * @param string $language
     * @param string $href
     * @param string $title
     */
    public function add($language, $href, $title = '')
    {
        $this->store($language, $href, $title);
    }

    /**
     * Removes link for a language if it exists.
     *
     * @param string $language
     */
    public function remove($language)
    {
        unset($this->links[$this->normalize($language)]);
    }

    /**
     * Sets link for the x-default language.
     *
     * @param string $href
     * @param string $title
     */
    public function setDefault($href, $title = '')
    {
        $this->store('x-default', $href, $title);
    }

    /**
     * Generates template markup of links for the page header.
     *
     * @param string $template
     *
     * @return string
     */
    public function generate($template = 'block_alternate_links')
    {
        if (0 === count($this->links)) {
            return '';
        }

        $template = new FrontendTemplate($template);

        $template->links = $this->links;

        return $template->parse();
    }

    /**
     * @param string $language
     * @param string $href
     * @param string $title
     */
    private function store($language, $href, $title)
    {
        $language = $this->normalize($language);

        $this->links[$language] = ['language' => $language, 'href' => $href, 'title' => $title];
    }

    /**
     * Normalizes language to a correct IETF Language Tag (BCP 47).
     *
     * @param string $language
     *
     * @return string
     *
     * @throws \InvalidArgumentException if the language is not a valid format (BCP 47 or ICU Locale ID)
     */
    private function normalize($language)
    {
        if (!preg_match('#([a-z]{2})((-|_)([A-Z]{2}))?#i', $language, $matches)) {
            throw new \InvalidArgumentException($language . ' is not a supported language format.');
        }

        return strtolower($matches[1]) . '-' . strtoupper($matches[4]);
    }
}
