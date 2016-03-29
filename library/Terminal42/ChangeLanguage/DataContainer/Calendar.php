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

class Calendar extends Backend
{

    /**
     * Get an array of possible calendars
     */
    public function getCalendars(DataContainer $dc)
    {
        $arrCalendars = array();
        $objCalendars = $this->Database->prepare("SELECT * FROM tl_calendar WHERE language!=? AND id!=? AND master=0 ORDER BY title")->execute($dc->activeRecord->language, $dc->id);

        while( $objCalendars->next() )
        {
            $arrCalendars[$objCalendars->id] = sprintf($GLOBALS['TL_LANG']['tl_calendar']['isSlave'], $objCalendars->title);
        }

        return $arrCalendars;
    }
}
