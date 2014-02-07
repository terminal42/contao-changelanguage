<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  terminal42 gmbh 2009-2013
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Return if the module is not active
 */
if (!in_array('calendar', \ModuleLoader::getActive()))
{
    return;
}


/**
 * Config
 */
$GLOBALS['TL_DCA']['tl_calendar_events']['config']['onload_callback'][] = array('tl_calendar_events_language', 'showSelectbox');


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['languageMain'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_calendar_events']['languageMain'],
	'exclude'                 => false,
	'inputType'               => 'select',
	'options_callback'        => array('tl_calendar_events_language', 'getMasterCalendar'),
	'eval'					  => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);


class tl_calendar_events_language extends Backend
{

	/**
	 * Get records from the master calendar
	 */
	public function getMasterCalendar(DataContainer $dc)
	{
		$arrItems = array();
		$objItems = $this->Database->prepare("SELECT * FROM tl_calendar_events WHERE pid=(SELECT tl_calendar.master FROM tl_calendar LEFT OUTER JOIN tl_calendar_events ON tl_calendar_events.pid=tl_calendar.id WHERE tl_calendar_events.id=?) ORDER BY startTime DESC")->execute($dc->id);

		while( $objItems->next() )
		{
			$arrItems[$objItems->id] = $objItems->title . ' [' . $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objItems->startTime) . ']';
		}

		return $arrItems;
	}


	/**
	 * Show the select menu only on slave calendars
	 */
	public function showSelectbox(DataContainer $dc)
	{
		if(\Input::get('act') == "edit")
		{
			$objArchive = $this->Database->prepare("SELECT tl_calendar.* FROM tl_calendar LEFT OUTER JOIN tl_calendar_events ON tl_calendar_events.pid=tl_calendar.id WHERE tl_calendar_events.id=?")
										 ->limit(1)
										 ->execute($dc->id);

			if($objArchive->numRows && $objArchive->master > 0)
			{
				$GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['default'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['default']);
				$GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['internal'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['internal']);
				$GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['external'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['external']);
				$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['title']['eval']['tl_class'] = 'w50';
				$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['alias']['eval']['tl_class'] = 'clr w50';
			}
		}
		else if(\Input::get('act') == "editAll")
		{
			$GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['regular'] = preg_replace('@([,|;]{1}language)([,|;]{1})@','$1,languageMain$2', $GLOBALS['TL_DCA']['tl_calendar_events']['palettes']['regular']);
		}
	}
}
