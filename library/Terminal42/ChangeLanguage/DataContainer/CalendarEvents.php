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

use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\Input;

class CalendarEvents
{
    /**
     * Get records from the master calendar
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getMasterCalendar(DataContainer $dc)
    {
        $arrItems = array();
        $objItems = Database::getInstance()
            ->prepare('
                SELECT * 
                FROM tl_calendar_events 
                WHERE pid=(
                    SELECT tl_calendar.master 
                    FROM tl_calendar 
                    LEFT OUTER JOIN tl_calendar_events ON tl_calendar_events.pid=tl_calendar.id 
                    WHERE tl_calendar_events.id=?
                ) 
                ORDER BY startTime DESC
            ')
            ->execute($dc->id)
        ;

        while ($objItems->next()) {
            $arrItems[$objItems->id] = sprintf(
                '%s [%s]',
                $objItems->title,
                Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $objItems->startTime)
            );
        }

        return $arrItems;
    }

    /**
     * Show the select menu only on slave calendars
     *
     * @param DataContainer $dc
     */
    public function showSelectbox(DataContainer $dc)
    {
        if ('edit' === \Input::get('act')) {
            $objArchive = Database::getInstance()
                ->prepare('
                    SELECT tl_calendar.* 
                    FROM tl_calendar 
                    LEFT OUTER JOIN tl_calendar_events ON tl_calendar_events.pid=tl_calendar.id 
                    WHERE tl_calendar_events.id=?
                ')
                ->limit(1)
                ->execute($dc->id)
            ;

            if ($objArchive->numRows && $objArchive->master > 0) {
                $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['default'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['default']);
                $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['internal'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['internal']);
                $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['external'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['external']);
                $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['title']['eval']['tl_class'] = 'w50';
                $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['alias']['eval']['tl_class'] = 'clr w50';
            }

        } elseif('editAll' === Input::get('act')) {
            $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['regular'] = preg_replace('@([,|;]{1}language)([,|;]{1})@','$1,languageMain$2', $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['regular']);
        }
    }
}
