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

class FaqCategory extends Backend
{

    /**
     * Get an array of possible categories
     */
    public function getCategories(DataContainer $dc)
    {
        $arrCalendars = array();
        $objCategories = $this->Database->prepare("SELECT * FROM tl_faq_category WHERE language!=? AND id!=? AND master=0 ORDER BY title")->execute($dc->activeRecord->language, $dc->id);

        while( $objCategories->next() )
        {
            $arrCalendars[$objCategories->id] = sprintf($GLOBALS['TL_LANG']['tl_faq_category']['isSlave'], $objCategories->title);
        }

        return $arrCalendars;
    }
}
