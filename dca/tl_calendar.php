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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_calendar']['palettes']['default'] = str_replace('jumpTo;', 'jumpTo;{language_legend},language,master;', $GLOBALS['TL_DCA']['tl_calendar']['palettes']['default']);


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_calendar']['fields']['master'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_calendar']['master'],
	'exclude'			=> true,
	'inputType'			=> 'select',
	'options_callback'	=> array('tl_calendar_language', 'getCalendars'),
	'eval'				=> array('includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_calendar']['isMaster']),
	'sql'               => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_calendar']['fields']['language'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_calendar']['language'],
	'exclude'			=> true,
	'search'            => true,
	'filter'            => true,
	'inputType'			=> 'text',
	'eval'              => array('mandatory'=>true, 'rgxp'=>'language', 'maxlength'=>5, 'nospace'=>true, 'tl_class'=>'w50'),
	'sql'               => "varchar(5) NOT NULL default ''"
);


class tl_calendar_language extends Backend
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
