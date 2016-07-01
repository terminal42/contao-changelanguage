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

use Contao\PageModel;
use Terminal42\ChangeLanguage\Navigation\PageFinder;
use Terminal42\ChangeLanguage\Tests\ContaoTestCase;

class AssociatedPagesTest extends ContaoTestCase
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
        $pageModel = new PageModel();
        $pageModel->id = $this->createPage();
        $pageModel->rootIsFallback = true;

        $this->createPage($pageModel->id);
        $this->createPage($pageModel->id);

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 3);
    }

    public function testFindsAllFromRelated()
    {
        $fallback = $this->createPage();

        $pageModel = new PageModel();
        $pageModel->id = $this->createPage($fallback);
        $pageModel->rootIsFallback = false;
        $pageModel->languageMain = $fallback;

        $this->createPage($fallback);

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 3);
    }

    public function testIgnoresEmptyMain()
    {
        $this->createPage();
        $this->createPage();

        $pageModel = new PageModel();
        $pageModel->id = $this->createPage();
        $pageModel->rootIsFallback = false;
        $pageModel->languageMain = 0;

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 1);
    }

    public function testIgnoresLanguageMainOnFallback()
    {
        $fallback = $this->createPage();

        $pageModel = new PageModel();
        $pageModel->id = $this->createPage($fallback);
        $pageModel->rootIsFallback = true;
        $pageModel->languageMain = $fallback;

        $this->createPage($fallback);

        $pages = $this->pageFinder->findAssociatedForPage($pageModel);

        $this->assertPageCount($pages, 1);
    }

    private function createPage($languageMain = 0, $published = true)
    {
        $published = $published ? '1' : '';

        return $this->query("
            INSERT INTO tl_page 
            (type, title, languageMain, published) 
            VALUES 
            ('regular', 'foobar', $languageMain, '$published')
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
