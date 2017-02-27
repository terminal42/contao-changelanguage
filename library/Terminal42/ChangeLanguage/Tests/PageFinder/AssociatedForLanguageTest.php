<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2017, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\Tests\PageFinder;

use Contao\PageModel;
use Terminal42\ChangeLanguage\PageFinder;
use Terminal42\ChangeLanguage\Tests\ContaoTestCase;

class AssociatedForLanguageTest extends ContaoTestCase
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
        $enRoot = $this->createRootPage('en', true);
        $deRoot = $this->createRootPage('de', false);

        $en = $this->createPage($enRoot);

        $dePage = new PageModel();
        $dePage->id = $this->createPage($deRoot, $en);
        $dePage->languageMain = $en;

        $page = $this->pageFinder->findAssociatedForLanguage($dePage, 'en');

        $this->assertInstanceOf('Contao\PageModel', $page);
        $this->assertSame($en, $page->id);
    }

    public function testReturnsRootWhenNoMatch()
    {
        $enRoot = $this->createRootPage('en', true);
        $deRoot = $this->createRootPage('de', false);

        $pageModel = new PageModel();
        $pageModel->id = $this->createPage($deRoot);
        $pageModel->pid = $deRoot;
        $pageModel->language = 'de';
        $pageModel->dns = '';

        $page = $this->pageFinder->findAssociatedForLanguage($pageModel, 'en');

        $this->assertInstanceOf('Contao\PageModel', $page);
        $this->assertSame($enRoot, $page->id);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowsExceptionWhenLanguageDoesNotExist()
    {
        $enRoot = $this->createRootPage('en', true);
        $deRoot = $this->createRootPage('de', false);

        $en = $this->createPage($enRoot);

        $dePage = new PageModel();
        $dePage->id = $this->createPage($deRoot, $en);
        $dePage->pid = $deRoot;
        $dePage->languageMain = $en;

        $this->pageFinder->findAssociatedForLanguage($dePage, 'fr');
    }

    private function createPage($pid = 0, $languageMain = 0, $published = true)
    {
        $published = $published ? '1' : '';

        return $this->query("
            INSERT INTO tl_page 
            (pid, type, languageMain, published) 
            VALUES 
            ($pid, 'regular', $languageMain, '$published')
        ");
    }

    private function createRootPage($language, $fallback, $published = true)
    {
        $published = $published ? '1' : '';
        $fallback = $fallback ? '1' : '';

        return $this->query("
            INSERT INTO tl_page 
            (type, language, fallback, published)
            VALUES 
            ('root', '$language', '$fallback', '$published')
        ");
    }
}
