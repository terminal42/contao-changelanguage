<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Navigation;

use Contao\PageModel;
use Contao\System;
use Terminal42\ChangeLanguage\Helper\LanguageText;
use Terminal42\ChangeLanguage\PageFinder;

class NavigationFactory
{
    private PageFinder $pageFinder;
    private LanguageText $languageText;
    private PageModel $currentPage;
    private array $locales = [];

    public function __construct(PageFinder $pageFinder, LanguageText $languageText, PageModel $currentPage, array $locales = [])
    {
        $this->pageFinder = $pageFinder;
        $this->languageText = $languageText;
        $this->currentPage = $currentPage;
        $this->locales = $locales;
    }

    /**
     * @return array<NavigationItem>
     *
     * @throws \RuntimeException
     */
    public function findNavigationItems(PageModel $currentPage): array
    {
        $rootPages = $this->pageFinder->findRootPagesForPage($currentPage, false, false);
        $navigationItems = $this->createNavigationItemsForRootPages($rootPages);

        $this->setTargetPageForNavigationItems(
            $navigationItems,
            $rootPages,
            $this->pageFinder->findAssociatedForPage($currentPage, false, $rootPages)
        );

        foreach ($navigationItems as $item) {
            if (isset($this->locales[$item->getLocaleId()])) {
                $item->setAriaLabel(
                    $item->isDirectFallback()
                        ? sprintf($GLOBALS['TL_LANG']['MSC']['gotoLanguage'], $this->locales[$item->getLocaleId()])
                        : sprintf($GLOBALS['TL_LANG']['MSC']['switchLanguageTo'][1], $this->locales[$item->getLocaleId()])
                );
            }

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
     * @param array<PageModel> $rootPages
     *
     * @return array<NavigationItem>
     *
     * @throws \RuntimeException if multiple root pages have the same language
     */
    private function createNavigationItemsForRootPages(array $rootPages): array
    {
        $navigationItems = [];

        foreach ($rootPages as $rootPage) {
            if (!$this->isPagePublished($rootPage)) {
                continue;
            }

            $language = strtolower($rootPage->language);

            if (\array_key_exists($language, $navigationItems)) {
                throw new \RuntimeException(sprintf('Multiple root pages for the language "%s" found', $rootPage->language));
            }

            $navigationItems[$language] = new NavigationItem($rootPage, $this->languageText->get($language));
        }

        return $navigationItems;
    }

    /**
     * Sets the target page for navigation items based on list of associated pages.
     *
     * @param array<NavigationItem> $navigationItems
     * @param array<PageModel>      $rootPages
     * @param array<PageModel>      $associatedPages
     *
     * @throws \RuntimeException
     */
    private function setTargetPageForNavigationItems(array $navigationItems, array $rootPages, array $associatedPages): void
    {
        foreach ($associatedPages as $page) {
            $page->loadDetails();

            if (!\array_key_exists($page->rootId, $rootPages)) {
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
     */
    private function isPagePublished(PageModel $page): bool
    {
        if (System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
            return true;
        }

        $time = time();

        return $page->published
            && ('' === $page->start || $page->start < $time)
            && ('' === $page->stop || $page->stop > $time);
    }
}
