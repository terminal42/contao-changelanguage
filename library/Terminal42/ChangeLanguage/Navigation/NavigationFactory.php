<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\Navigation;

use Contao\PageModel;
use Terminal42\ChangeLanguage\Helper\LanguageText;

class NavigationFactory
{
    /**
     * @var PageFinder
     */
    private $pageFinder;

    /**
     * @var LanguageText
     */
    private $languageText;

    /**
     * Constructor.
     *
     * @param PageFinder   $pageFinder
     * @param LanguageText $languageText
     */
    public function __construct(PageFinder $pageFinder, LanguageText $languageText)
    {
        $this->pageFinder   = $pageFinder;
        $this->languageText = $languageText;
    }

    /**
     * @param PageModel $currentPage
     * @param bool      $hideActive
     * @param bool      $hideNoFallback
     *
     * @return NavigationItem[]
     */
    public function findNavigationItems(PageModel $currentPage, $hideActive, $hideNoFallback)
    {
        /** @var NavigationItem[] $navigationItems */
        $navigationItems = [];
        $rootPages = $this->pageFinder->findRootPagesForPage($currentPage);

        foreach ($rootPages as $rootPage) {
            $language = strtolower($rootPage->language);

            if (array_key_exists($language, $navigationItems)) {
                throw new \OverflowException(
                    sprintf('Multiple root pages for the language "%s" found', $rootPage->language)
                );
            }

            $navigationItems[$language] = new NavigationItem($rootPage, $this->languageText->get($language));
        }

        foreach ($this->pageFinder->findAssociatedForPage($currentPage) as $page) {
            $page->loadDetails();

            if (array_key_exists($page->rootId, $rootPages)) {
                $navigationItems[strtolower($page->language)]->setTargetPage($page, true);
            }
        }

        foreach ($navigationItems as $k => $item) {
            if ($hideActive && $item->isIsCurrentPage()) {
                unset($navigationItems[$k]);
                continue;
            }

            if ($hideNoFallback && !$item->isIsDirectFallback()) {
                unset($navigationItems[$k]);
                continue;
            }

            if (!$item->hasTargetPage()) {
                $item->setTargetPage(
                    $this->pageFinder->findAssociatedParentForLanguage($currentPage, $item->getLanguageTag()),
                    false
                );
            }
        }

        $this->languageText->orderNavigationItems($navigationItems);

        return array_values($navigationItems);
    }
}
