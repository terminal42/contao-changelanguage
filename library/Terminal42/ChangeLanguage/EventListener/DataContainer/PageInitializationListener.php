<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2019, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\DataContainer;
use Contao\PageModel;
use Haste\Dca\PaletteManipulator;

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
                ['__selector__', 'root', 'folder']
            )
        );
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
