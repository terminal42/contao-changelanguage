<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2019, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\Tests\PageFinder;

use Contao\PageModel;
use Terminal42\ChangeLanguage\PageFinder;
use Terminal42\ChangeLanguage\Tests\ContaoTestCase;

class AssociatedForPageTest extends ContaoTestCase
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

    public function testFindsOnePage()
    {
        $pageModel = new PageModel();
        $pageModel->id = $this->createPage();

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 1);
    }

    public function testFindsAllFromFallback()
    {
        $enRoot = $this->createRootPage('en');
        $deRoot = $this->createRootPage('de', '');
        $frRoot = $this->createRootPage('fr', '');

        $pageModel = new PageModel();
        $pageModel->id = $this->createPage(0, $enRoot);
        $pageModel->pid = $enRoot;

        $this->createPage($pageModel->id, $frRoot);
        $this->createPage($pageModel->id, $deRoot);

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 3);
    }

    public function testFindsAllFromRelated()
    {
        $enRoot = $this->createRootPage('en');
        $deRoot = $this->createRootPage('de', '');
        $frRoot = $this->createRootPage('fr', '');

        $fallback = $this->createPage(0, $enRoot);

        $pageModel = new PageModel();
        $pageModel->id = $this->createPage($fallback, $deRoot);
        $pageModel->pid = $deRoot;
        $pageModel->languageMain = $fallback;

        $this->createPage($fallback, $frRoot);

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 3);
    }

    public function testIgnoresEmptyMain()
    {
        $this->createPage();
        $this->createPage();

        $pageModel = new PageModel();
        $pageModel->id = $this->createPage();
        $pageModel->languageMain = 0;

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 1);
    }

    public function testIgnoresLanguageMainOnFallback()
    {
        $enRoot = $this->createRootPage('en');
        $deRoot = $this->createRootPage('de', '');

        $fallback = $this->createPage(0, $deRoot);

        $pageModel = new PageModel();
        $pageModel->id = $this->createPage($fallback, $enRoot);
        $pageModel->pid = $enRoot;
        $pageModel->languageMain = $fallback;

        $this->createPage($fallback);

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 1);
    }

    public function testFindsRootsForRootPage()
    {
        $en = $this->createRootPage('en');
        $de = $this->createRootPage('de');

        $pageModel = new PageModel();
        $pageModel->id = $de;
        $pageModel->type = 'root';
        $pageModel->dns = '';

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 2);
        $this->assertSame('root', $pages[$en]->type);
        $this->assertSame('root', $pages[$de]->type);
    }

    public function testFindsAllOnDifferentDomains()
    {
        $enRoot = $this->createRootPage('en', '1', 'www.example.com');
        $deRoot = $this->createRootPage('de', '1', 'www.example.org', $enRoot);

        $en = $this->createPage(0, $enRoot);
        $de = $this->createPage($en, $deRoot);

        $pageModel = new PageModel();
        $pageModel->id = $de;
        $pageModel->pid = $deRoot;
        $pageModel->languageMain = $en;
        $pageModel->type = 'regular';

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 2);
    }

    public function testIgnoresPagesInWrongRoot()
    {
        $enRoot = $this->createRootPage('en', '1', 'www.example.com');
        $deRoot = $this->createRootPage('de', '', 'www.example.com');
        $frRoot = $this->createRootPage('fr', '1', 'www.example.org');

        $en = $this->createPage(0, $enRoot);
        $de = $this->createPage($en, $deRoot);
        $this->createPage($en, $frRoot);

        $pageModel = new PageModel();
        $pageModel->id = $de;
        $pageModel->pid = $deRoot;
        $pageModel->languageMain = $en;
        $pageModel->type = 'regular';

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 2);
    }

    private function createPage($languageMain = 0, $pid = 0)
    {
        return $this->query("
            INSERT INTO tl_page 
            (type, pid, languageMain, published) 
            VALUES 
            ('regular', $pid, $languageMain, '1')
        ");
    }

    private function createRootPage($language, $fallback = '1', $dns = '', $languageRoot = 0)
    {
        return $this->query("
            INSERT INTO tl_page 
                (type, dns, fallback, language, languageRoot, published) 
            VALUES 
                ('root', '$dns', '$fallback', '$language', $languageRoot, '1')
        ");
    }

    private function assertPageCount($pages, $count)
    {
        $this->assertCount($count, $pages);

        foreach ($pages as $instance) {
            $this->assertInstanceOf('\Contao\PageModel', $instance);
        }
    }
}
