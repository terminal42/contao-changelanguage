<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\DataContainer;

use Contao\Backend;
use Contao\DataContainer;

class Faq extends Backend
{

    /**
     * Get records from the master category
     */
    public function getMasterCategory(DataContainer $dc)
    {
        $arrItems = array();
        $objItems = $this->Database->prepare("SELECT * FROM tl_faq WHERE pid=(SELECT tl_faq_category.master FROM tl_faq_category LEFT OUTER JOIN tl_faq ON tl_faq.pid=tl_faq_category.id WHERE tl_faq.id=?) ORDER BY sorting")->execute($dc->id);

        while( $objItems->next() )
        {
            $arrItems[$objItems->id] = $objItems->question . ' [ID ' . $objItems->id . ']';
        }

        return $arrItems;
    }


    /**
     * Show the select menu only on slave categories
     */
    public function showSelectbox(DataContainer $dc)
    {
        if(\Input::get('act') == "edit")
        {
            $objArchive = $this->Database->prepare("SELECT tl_faq_category.* FROM tl_faq_category LEFT OUTER JOIN tl_faq ON tl_faq.pid=tl_faq_category.id WHERE tl_faq.id=?")
                                         ->limit(1)
                                         ->execute($dc->id);

            if($objArchive->numRows && $objArchive->master > 0)
            {
                $GLOBALS['TL_DCA']['tl_faq']['palettes']['default'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_faq']['palettes']['default']);
                $GLOBALS['TL_DCA']['tl_faq']['palettes']['internal'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_faq']['palettes']['internal']);
                $GLOBALS['TL_DCA']['tl_faq']['palettes']['external'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_faq']['palettes']['external']);
                $GLOBALS['TL_DCA']['tl_faq']['fields']['question']['eval']['tl_class'] = 'w50';
                $GLOBALS['TL_DCA']['tl_faq']['fields']['alias']['eval']['tl_class'] = 'clr w50';
            }
        }
        else if(\Input::get('act') == "editAll")
        {
            $GLOBALS['TL_DCA']['tl_faq']['palettes']['regular'] = preg_replace('@([,|;]{1}language)([,|;]{1})@','$1,languageMain$2', $GLOBALS['TL_DCA']['tl_faq']['palettes']['regular']);
        }
    }
}
