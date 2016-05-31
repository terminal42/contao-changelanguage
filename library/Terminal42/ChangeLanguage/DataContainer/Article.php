<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\DataContainer;

use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Haste\Dca\PaletteManipulator;
use Terminal42\ChangeLanguage\Finder;

class Article
{
    /**
     * Inject fields if appropriate.
     *
     * @param DataContainer $dc
     */
    public function showSelectbox(DataContainer $dc)
    {
        $act = Input::get('act');

        if ('edit' === $act) {
            $objPage = Database::getInstance()
                ->prepare("SELECT p.* FROM tl_page p LEFT JOIN tl_article a ON a.pid=p.id WHERE a.id=? AND a.showTeaser='1'")
                ->limit(1)
                ->execute($dc->id)
            ;

            // Article does not have showTeaser enabled
            if (!$objPage->numRows) {
                return;
            }

            $arrMain = Finder::findMainLanguagePageForPage($objPage);

            if (false !== $arrMain) {
                $GLOBALS['TL_DCA']['tl_article']['fields']['title']['eval']['tl_class'] = 'w50';
                $GLOBALS['TL_DCA']['tl_article']['fields']['alias']['eval']['tl_class'] = 'clr w50';

                $this->addSelectboxToPalette();
            }

        } elseif ('editAll' === $act || 'overrideAll' === $act) {
            $this->addSelectboxToPalette();
        }
    }

    /**
     * Return all fallback articles for the current article (used as options_callback).
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getFallbackArticles(DataContainer $dc)
    {
        $arrPage = Finder::findMainLanguagePageForPage($dc->activeRecord->pid);

        if (false === $arrPage) {
            return array();
        }

        $arrArticles = array();
        $objArticles = Database::getInstance()
            ->prepare('SELECT id, title FROM tl_article WHERE pid=? AND inColumn=?')
            ->execute($arrPage['id'], $dc->activeRecord->inColumn)
        ;

        while ($objArticles->next()) {
            $arrArticles[$objArticles->id] = sprintf('%s [ID %s]', $objArticles->title, $objArticles->id);
        }

        return $arrArticles;
    }

    /**
     * Adds languageMain field to tl_article palette
     */
    private function addSelectboxToPalette()
    {
        PaletteManipulator::create()
            ->addField('languageMain', 'title', PaletteManipulator::POSITION_AFTER, 'title_legend')
            ->applyToPalette('default', 'tl_article')
        ;
    }
}
