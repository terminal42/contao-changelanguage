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

use Contao\Backend;

class Article extends Backend
{

    /**
     * Initialize the class
     */
    public function __construct()
    {
        parent::__construct();

        $this->import('ChangeLanguage');
    }


    /**
     * Inject fields if appropriate.
     *
     * @access public
     * @return void
     */
    public function showSelectbox($dc)
    {
        if (\Input::get('act') == 'edit')
        {
            $objPage = $this->Database->prepare("SELECT p.* FROM tl_page p LEFT JOIN tl_article a ON a.pid=p.id WHERE a.id=? GROUP BY a.pid")->execute($dc->id);
            $arrMain = $this->ChangeLanguage->findMainLanguagePageForPage($objPage);

            if ($arrMain !== false)
            {
                $GLOBALS['TL_DCA']['tl_article']['fields']['title']['eval']['tl_class'] = 'w50';
                $GLOBALS['TL_DCA']['tl_article']['fields']['alias']['eval']['tl_class'] = 'clr w50';
                $GLOBALS['TL_DCA']['tl_article']['palettes']['default'] = preg_replace('@([,|;]title)([,|;])@','$1,languageMain$2', $GLOBALS['TL_DCA']['tl_article']['palettes']['default']);
            }
        }
        elseif (\Input::get('act') == 'editAll')
        {
            $GLOBALS['TL_DCA']['tl_page']['palettes']['default'] = preg_replace('@([,|;]title)([,|;])@','$1,languageMain$2', $GLOBALS['TL_DCA']['tl_page']['palettes']['default']);
        }
    }


    /**
     * Return all fallback pages for the current page (used as options_callback).
     *
     * @access public
     * @return array
     */
    public function getFallbackArticles($dc)
    {
        $arrPage = $this->ChangeLanguage->findMainLanguagePageForPage($dc->activeRecord->pid);

        if ($arrPage === false)
        {
            return array();
        }

        $arrArticles = array();
        $objArticles = $this->Database->prepare("SELECT id, title FROM tl_article WHERE pid=? AND inColumn=?")->execute($arrPage['id'], $dc->activeRecord->inColumn);

        while ($objArticles->next())
        {
            $arrArticles[$objArticles->id] = $objArticles->title . ' [ID ' . $objArticles->id . ']';
        }

        return $arrArticles;
    }
}

