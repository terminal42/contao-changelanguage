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
 * @copyright  terminal42 gmbh 2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ChangeLanguage extends Controller
{

	public function __construct()
	{
		parent::__construct();

		$this->import('Database');
	}


	/**
	 * Find the main language page associated with the given page ID or page object
	 * @param Database_Result|int
	 * @return array|false
	 */
	public function findMainLanguagePageForPage($objPage)
	{
		if (is_numeric($objPage))
		{
			$objPage = $this->getPageDetails($objPage);
		}

		// If main langugae is not set, either this is the fallback or we have no fallback
		if ($objPage->languageMain == 0)
		{
			return false;
		}

		$objMain = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($objPage->languageMain);

		return $objMain->row();
	}


	/**
	 * Find the main language root page for a page ID or page object
	 * @param Database_Result|int
	 * @return array|false
	 */
	public function findMainLanguageRootForPage($objPage)
	{
		if (is_numeric($objPage))
		{
			$objPage = $this->getPageDetails($objPage);
		}

		$arrRoot = self::findFallbackRootForDomain($objPage->domain);

		if ($arrRoot === false)
		{
			return false;
		}

		if ($arrRoot['languageRoot'] > 0)
		{
			$objRoot = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($arrRoot['languageRoot']);

			return $objRoot->numRows ? $objRoot->row() : false;
		}
		elseif ($arrRoot['fallback'])
		{
			return $arrRoot;
		}

		return false;
	}


	/**
	 * Find all associated root pages for a given domain (use $objPage->domain)
	 * @param string
	 * @return array
	 */
	public function findLanguageRootsForDomain($strDomain)
	{
		$arrFallback = $this->findFallbackRootForDomain($strDomain);

		if ($arrFallback === false)
		{
			return array();
		}

		$arrPages = array();
		$objPages = $this->Database->prepare("SELECT DISTINCT * FROM tl_page WHERE type='root' AND (dns=? OR dns IN (SELECT dns FROM tl_page WHERE type='root' AND fallback='1' AND (id=? OR languageRoot=? OR (languageRoot>0 && languageRoot=?)))) ORDER BY sorting")->execute($arrFallback['dns'], $arrFallback['languageRoot'], $arrFallback['id'], $arrFallback['languageRoot']);

		while ($objPages->next())
		{
			$arrPages[$objPages->id] = $objPages->row();
		}

		return $arrPages;
	}


	/**
	 * Find the fallback page for a given domain (use $objPage->domain)
	 * @param string
	 * @return array|false
	 */
	public function findFallbackRootForDomain($strDomain)
	{
		$objPage = $this->Database->prepare("SELECT * FROM tl_page WHERE type='root' AND fallback='1' AND dns=?")
										  ->limit(1)
										  ->execute($strDomain);

		if ($objPage->numRows)
		{
			return $objPage->row();
		}

		return false;
	}
}

