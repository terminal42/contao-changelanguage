<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\DataContainer;
use Contao\Input;
use Contao\PageModel;
use Haste\Dca\PaletteManipulator;

class PageInitializationListener
{
    /**
     * Register our own callbacks.
     */
    public function register(): void
    {
        $GLOBALS['TL_DCA']['tl_page']['config']['onload_callback'][] = function (DataContainer $dc): void {
            $this->onLoad($dc);
        };
    }

    /**
     * Load page data container configuration depending on current mode.
     */
    public function onLoad(DataContainer $dc): void
    {
        if ('page' !== Input::get('do')) {
            return;
        }

        switch (Input::get('act')) {
            case 'edit':
                $this->handleEditMode($dc);
                break;

            case 'editAll':
                $this->handleEditAllMode();
                break;
        }
    }

    private function handleEditMode(DataContainer $dc): void
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

    private function handleEditAllMode(): void
    {
        $this->addRootLanguageFields();
        $this->addRegularLanguageFields(
            array_diff(
                array_keys($GLOBALS['TL_DCA']['tl_page']['palettes']),
                ['__selector__', 'root', 'rootfallback', 'folder']
            )
        );
    }

    private function addRootLanguageFields(): void
    {
        PaletteManipulator::create()
            ->addField('languageRoot', 'fallback')
            ->applyToPalette('root', 'tl_page')
        ;

        if (isset($GLOBALS['TL_DCA']['tl_page']['palettes']['rootfallback'])) {
            PaletteManipulator::create()
                ->addField('languageRoot', 'fallback')
                ->applyToPalette('rootfallback', 'tl_page')
            ;
        }
    }

    /**
     * @param array|string $palettes
     * @param bool         $addLanguageMain
     */
    private function addRegularLanguageFields($palettes, $addLanguageMain = true): void
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
