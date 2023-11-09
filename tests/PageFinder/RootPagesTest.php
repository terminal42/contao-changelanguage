<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Tests\PageFinder;

use Contao\PageModel;
use Contao\TestCase\ContaoDatabaseTrait;
use Terminal42\ChangeLanguage\PageFinder;
use Terminal42\ChangeLanguage\Tests\ContaoTestCase;

class RootPagesTest extends ContaoTestCase
{
    use ContaoDatabaseTrait;

    private PageFinder $pageFinder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageFinder = new PageFinder();
    }

    public function testFindsOneRoot(): void
    {
        $pageModel = $this->createRootPage('', 'en');

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 1);
        $this->assertSame('root', $roots[1]->type);
        $this->assertSame('', $roots[1]->dns);
        $this->assertSame('en', $roots[1]->language);
    }

    public function testFindRootsWithSameDns(): void
    {
        $pageModel = $this->createRootPage('', 'en');
        $this->createRootPage('', 'de', false);

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 2);
    }

    public function testFindsMasterRoot(): void
    {
        $pageModel = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('bar.com', 'de', true, $pageModel->id);

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 2);
    }

    public function testFindsMasterAndNonFallbacks(): void
    {
        $pageModel = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr', true, $pageModel->id);
        $this->createRootPage('bar.com', 'it', false);

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 4);
    }

    public function testFindsMasterFromSlave(): void
    {
        $master = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr', true, $master->id);
        $pageModel = $this->createRootPage('bar.com', 'it', false);

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 4);
    }

    public function testFindsMasterFromMultipleDomains(): void
    {
        $master = $this->createRootPage('en.com', 'en');
        $this->createRootPage('de.com', 'de', true, $master->id);
        $pid = $this->createRootPage('fr.com', 'fr', true, $master->id);

        $pageModel = $this->createPage($pid->id);

        $roots = $this->pageFinder->findRootPagesForPage($pageModel, false, false);

        $this->assertPageCount($roots, 3);
    }

    public function testIgnoresNonRelated(): void
    {
        $pageModel = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr');
        $this->createRootPage('bar.com', 'it', false);

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 2);
        $this->assertSame('foo.com', $roots[1]->dns);
    }

    public function testIgnoresUnpublished(): void
    {
        $pageModel = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr', true, $pageModel->id);
        $this->createRootPage('bar.com', 'it', false, 0, false);

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 3);
    }

    public function testIncludesUnpublishedWhenEnabled(): void
    {
        $pageModel = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr', true, $pageModel->id);
        $this->createRootPage('bar.com', 'it', false, 0, false);

        $roots = $this->pageFinder->findRootPagesForPage($pageModel, false, false);

        $this->assertPageCount($roots, 4);
    }

    public function testNonFallbackMaster(): void
    {
        $pageModel = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr', true, 0);
        $this->createRootPage('bar.com', 'it', false, $pageModel->id);

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 2);
    }

    public function testKeyEqualsPageId(): void
    {
        $master = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr', true, $master->id);
        $this->createRootPage('bar.com', 'it', false);

        $roots = $this->pageFinder->findRootPagesForPage($master);

        $this->assertPageCount($roots, 4);

        foreach ($roots as $id => $page) {
            $this->assertSame((int) $page->id, $id);
        }
    }

    private function assertPageCount(array $roots, int $count): void
    {
        $this->assertCount($count, $roots);

        foreach ($roots as $instance) {
            $this->assertInstanceOf(PageModel::class, $instance);
        }
    }
}
