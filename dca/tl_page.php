<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Felix Pfeiffer : Neue Medien 2008 / Andreas Schempp 2009
 * @author 	   Felix Pfeiffer <info@felixpfeiffer.com>, Andreas Schempp <andreas@schempp.ch>
 * @license	   LGPL
 */


/**
 * Config
 */
$GLOBALS['TL_DCA']['tl_page']['config']['onload_callback'][] = array('tl_page_changelanguage','showSelectbox');
$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][] = array('tl_page_changelanguage','resetFallback');
$GLOBALS['TL_DCA']['tl_page']['list']['label']['label_callback'] = array('tl_page_changelanguage', 'addFallbackNotice');


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['languageMain'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['languageMain'],
	'exclude'                 => false,
	'inputType'               => 'select',
	'options_callback'        => array('tl_page_changelanguage', 'getFallbackPages'),
	'eval'                    => array('tl_class'=>'w50'),
);


/**
 * Provides miscellaneous functions for backend data container.
 * 
 * @extends Backend
 */
class tl_page_changelanguage extends Backend 
{
	
	public $arrPages = array();
	
	
	/**
	 * Return all fallback pages for the current page (used as options_callback).
	 * 
	 * @access public
	 * @return array
	 */
	public function getFallbackPages()
	{
		// Handle "edit all" option
		if ($this->Input->get('act') == 'editAll')
		{
			if (!is_array($GLOBALS['languageMain_IDS']))
			{
				$ids = $this->Session->get('CURRENT');
				$GLOBALS['languageMain_IDS'] = $ids['IDS'];
			}
			$this->Input->setGet('id', array_shift($GLOBALS['languageMain_IDS']));
		}
		
		
		$objPage = $this->getPageDetails($this->Input->get('id'));
		
		$objFallback = $this->Database->prepare("SELECT id, title FROM tl_page WHERE dns=? AND type='root' AND fallback=1")
									  ->limit(1)
									  ->execute($objPage->domain);

		$this->arrPages[0] = $GLOBALS['TL_LANG']['tl_page']['no_subpage'];
		
		if($objFallback->numRows && $objPage->rootId != $objFallback->id)
		{
			$this->createPageList($objFallback->id, 0);			
		}
		
		return $this->arrPages;
	}
	
	
	/**
	 * Generates a list of all subpages and fill into $this->arrPages.
	 * 
	 * @access public
	 * @param int $intId. (default: 0)
	 * @param int $level. (default: -1)
	 * @return void
	 */
	public function createPageList($intId=0, $level=-1)
	{
		// Add child pages
		$objPages = $this->Database->prepare("SELECT id, title FROM tl_page WHERE pid=? AND ( type = 'regular' OR type = 'redirect' OR type = 'forward') ORDER BY sorting")
								   ->execute($intId);
								   
		if ($objPages->numRows < 1)
		{
			return;
		}

		++$level;
		$strOptions = '';

		while ($objPages->next())
		{
			$fallbackID = $objPages->id;
			$fallbackTitle = str_repeat("&nbsp;", (3 * $level)) . $objPages->title;
			$this->arrPages[$fallbackID] = $fallbackTitle;
			
			$this->createPageList($objPages->id, $level);
		}
	}
	
	
	/**
	 * Inject languageMain field if appropriate.
	 * 
	 * @access public
	 * @return void
	 */
	public function showSelectbox()
	{
		if($this->Input->get('act') == "edit")
		{
			$objPage = $this->getPageDetails($this->Input->get('id'));
			
			// Save resources if we are not a regular page
			if ($objPage->type !== 'regular')
				return;
			
			$objFallback = $this->Database->prepare("SELECT id FROM tl_page WHERE dns=? AND type='root' AND fallback=1 LIMIT 1")
								->execute($objPage->domain);

			if($objFallback->numRows && $objPage->rootId != 0 && $objFallback->id != $objPage->rootId)
			{
				$GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] = preg_replace('@([,|;])language([,|;])@','$1language,languageMain$2', $GLOBALS['TL_DCA']['tl_page']['palettes']['regular']);
			}
		}
		else if($this->Input->get('act') == "editAll")
		{
			$GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] = preg_replace('@([,|;])language([,|;])@','$1language,languageMain$2', $GLOBALS['TL_DCA']['tl_page']['palettes']['regular']);
		}
	}
	
	
	/**
	 * Make sure languageMain is "0" on fallback tree. Otherwise unknown behaviour could occure.
	 * 
	 * @access public
	 * @param mixed $dc
	 * @return void
	 */
	public function resetFallback($dc)
	{
		if ($dc->id > 0)
		{
			$objPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute($dc->id);
			
			if ($objPage->numRows && $objPage->type == 'root' && $objPage->fallback)
			{
				$arrIds = $this->getChildRecords($objPage->id, 'tl_page');
				$arrIds[] = $objPage->id;
				
				$this->Database->execute("UPDATE tl_page SET languageMain=0 WHERE id IN (" . implode(',', $arrIds) . ")");
			}
		}
	}
	
	
	/**
	 * Show notice if no fallback page is set
	 */
	public function addFallbackNotice($row, $label, $imageAttribute, DataContainer $dc)
	{
		if (in_array('cacheicon', $this->Config->getActiveModules()))
		{
			$objPage = new tl_page_cacheicon();
		}
		else
		{
			$objPage = new tl_page();
		}
		
		$label = $objPage->addImage($row, $label, $imageAttribute, $dc);
		
		if (!$row['languageMain'])
		{
			$objPage = $this->getPageDetails($row['id']);
				
			// Save resources if we are not a regular page
			if ($objPage->type !== 'regular')
				return $label;
			
			$objFallback = $this->Database->prepare("SELECT id FROM tl_page WHERE dns=? AND type='root' AND fallback=1 LIMIT 1")
								->execute($objPage->domain);
	
			if($objFallback->numRows && $objPage->rootId != 0 && $objFallback->id != $objPage->rootId)
			{
				$label .= '<span style="color:#b3b3b3; padding-left:3px;">[' . $GLOBALS['TL_LANG']['MSC']['noMainLanguage'] . ']</span>';
			}
		}
		
		return $label;
	}
}

