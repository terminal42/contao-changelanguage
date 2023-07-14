<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\Tests\PageFinder;

use Contao\PageModel;
use Terminal42\ChangeLanguage\PageFinder;
use Terminal42\ChangeLanguage\Tests\ContaoTestCase;

class RootPagesTest extends ContaoTestCase
{
    private PageFinder $pageFinder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pageFinder = new PageFinder();
    }

    public function testFindsOneRoot(): void
    {
        $this->createRootPage('', 'en');

        $pageModel = new PageModel();
        $pageModel->id = 1;
        $pageModel->domain = '';

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 1);
        $this->assertSame('root', $roots[1]->type);
        $this->assertSame('', $roots[1]->dns);
        $this->assertSame('en', $roots[1]->language);
    }

    public function testFindRootsWithSameDns(): void
    {
        $this->createRootPage('', 'en');
        $this->createRootPage('', 'de', false);

        $pageModel = new PageModel();
        $pageModel->id = 1;
        $pageModel->domain = '';

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 2);
    }

    public function testFindsMasterRoot(): void
    {
        $master = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('bar.com', 'de', true, $master);

        $pageModel = new PageModel();
        $pageModel->id = 1;
        $pageModel->domain = 'foo.com';

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 2);
    }

    public function testFindsMasterAndNonFallbacks(): void
    {
        $master = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr', true, $master);
        $this->createRootPage('bar.com', 'it', false);

        $pageModel = new PageModel();
        $pageModel->id = $master;
        $pageModel->domain = 'foo.com';

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 4);
    }

    public function testFindsMasterFromSlave(): void
    {
        $master = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr', true, $master);
        $search = $this->createRootPage('bar.com', 'it', false);

        $pageModel = new PageModel();
        $pageModel->id = $search;
        $pageModel->domain = 'bar.com';

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 4);
    }

    public function testFindsMasterFromMultipleDomains(): void
    {
        $master = $this->createRootPage('en.com', 'en');
        $this->createRootPage('de.com', 'de', true, $master);
        $pid = $this->createRootPage('fr.com', 'fr', true, $master);

        $search = $this->query("
            INSERT INTO tl_page
            (type, pid, published)
            VALUES
            ('regular', '$pid', '1')
        ");

        $pageModel = new PageModel();
        $pageModel->id = $search;
        $pageModel->pid = $pid;

        $roots = $this->pageFinder->findRootPagesForPage($pageModel, false, false);

        $this->assertPageCount($roots, 3);
    }

    public function testIgnoresNonRelated(): void
    {
        $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr');
        $this->createRootPage('bar.com', 'it', false);

        $pageModel = new PageModel();
        $pageModel->id = 1;
        $pageModel->domain = 'foo.com';

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 2);
        $this->assertSame('foo.com', $roots[1]->dns);
    }

    public function testIgnoresUnpublished(): void
    {
        $master = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr', true, $master);
        $this->createRootPage('bar.com', 'it', false, 0, false);

        $pageModel = new PageModel();
        $pageModel->id = 1;
        $pageModel->domain = 'foo.com';

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 3);
    }

    public function testIncludesUnpublishedWhenEnabled(): void
    {
        $master = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr', true, $master);
        $this->createRootPage('bar.com', 'it', false, 0, false);

        $pageModel = new PageModel();
        $pageModel->id = 1;
        $pageModel->domain = 'foo.com';

        $roots = $this->pageFinder->findRootPagesForPage($pageModel, false, false);

        $this->assertPageCount($roots, 4);
    }

    public function testNonFallbackMaster(): void
    {
        $master = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr', true, 0);
        $this->createRootPage('bar.com', 'it', false, $master);

        $pageModel = new PageModel();
        $pageModel->id = 1;
        $pageModel->domain = 'foo.com';

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 2);
    }

    public function testKeyEqualsPageId(): void
    {
        $master = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('foo.com', 'de', false);
        $this->createRootPage('bar.com', 'fr', true, $master);
        $this->createRootPage('bar.com', 'it', false);

        $pageModel = new PageModel();
        $pageModel->id = $master;
        $pageModel->domain = 'foo.com';

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 4);

        foreach ($roots as $id => $page) {
            $this->assertSame((int) $page->id, $id);
        }
    }

    private function createRootPage($dns, $language, $fallback = true, $master = 0, $published = true)
    {
        $fallback = $fallback ? '1' : '';
        $published = $published ? '1' : '';

        return $this->query("
            INSERT INTO tl_page
            (type, title, dns, language, fallback, languageRoot, published)
            VALUES
            ('root', 'foobar', '$dns', '$language', '$fallback', $master, '$published')
        ");
    }

    private function assertPageCount($roots, $count): void
    {
        $this->assertCount($count, $roots);

        foreach ($roots as $instance) {
            $this->assertInstanceOf(PageModel::class, $instance);
        }
    }
}
