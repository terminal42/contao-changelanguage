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
use Terminal42\ChangeLanguage\Language;
use Terminal42\ChangeLanguage\NavigationItem;

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
        return array_key_exists(Language::toLanguageTag($language), $this->links);
    }

    /**
     * Adds or replaces a link for a language.
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
     * Adds a link from a NavigationItem instance.
     *
     * @param NavigationItem $item
     */
    public function addFromNavigationItem(NavigationItem $item)
    {
        $this->add($item->getLanguageTag(), $item->getHref(), $item->getTitle());
    }

    /**
     * Removes link for a language if it exists.
     *
     * @param string $language
     */
    public function remove($language)
    {
        unset($this->links[Language::toLanguageTag($language)]);
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
        $language = Language::toLanguageTag($language);

        $this->links[$language] = ['language' => $language, 'href' => $href, 'title' => $title];
    }
}
