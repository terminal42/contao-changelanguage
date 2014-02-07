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
 * Config
 */
$GLOBALS['TL_DCA']['tl_news']['config']['onload_callback'][] = array('tl_news_changelanguage', 'showSelectbox');


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_news']['fields']['languageMain'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_news']['languageMain'],
	'exclude'                 => false,
	'inputType'               => 'select',
	'options_callback'        => array('tl_news_changelanguage', 'getMasterArchive'),
	'eval'					  => array('includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
	'sql'                     => "int(10) unsigned NOT NULL default '0'"
);


class tl_news_changelanguage extends Backend
{

	/**
	 * Get records from the master archive
	 *
	 * @param	DataContainer
	 * @return	array
	 * @link	http://www.contao.org/callbacks.html#options_callback
	 */
	public function getMasterArchive(DataContainer $dc)
	{
		$sameDay = $GLOBALS['TL_LANG']['tl_news']['sameDay'];
		$otherDay = $GLOBALS['TL_LANG']['tl_news']['otherDay'];

		$arrItems = array($sameDay => array(), $otherDay => array());
		$objItems = $this->Database->prepare("SELECT * FROM tl_news WHERE pid=(SELECT tl_news_archive.master FROM tl_news_archive LEFT OUTER JOIN tl_news ON tl_news.pid=tl_news_archive.id WHERE tl_news.id=?) ORDER BY date DESC, time DESC")->execute($dc->id);

		$dayBegin = strtotime('0:00', $dc->activeRecord->date);

		while( $objItems->next() )
		{
			if (strtotime('0:00', $objItems->date) == $dayBegin)
			{
				$arrItems[$sameDay][$objItems->id] = $objItems->headline . ' [' . $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objItems->time) . ']';
			}
			else
			{
				$arrItems[$otherDay][$objItems->id] = $objItems->headline . ' [' . $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objItems->time) . ']';
			}
		}

		return $arrItems;
	}


	/**
	 * Show the select menu only on slave archives
	 *
	 * @param	DataContainer
	 * @return	void
	 * @link	http://www.contao.org/callbacks.html#onload_callback
	 */
	public function showSelectbox(DataContainer $dc)
	{
		if(\Input::get('act') == "edit")
		{
			$objArchive = $this->Database->prepare("SELECT tl_news_archive.* FROM tl_news_archive LEFT OUTER JOIN tl_news ON tl_news.pid=tl_news_archive.id WHERE tl_news.id=?")
										 ->limit(1)
										 ->execute($dc->id);

			if($objArchive->numRows && $objArchive->master > 0)
			{
				$GLOBALS['TL_DCA']['tl_news']['palettes']['default'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_news']['palettes']['default']);
				$GLOBALS['TL_DCA']['tl_news']['palettes']['internal'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_news']['palettes']['internal']);
				$GLOBALS['TL_DCA']['tl_news']['palettes']['external'] = preg_replace('@([,|;])(alias[,|;])@','$1languageMain,$2', $GLOBALS['TL_DCA']['tl_news']['palettes']['external']);
				$GLOBALS['TL_DCA']['tl_news']['fields']['headline']['eval']['tl_class'] = 'w50';
				$GLOBALS['TL_DCA']['tl_news']['fields']['alias']['eval']['tl_class'] = 'clr w50';
			}
		}
		else if(\Input::get('act') == "editAll")
		{
			$GLOBALS['TL_DCA']['tl_news']['palettes']['regular'] = preg_replace('@([,|;]{1}language)([,|;]{1})@','$1,languageMain$2', $GLOBALS['TL_DCA']['tl_news']['palettes']['regular']);
		}
	}
}
