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
 * @copyright  terminal42 gmbh 2008-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleLanguageRedirect extends Module
{

	/**
	 * Module does not output anything...
	 * Redirect if the user is logged in
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### LANGUAGE REDIRECT ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// If user is logged in, redirect him
		if (FE_USER_LOGGED_IN && !BE_USER_LOGGED_IN)
		{
			// try to switch the language/page
			if (\FrontendUser::getInstance()->language != $GLOBALS['TL_LANGUAGE'])
			{
				global $objPage;
				$mainLanguageID = $objPage->languageMain != 0 ? $objPage->languageMain : $objPage->id;
				$objPages =  \Database::getInstance()->prepare("SELECT * FROM tl_page WHERE languageMain=? OR id=? AND published=?")
		        							->execute($mainLanguageID, $mainLanguageID, 1);

		        while( $objPages->next() )
		        {
		        	// redirect
		        	if ($objPages->language == \FrontendUser::getInstance()->language)
		        	{
		        		$strParam = '';
		        		$strGet = '?';
		        		foreach( $_GET as $key => $value )
		        		{
		        			switch( $key )
		        			{
		        				case 'page':
		        				case 'keywords':
		        					$strGet .= $key.'='.$value.'&';
		        					break;

		        				default:
				        			$strParam .= '/'.$key.'/'.$value;
		        			}
		        		}

		        		$this->redirect($this->generateFrontendUrl($objPages->row(), $strParam).$strGet);
		        	}
		        }
			}
		}

		// if user is not logged in, we have the correct language, or no page exists, we do nothing
		// assume Contao has found the right language...
		return '';
	}


	/**
	 * Not in use, but must be declared because parent method is abstract
	 */
	protected function compile() {}
}

