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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_faq_category']['palettes']['default'] = str_replace('jumpTo;', 'jumpTo;{language_legend},language,master;', $GLOBALS['TL_DCA']['tl_faq_category']['palettes']['default']);


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_faq_category']['fields']['master'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_faq_category']['master'],
	'exclude'			=> true,
	'inputType'			=> 'select',
	'options_callback'	=> array('tl_faq_category_language', 'getCategories'),
	'eval'				=> array('includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_faq_category']['isMaster']),
	'sql'               => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_faq_category']['fields']['language'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_faq_category']['language'],
	'exclude'			=> true,
	'search'            => true,
	'filter'            => true,
	'inputType'			=> 'text',
	'eval'              => array('mandatory'=>true, 'rgxp'=>'language', 'maxlength'=>5, 'nospace'=>true, 'tl_class'=>'w50'),
	'sql'               => "varchar(5) NOT NULL default ''"
);


class tl_faq_category_language extends Backend
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
