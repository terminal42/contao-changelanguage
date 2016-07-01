<?php
/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\Tests\PageFinder;

use Contao\Model\Registry;
use Contao\PageModel;
use Terminal42\ChangeLanguage\PageFinder;
use Terminal42\ChangeLanguage\Tests\ContaoTestCase;

class RootPagesTest extends ContaoTestCase
{
    /**
     * @var PageFinder
     */
    private $pageFinder;

    public function setUp()
    {
        parent::setUp();

        $this->pageFinder = new PageFinder();
    }

    public function testFindsOneRoot()
    {
        $this->createRootPage('', 'en');

        $pageModel = new PageModel();
        $pageModel->id = 1;
        $pageModel->domain = '';

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 1);
        $this->assertEquals('root', $roots[1]->type);
        $this->assertEquals('', $roots[1]->dns);
        $this->assertEquals('en', $roots[1]->language);
    }

    public function testFindRootsWithSameDns()
    {
        $this->createRootPage('', 'en');
        $this->createRootPage('', 'de', false);

        $pageModel = new PageModel();
        $pageModel->id = 1;
        $pageModel->domain = '';

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 2);
    }

    public function testFindsMasterRoot()
    {
        $master = $this->createRootPage('foo.com', 'en');
        $this->createRootPage('bar.com', 'de', true, $master);

        $pageModel = new PageModel();
        $pageModel->id = 1;
        $pageModel->domain = 'foo.com';

        $roots = $this->pageFinder->findRootPagesForPage($pageModel);

        $this->assertPageCount($roots, 2);
    }

    public function testFindsMasterAndNonFallbacks()
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

    public function testFindsMasterFromSlave()
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

    public function testIgnoresNonRelated()
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
        $this->assertEquals('foo.com', $roots[1]->dns);
    }

    public function testIgnoresUnpublished()
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

    public function testNonFallbackMaster()
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

    public function testKeyEqualsPageId()
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
            $this->assertEquals($page->id, $id);
        }
    }

    private function createRootPage($dns, $language, $fallback = true, $master = 0, $published = true)
    {
        $fallback  = $fallback ? '1' : '';
        $published = $published ? '1' : '';

        return $this->query("
            INSERT INTO tl_page 
            (type, title, dns, language, fallback, languageRoot, published) 
            VALUES 
            ('root', 'foobar', '$dns', '$language', '$fallback', $master, '$published')
        ");
    }

    private function assertPageCount($roots, $count)
    {
        $this->assertCount($count, $roots);

        foreach ($roots as $instance) {
            $this->assertInstanceOf('\Contao\PageModel', $instance);
        }
    }
}
