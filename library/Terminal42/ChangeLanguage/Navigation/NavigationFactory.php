<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2017, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
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
        $this->pageFinder = $pageFinder;
        $this->languageText = $languageText;
        $this->currentPage = $currentPage;
    }

    /**
     * @param PageModel $currentPage
     *
     * @throws \RuntimeException
     *
     * @return NavigationItem[]
     */
    public function findNavigationItems(PageModel $currentPage)
    {
        $rootPages = $this->pageFinder->findRootPagesForPage($currentPage, false, false);
        $navigationItems = $this->createNavigationItemsForRootPages($rootPages);

        $this->setTargetPageForNavigationItems(
            $navigationItems,
            $rootPages,
            $this->pageFinder->findAssociatedForPage($currentPage, false, $rootPages)
        );

        foreach ($navigationItems as $k => $item) {
            if (!$item->hasTargetPage()) {
                try {
                    $item->setTargetPage(
                        $this
                            ->pageFinder
                            ->findAssociatedParentForLanguage($currentPage, $item->getLanguageTag()),
                        false
                    );
                } catch (\RuntimeException $e) {
                    // parent page of current page not found or not published
                }
            }
        }

        $this->languageText->orderNavigationItems($navigationItems);

        return array_values($navigationItems);
    }

    /**
     * Builds NavigationItem's from given root pages.
     *
     * @param PageModel[] $rootPages
     *
     * @throws \RuntimeException if multiple root pages have the same language
     *
     * @return NavigationItem[]
     */
    private function createNavigationItemsForRootPages(array $rootPages)
    {
        $navigationItems = [];

        foreach ($rootPages as $rootPage) {
            if (!$this->isPagePublished($rootPage)) {
                continue;
            }

            $language = strtolower($rootPage->language);

            if (array_key_exists($language, $navigationItems)) {
                throw new \RuntimeException(
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
     * @throws \RuntimeException
     */
    private function setTargetPageForNavigationItems(array $navigationItems, array $rootPages, array $associatedPages)
    {
        foreach ($associatedPages as $page) {
            $page->loadDetails();

            if (!array_key_exists($page->rootId, $rootPages)) {
                throw new \RuntimeException(sprintf('Missing root page for language "%s"', $page->language));
            }

            if (!$this->isPagePublished($rootPages[$page->rootId])) {
                continue;
            }

            $language = strtolower($page->language);
            $isCurrentPage = $this->currentPage->id === $page->id;

            $navigationItems[$language]->setTargetPage($page, true, $isCurrentPage);
        }
    }

    /**
     * Returns whether the given page is published.
     *
     * @param PageModel $page
     *
     * @return bool
     */
    private function isPagePublished(PageModel $page)
    {
        $time = time();

        return $page->published
            && ($page->start === '' || $page->start < $time)
            && ($page->stop === '' || $page->stop > $time)
        ;
    }
}
