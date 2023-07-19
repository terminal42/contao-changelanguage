<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Tests\PageFinder;

use Contao\PageModel;
use Terminal42\ChangeLanguage\PageFinder;
use Terminal42\ChangeLanguage\Tests\ContaoTestCase;

class AssociatedForLanguageTest extends ContaoTestCase
{
    private PageFinder $pageFinder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageFinder = new PageFinder();
    }

    public function testFindsOnePage(): void
    {
        $enRoot = $this->createRootPage('', 'en', true);
        $deRoot = $this->createRootPage('', 'de', false);

        $enPage = $this->createPage($enRoot->id);
        $dePage = $this->createPage($deRoot->id, $enPage->id);

        $page = $this->pageFinder->findAssociatedForLanguage($dePage, 'en');

        $this->assertInstanceOf(PageModel::class, $page);
        $this->assertSame($enPage->id, $page->id);
    }

    public function testReturnsRootWhenNoMatch(): void
    {
        $enRoot = $this->createRootPage('', 'en', true);
        $deRoot = $this->createRootPage('', 'de', false);

        $pageModel = $this->createPage($deRoot->id);
        $pageModel->language = 'de';

        $page = $this->pageFinder->findAssociatedForLanguage($pageModel, 'en');

        $this->assertInstanceOf(PageModel::class, $page);
        $this->assertSame($enRoot->id, $page->id);
    }

    public function testThrowsExceptionWhenLanguageDoesNotExist(): void
    {
        $this->expectException('InvalidArgumentException');

        $enRoot = $this->createRootPage('', 'en', true);
        $deRoot = $this->createRootPage('', 'de', false);

        $enPage = $this->createPage($enRoot->id);
        $dePage = $this->createPage($deRoot->id, $enPage->id);

        $this->pageFinder->findAssociatedForLanguage($dePage, 'fr');
    }
}
