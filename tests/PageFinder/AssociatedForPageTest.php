<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Tests\PageFinder;

use Contao\PageModel;
use Terminal42\ChangeLanguage\PageFinder;
use Terminal42\ChangeLanguage\Tests\ContaoTestCase;

class AssociatedForPageTest extends ContaoTestCase
{
    private PageFinder $pageFinder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageFinder = new PageFinder();
        $GLOBALS['TL_LANG'] = [];
    }

    public function testFindsOnePage(): void
    {
        $pageModel = $this->createPage();

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 1);
    }

    public function testFindsAllFromFallback(): void
    {
        $enRoot = $this->createRootPage('', 'en');
        $deRoot = $this->createRootPage('', 'de', false);
        $frRoot = $this->createRootPage('', 'fr', false);

        $pageModel = $this->createPage($enRoot->id);

        $this->createPage($frRoot->id, $pageModel->id);
        $this->createPage($deRoot->id, $pageModel->id);

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 3);
    }

    public function testFindsAllFromRelated(): void
    {
        $enRoot = $this->createRootPage('', 'en');
        $deRoot = $this->createRootPage('', 'de', false);
        $frRoot = $this->createRootPage('', 'fr', false);

        $fallback = $this->createPage($enRoot->id);

        $pageModel = $this->createPage($deRoot->id, $fallback->id);

        $this->createPage($frRoot->id, $fallback->id);

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 3);
    }

    public function testIgnoresEmptyMain(): void
    {
        $this->createPage();
        $this->createPage();

        $pageModel = $this->createPage();

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 1);
    }

    public function testIgnoresLanguageMainOnFallback(): void
    {
        $enRoot = $this->createRootPage('', 'en');
        $deRoot = $this->createRootPage('', 'de', false);

        $fallback = $this->createPage($deRoot->id);

        $pageModel = $this->createPage($enRoot->id, $fallback->id);

        $this->createPage(0, $fallback->id);

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 1);
    }

    public function testFindsRootsForRootPage(): void
    {
        $en = $this->createRootPage('', 'en');
        $de = $this->createRootPage('', 'de');

        $pages = $this->pageFinder->findAssociatedForPage($de);

        $this->assertPageCount($pages, 2);
        $this->assertSame('root', $pages[$en->id]->type);
        $this->assertSame('root', $pages[$de->id]->type);
    }

    public function testFindsAllOnDifferentDomains(): void
    {
        $enRoot = $this->createRootPage('www.example.com', 'en');
        $deRoot = $this->createRootPage('www.example.org', 'de', true, $enRoot->id);

        $en = $this->createPage($enRoot->id);
        $de = $this->createPage($deRoot->id, $en->id);

        $pages = $this->pageFinder->findAssociatedForPage($de);

        $this->assertPageCount($pages, 2);
    }

    public function testIgnoresPagesInWrongRoot(): void
    {
        $enRoot = $this->createRootPage('www.example.com', 'en');
        $deRoot = $this->createRootPage('www.example.com', 'de', false);
        $frRoot = $this->createRootPage('www.example.org', 'fr');

        $en = $this->createPage($enRoot->id);
        $de = $this->createPage($deRoot->id, $en->id);
        $this->createPage($frRoot->id, $en->id);

        $pages = $this->pageFinder->findAssociatedForPage($de);

        $this->assertPageCount($pages, 2);
    }

    private function assertPageCount(array $pages, int $count): void
    {
        $this->assertCount($count, $pages);

        foreach ($pages as $instance) {
            $this->assertInstanceOf(PageModel::class, $instance);
        }
    }
}
