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
use Terminal42\ChangeLanguage\PageFinder;

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
     * @var PageModel
     */
    private $currentPage;

    /**
     * Constructor.
     *
     * @param PageFinder   $pageFinder
     * @param LanguageText $languageText
     * @param PageModel    $currentPage
     */
    public function __construct(PageFinder $pageFinder, LanguageText $languageText, PageModel $currentPage)
    {
        $this->pageFinder   = $pageFinder;
        $this->languageText = $languageText;
        $this->currentPage  = $currentPage;
    }

    /**
     * @param PageModel $currentPage
     * @param bool      $hideActive
     * @param bool      $hideNoFallback
     *
     * @return NavigationItem[]
     *
     * @throws \OverflowException
     * @throws \UnderflowException
     */
    public function findNavigationItems(PageModel $currentPage, $hideActive, $hideNoFallback)
    {
        $rootPages = $this->pageFinder->findRootPagesForPage($currentPage);
        $navigationItems = $this->createNavigationItemsForRootPages($rootPages);

        $this->setTargetPageForNavigationItems(
            $navigationItems,
            $rootPages,
            $this->pageFinder->findAssociatedForPage($currentPage)
        );

        foreach ($navigationItems as $k => $item) {
            if ($hideActive && $item->isCurrentPage()) {
                unset($navigationItems[$k]);
                continue;
            }

            if ($hideNoFallback && !$item->isDirectFallback()) {
                unset($navigationItems[$k]);
                continue;
            }

            if (!$item->hasTargetPage()) {
                $item->setTargetPage(
                    $this->pageFinder->findAssociatedParentForLanguage($currentPage, $item->getLanguageTag()),
                    false,
                    false
                );
            }
        }

        $this->languageText->orderNavigationItems($navigationItems);

        return array_values($navigationItems);
    }

    /**
     * Builds NavigationItem's from given root pages.
     *
     * @param NavigationItem[] $rootPages
     *
     * @return NavigationItem[]
     *
     * @throws \OverflowException if multiple root pages have the same language
     */
    private function createNavigationItemsForRootPages(array $rootPages)
    {
        $navigationItems = [];

        foreach ($rootPages as $rootPage) {
            $language = strtolower($rootPage->language);

            if (array_key_exists($language, $navigationItems)) {
                throw new \OverflowException(
                    sprintf('Multiple root pages for the language "%s" found', $rootPage->language)
                );
            }

            $navigationItems[$language] = new NavigationItem($rootPage, $this->languageText->get($language));
        }

        return $navigationItems;
    }

    /**
     * Sets the target page for navigation items based on list of associated pages.
     *
     * @param NavigationItem[] $navigationItems
     * @param PageModel[]      $rootPages
     * @param PageModel[]      $associatedPages
     *
     * @throws \UnderflowException
     */
    private function setTargetPageForNavigationItems(array $navigationItems, array $rootPages, array $associatedPages)
    {
        foreach ($associatedPages as $page) {
            $page->loadDetails();

            if (!array_key_exists($page->rootId, $rootPages)) {
                throw new \UnderflowException(sprintf('Missing root page for language "%s"', $page->language));
            }

            $language      = strtolower($page->language);
            $isCurrentPage = $this->currentPage->id === $page->id;

            $navigationItems[$language]->setTargetPage($page, true, $isCurrentPage);
        }
    }
}
