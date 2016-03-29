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
        if ('edit' === Input::get('act')) {
            $objPage = Database::getInstance()
                ->prepare('SELECT p.* FROM tl_page p LEFT JOIN tl_article a ON a.pid=p.id WHERE a.id=? GROUP BY a.pid')
                ->execute($dc->id)
            ;

            $arrMain = Finder::findMainLanguagePageForPage($objPage);

            if ($arrMain !== false) {
                $GLOBALS['TL_DCA']['tl_article']['fields']['title']['eval']['tl_class'] = 'w50';
                $GLOBALS['TL_DCA']['tl_article']['fields']['alias']['eval']['tl_class'] = 'clr w50';
                $GLOBALS['TL_DCA']['tl_article']['palettes']['default'] = preg_replace('@([,|;]title)([,|;])@','$1,languageMain$2', $GLOBALS['TL_DCA']['tl_article']['palettes']['default']);
            }

        } elseif ('editAll' === Input::get('act')) {
            $GLOBALS['TL_DCA']['tl_page']['palettes']['default'] = preg_replace('@([,|;]title)([,|;])@','$1,languageMain$2', $GLOBALS['TL_DCA']['tl_page']['palettes']['default']);
        }
    }

    /**
     * Return all fallback pages for the current page (used as options_callback).
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getFallbackArticles(DataContainer $dc)
    {
        $arrPage = Finder::findMainLanguagePageForPage($dc->activeRecord->pid);

        if ($arrPage === false) {
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
}
