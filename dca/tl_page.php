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
 * @copyright  Felix Pfeiffer 2008, terminal42 gmbh 2008-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Felix Pfeiffer <info@felixpfeiffer.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Config
 */
$GLOBALS['TL_DCA']['tl_page']['config']['onload_callback'][] = array('tl_page_changelanguage','showSelectbox');
$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][] = array('tl_page_changelanguage','resetFallback');
$GLOBALS['TL_DCA']['tl_page']['config']['ondelete_callback'][] = array('tl_page_changelanguage','resetLanguageMain');
$GLOBALS['TL_DCA']['tl_page']['list']['label']['label_callback'] = array('tl_page_changelanguage', 'addFallbackNotice');


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['fallback']['eval']['submitOnChange'] = true;

$GLOBALS['TL_DCA']['tl_page']['fields']['languageMain'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['languageMain'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_page_changelanguage', 'getFallbackPages'),
	'eval'                    => array('includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_page']['no_subpage'], 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_page']['fields']['languageRoot'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['languageRoot'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_page_changelanguage', 'getRootPages'),
	'eval'                    => array('includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_page']['no_rootpage'], 'tl_class'=>'w50'),
);


class tl_page_changelanguage extends Backend
{

	/**
	 * Inject fields if appropriate.
	 *
	 * @access public
	 * @return void
	 */
	public function showSelectbox($dc)
	{
		if ($this->Input->get('act') == 'edit')
		{
			$objPage = $this->getPageDetails($dc->id);

			if ($objPage->type == 'root' && $objPage->fallback)
			{
				$GLOBALS['TL_DCA']['tl_page']['fields']['fallback']['eval']['tl_class'] = 'm12 w50';
				$GLOBALS['TL_DCA']['tl_page']['fields']['dns']['eval']['tl_class'] = 'clr w50';
				$GLOBALS['TL_DCA']['tl_page']['palettes']['root'] = preg_replace('@([,|;]fallback)([,|;])@','$1,languageRoot$2', $GLOBALS['TL_DCA']['tl_page']['palettes']['root']);
			}
			elseif ($objPage->type != 'root')
			{
				$objRootPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute($objPage->rootId);

				$objFallback = $this->Database->prepare("SELECT id FROM tl_page WHERE type='root' AND fallback='1' AND id!=? AND (dns=? OR id=?)")->limit(1)->execute($objRootPage->id, $objPage->domain, ($objRootPage->fallback ? $objRootPage->languageRoot : 0));

				if($objFallback->numRows)
				{
					$GLOBALS['TL_DCA']['tl_page']['fields']['title']['eval']['tl_class'] = 'w50';
					$GLOBALS['TL_DCA']['tl_page']['fields']['alias']['eval']['tl_class'] = 'clr w50';
					$GLOBALS['TL_DCA']['tl_page']['palettes'][$objPage->type] = preg_replace('@([,|;]title)([,|;])@','$1,languageMain$2', $GLOBALS['TL_DCA']['tl_page']['palettes'][$objPage->type]);
				}
			}
		}
		elseif ($this->Input->get('act') == 'editAll')
		{
			foreach( $GLOBALS['TL_DCA']['tl_page']['palettes'] as $name => $palette )
			{
				if ($name == '__selector__' || $name == 'root')
					continue;

				$GLOBALS['TL_DCA']['tl_page']['palettes'][$name] = preg_replace('@([,|;]title)([,|;])@','$1,languageMain$2', $palette);
			}
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

			if ($objPage->numRows && $objPage->type == 'root' && $objPage->fallback && !$objPage->languageRoot)
			{
				$arrIds = $this->getChildRecords($objPage->id, 'tl_page');
				$arrIds[] = $objPage->id;

				$this->Database->query("UPDATE tl_page SET languageMain=0 WHERE id IN (" . implode(',', $arrIds) . ")");
			}
			elseif ($objPage->numRows && $objPage->type == 'root' && !$objPage->fallback && $objPage->languageRoot)
			{
				$this->Database->query("UPDATE tl_page SET languageRoot=0 WHERE id=".$objPage->id);
			}
		}
	}


	/**
	 * Reset the language main when the fallback is deleted
	 * @param object
	 */
    public function resetLanguageMain($dc)
    {
        $arrIds = $this->getChildRecords($dc->id, 'tl_page');
        $arrIds[] = $dc->id;

        $this->Database->execute("UPDATE tl_page SET languageMain=0 WHERE languageMain IN (" . implode(',', $arrIds) . ")");
    }


	/**
	 * Show notice if no fallback page is set
	 */
	public function addFallbackNotice($row, $label, $dc=null, $imageAttribute='', $blnReturnImage=false, $blnProtected=false)
	{
		if (in_array('cacheicon', $this->Config->getActiveModules()))
		{
			$objPage = new tl_page_cacheicon();
			$label = $objPage->addImage($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);
		}
		elseif (in_array('Avisota', $this->Config->getActiveModules()))
		{
			$objPage = new tl_page_avisota();
			$label = $objPage->addIcon($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);
		}
		else
		{
			$objPage = new tl_page();
			$label = $objPage->addIcon($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);
		}

		if (!$row['languageMain'])
		{
			// Save resources if we are not a regular page
			if ($row['type'] == 'root' || $row['type'] == 'folder')
				return $label;

			$objPage = $this->getPageDetails($row['id']);

			$objRootPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute($objPage->rootId);

			$objFallback = $this->Database->prepare("SELECT id FROM tl_page WHERE type='root' AND fallback='1' AND id!=? AND (dns=? OR id=?)")->limit(1)->execute($objRootPage->id, $objPage->domain, ($objRootPage->fallback ? $objRootPage->languageRoot : 0));

			if ($objFallback->numRows)
			{
				$label .= '<span style="color:#b3b3b3; padding-left:3px;">[' . $GLOBALS['TL_LANG']['MSC']['noMainLanguage'] . ']</span>';
			}
		}

		return $label;
	}


	/**
	 * Return all fallback pages for the current page (used as options_callback).
	 *
	 * @access public
	 * @return array
	 */
	public function getFallbackPages($dc)
	{
		$this->import('ChangeLanguage');

		$arrPages = array();
		$arrRoot = $this->ChangeLanguage->findMainLanguageRootForPage($dc->id);

		if ($arrRoot !== false)
		{
			$this->generatePageOptions($arrPages, $arrRoot['id'], 0);
		}

		return $arrPages;
	}


	public function getRootPages($dc)
	{
		$arrPages = array();
		$objPages = $this->Database->prepare("SELECT * FROM tl_page WHERE type='root' AND fallback='1' AND languageRoot=0 AND language!=(SELECT language FROM tl_page WHERE id=?) AND id!=?")->execute($dc->id, $dc->id);

		while( $objPages->next() )
		{
			$arrPages[$objPages->id] = $objPages->title . (strlen($objPages->dns) ? (' (' . $objPages->dns . ')') : '') . ' [' . $objPages->language . ']';
		}

		return $arrPages;
	}


	/**
	 * Generates a list of all subpages
	 *
	 * @param array
	 * @param int
	 * @param int
	 */
	protected function generatePageOptions(&$arrPages, $intId=0, $level=-1)
	{
		// Add child pages
		$objPages = $this->Database->prepare("SELECT id, title FROM tl_page WHERE pid=? AND type != 'root' ORDER BY sorting")
								   ->execute($intId);

		if ($objPages->numRows < 1)
		{
			return;
		}

		++$level;
		$strOptions = '';

		while ($objPages->next())
		{
			$arrPages[$objPages->id] = str_repeat("&nbsp;", (3 * $level)) . $objPages->title;

			$this->generatePageOptions($arrPages, $objPages->id, $level);
		}
	}
}

