<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2017, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\Database;
use Contao\DataContainer;
use Contao\PageModel;
use Haste\Dca\PaletteManipulator;
use Terminal42\ChangeLanguage\PageFinder;

class PageInitializationListener
{
    /**
     * Register our own callbacks.
     */
    public function register()
    {
        $GLOBALS['TL_DCA']['tl_page']['config']['onload_callback'][] = function (DataContainer $dc) {
            $this->onLoad($dc);
        };
    }

    /**
     * Load page data container configuration depending on current mode.
     *
     * @param DataContainer $dc
     */
    public function onLoad(DataContainer $dc)
    {
        if ('page' !== \Input::get('do')) {
            return;
        }

        switch (\Input::get('act')) {
            case 'edit':
                $this->handleEditMode($dc);
                break;
            case 'editAll':
                $this->handleEditAllMode();
                break;
            // Page picker popup
            case 'show':
                if ('languageMain' === \Input::get('field')) {
                    $this->setRootNodesForPage($dc->id);
                }
                break;
        }
    }

    private function handleEditMode(DataContainer $dc)
    {
        $page = PageModel::findByPk($dc->id);

        if (null === $page) {
            return;
        }

        if ('root' === $page->type) {
            if ($page->fallback) {
                $this->addRootLanguageFields();
            }

            return;
        }

        $root = PageModel::findByPk($page->loadDetails()->rootId);
        $addLanguageMain = true;

        if ($root->fallback && (!$root->languageRoot || null === PageModel::findByPk($root->languageRoot))) {
            $addLanguageMain = false;
        }

        $this->addRegularLanguageFields($page->type, $addLanguageMain);
    }

    private function handleEditAllMode()
    {
        $this->addRootLanguageFields();
        $this->addRegularLanguageFields(
            array_diff(
                array_keys($GLOBALS['TL_DCA']['tl_page']['palettes']),
                ['__selector__', 'root']
            )
        );
    }

    /**
     * Limits the available pages in page picker to the fallback page tree.
     *
     * @param int $pageId
     */
    private function setRootNodesForPage($pageId)
    {
        $page = PageModel::findWithDetails($pageId);
        $root = PageModel::findByPk($page->rootId);

        if ($root->fallback
            && (!$root->languageRoot || ($languageRoot = PageModel::findByPk($root->languageRoot)) === null)
        ) {
            return;
        }

        $pageFinder = new PageFinder();
        $masterRoot = $pageFinder->findMasterRootForPage($page);

        if (null !== $masterRoot) {
            $GLOBALS['TL_DCA']['tl_page']['fields']['languageMain']['eval']['rootNodes'] = Database::getInstance()
                ->prepare('SELECT id FROM tl_page WHERE pid=? ORDER BY sorting')
                ->execute($masterRoot->id)
                ->fetchEach('id')
            ;
        }
    }

    private function addRootLanguageFields()
    {
        PaletteManipulator::create()
            ->addField('languageRoot', 'fallback')
            ->applyToPalette('root', 'tl_page')
        ;
    }

    /**
     * @param array|string $palettes
     * @param bool         $addLanguageMain
     */
    private function addRegularLanguageFields($palettes, $addLanguageMain = true)
    {
        $pm = PaletteManipulator::create()
            ->addLegend('language_legend', 'title_legend', PaletteManipulator::POSITION_AFTER, true)
            ->addField('languageQuery', 'language_legend', PaletteManipulator::POSITION_APPEND)
        ;

        if ($addLanguageMain) {
            $pm->addField('languageMain', 'language_legend', PaletteManipulator::POSITION_PREPEND);
        }

        foreach ((array) $palettes as $palette) {
            $pm->applyToPalette($palette, 'tl_page');
        }
    }
}
