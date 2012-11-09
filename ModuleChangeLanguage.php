<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Felix Pfeiffer 2008, Andreas Schempp 2008-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Felix Pfeiffer <info@felixpfeiffer.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class ModuleChangelanguage extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_changelanguage';

	/**
	 * ChangeLanguage object instance
	 * @var object
	 */
	protected $ChangeLanguage;


	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### CHANGE LANGUAGE ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Prepare custom language texts
		$this->customLanguageText = deserialize($this->customLanguageText, true);
		$customLanguageText = array();
		foreach ($this->customLanguageText as $arrText)
		{
			$customLanguageText[strtolower($arrText['value'])] = $arrText['label'];
		}
		$this->customLanguageText = $customLanguageText;

		if ($this->navigationTpl == '')
		{
			$this->navigationTpl = 'nav_default';
		}

		$this->ChangeLanguage = new ChangeLanguage();

		return parent::generate();
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
        global $objPage;

		// Required for the current pagetree language
		$objRootPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($objPage->rootId);

		$arrRootPages = $this->ChangeLanguage->findLanguageRootsForDomain($objPage->domain);


        // Check if there are foreign languages of this page
        $arrLanguagePages = array();
        $mainLanguageID = $objPage->languageMain != 0 ? $objPage->languageMain : $objPage->id;
        $arrPageIds =  $this->Database->prepare("SELECT id FROM tl_page WHERE languageMain=? OR id=?")
        							  ->execute($mainLanguageID, $mainLanguageID)
        							  ->fetchEach('id');

		foreach ($arrPageIds as $intId)
		{
			$objLanguagePage = $this->getPageDetails($intId);

			// If the page isn't published, continue with the next page
			if ((!$objLanguagePage->published || ($objLanguagePage->start > 0 && $objLanguagePage->start > time()) || ($objLanguagePage->stop > 0 && $objLanguagePage->stop < time())) && !BE_USER_LOGGED_IN) {

				continue;
			}

			// Do not add pages without root pages
			if ($arrRootPages[$objLanguagePage->rootId])
			{
				$arrLanguagePages[$arrRootPages[$objLanguagePage->rootId]['language']] = $objLanguagePage->row();
			}
		}

        $arrParams = array('url'=>array(), 'get'=>array());
        if ($this->keepUrlParams)
    	{
    		foreach( array_keys($_GET) as $strKey )
    		{
    			$strValue = $this->Input->get($strKey);

    			// Do not keep empty parameters and arrays
    			if ($strValue != '' && $strKey != 'language')
    			{
    				// Parameter passed after "?"
    				if (strpos($this->Environment->request, $strKey.'='.$strValue) !== false)
    				{
    					$arrParams['get'][$strKey] = $strValue;
    				}
    				else
    				{
    					$arrParams['url'][$strKey] = $strValue;
    				}
    			}
    		}
    	}

    	// Always keep search parameters
    	if ($this->Input->get('keywords') != '')
    	{
    		$arrParams['get']['keywords'] = $this->Input->get('keywords');
    	}

		$arrItems = array();
        $c = 0;
        $count = count($arrRootPages);

        foreach ($arrRootPages as $arrRootPage)
        {
        	$domain = '';
        	if ($objPage->domain != $arrRootPage['dns'])
            {
            	$domain  = ($this->Environment->ssl ? 'https://' : 'http://') . $arrRootPage['dns'] . '/';

            	if (strlen(TL_PATH))
            	{
            		$domain .= TL_PATH . '/';
            	}
            }


        	$blnDirectFallback = true;

        	// If the root isn't published, continue with the next page
            if ((!$arrRootPage['published'] || ($arrRootPage['start'] > 0 && $arrRootPage['start'] > time()) || ($arrRootPage['stop'] > 0 && $arrRootPage['stop'] < time())) && !BE_USER_LOGGED_IN)
            {
                continue;
            }

            // Active page
            else if($arrRootPage['language'] == $objRootPage->language)
            {
            	// If it is the active page, and we want to hide this, continue with the next page
            	if ($this->hideActiveLanguage)
            		continue;

				$active = true;
            	$pageTitle = $arrRootPage['title'];
            	$href = "";

            	if (in_array('articlelanguage', $this->Config->getActiveModules()) && strlen($_SESSION['ARTICLE_LANGUAGE']))
            	{
            		$objArticle = $this->Database->prepare("SELECT * FROM tl_article WHERE (pid=? OR pid=?) AND language=?")
            									 ->execute($objPage->id, $objPage->languageMain, $_SESSION['ARTICLE_LANGUAGE']);

            		if ($objArticle->numRows)
            		{
            			$objPage->cssClass = trim($objPage->cssClass . ' lang-' . $_SESSION['ARTICLE_LANGUAGE']);
            		}
            		else
            		{
            			$objPage->cssClass = trim($objPage->cssClass . ' lang-' . $arrRootPage['language']);
            		}
            	}
            	else
            	{
            		$objPage->cssClass = trim($objPage->cssClass . ' lang-' . $arrRootPage['language']);
            	}
            }

            // Search for foreign language
            else
            {
            	$active = false;
            	$target = '';

            	// HOOK: allow extensions to modify url parameters
				if (isset($GLOBALS['TL_HOOKS']['translateUrlParameters']) && is_array($GLOBALS['TL_HOOKS']['translateUrlParameters']))
				{
					foreach ($GLOBALS['TL_HOOKS']['translateUrlParameters'] as $callback)
					{
						$this->import($callback[0]);
						$arrParams = $this->$callback[0]->$callback[1]($arrParams, $arrRootPage['language'], $arrRootPage);
					}
				}

            	// Make sure $strParam is empty, otherwise previous pages could affect url
            	$strParam = '';
            	$arrRequest = array();

            	foreach( $arrParams['url'] as $k => $v )
            	{
            		$strParam .= '/' . $k . '/' . $v;
            	}

            	foreach( $arrParams['get'] as $k => $v )
            	{
            		$arrRequest[] = $k . '=' . $v;
            	}


            	// Matching language page found
	            if(array_key_exists($arrRootPage['language'], $arrLanguagePages))
	            {
	            	$pageTitle = $arrLanguagePages[$arrRootPage['language']]['title'];
	            	$href = $this->generateFrontendUrl($arrLanguagePages[$arrRootPage['language']], $strParam, $arrRootPage['language']) . (count($arrRequest) ? ('?'.implode('&amp;', $arrRequest)) : '');

	            	if ($arrLanguagePages[$arrRootPage['language']]['target'])
	            	{
	            		$target = ($objPage->outputFormat == 'html5') ? ' target="_blank"' : ' onclick="window.open(this.href); return false;"';
	            	}
	            }

	            // Step up in the current page trail until we find a page with valid languageMain
	            else
	            {
	            	$blnDirectFallback = false;
	            	$blnFound = false;
	            	$arrTrail = $objPage->trail;

	            	// last id in trail is the current page, we don't need to search that
	            	for ($i=count($arrTrail)-2; $i>=0; $i--)
	            	{
	            		// Fallback tree, search for trail id
	            		if ($objRootPage->fallback)
	            		{
	            			$objTrailPage = $this->Database->prepare("SELECT * FROM tl_page WHERE (id=? OR languageMain=?)")
		            									   ->execute($arrTrail[$i], $arrTrail[$i]);
	            		}

	            		// not fallback tree, search for trail languageMain
	            		else
	            		{
	            			$objTPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($arrTrail[$i]);

	            			// Basically impossible, but DB would throw exception
	            			if (!$objTPage->numRows)
	            				continue;

	            			if ($objTPage->languageMain == 0)
	            				continue;

	            			$objTrailPage = $this->Database->prepare("SELECT * FROM tl_page WHERE (id=? OR languageMain=?)")
		            									   ->execute($objTPage->languageMain, $objTPage->languageMain);
	            		}

	            		if ($objTrailPage->numRows)
	            		{
	            			while( $objTrailPage->next() )
							{
								if ($objTrailPage->type == 'root')
								{
									if ($objTrailPage->id == $arrRootPage['id'])
									{
										$blnFound = true;
										break;
									}

									continue;
								}

								$objPageDetails = $this->getPageDetails($objTrailPage->id);

								// We found a page in the correct page tree
								if ($objPageDetails->rootId == $arrRootPage['id'])
								{
									$blnFound = true;
									break;
								}
							}
	            		}

	            		if ($blnFound)
	            			break;
	            	}

	            	// We found a trail page
	            	if ($blnFound)
	            	{
		            	$pageTitle = $objTrailPage->title;
		            	$href = $this->generateFrontendUrl($objTrailPage->row(), $strParam, $arrRootPage['language']);

	        	    	if ($objTrailPage->target)
	    	        	{
		            		$target = ($objPage->outputFormat == 'html5') ? ' target="_blank"' : ' onclick="window.open(this.href); return false;"';
		            	}
	            	}

	            	else
	            	{
		            	$pageTitle = $arrRootPage['title'];
		            	$href = $this->generateFrontendUrl($arrRootPage, null, $arrRootPage['language']);
	            	}
	            }
            }

            // Hide languages without direct fallback
            if ($this->hideNoFallback && !$blnDirectFallback)
            {
            	continue;
            }

            // Build template array
			$arrItems[$c] = array
			(
				'isActive'	=> $active,
				'class'		=> 'lang-' . $arrRootPage['language'] . ($blnDirectFallback ? '' : ' nofallback') . ($c == 0 ? ' first' : '') . ($c == $count-1 ? ' last' : ''),
				'link'		=> $this->getLabel($arrRootPage['language']),
				'subitems'	=> '',
				'href'		=> ($domain . $href),
				'pageTitle' => strip_tags($pageTitle),
				'accesskey'	=> '',
				'tabindex'	=> '',
				'nofollow'	=> false,
				'target'	=> $target,
				'language'	=> $arrRootPage['language'],
			);

            $c++;
        }

        if ($this->customLanguage)
        {
	        usort($arrItems, array($this, 'orderByCustom'));
       	}


        $objTemplate = new FrontendTemplate($this->navigationTpl);
        $objTemplate->level = 'level_1';
        $objTemplate->items = $arrItems;

        $this->Template->items = $objTemplate->parse();

        // Fix contao problem with date/time formats...
        $this->getPageDetails($objPage->id);
	}


	/**
	 * Re-order language options by custom texts.
	 *
	 * @access private
	 * @param array $a
	 * @param array $b
	 * @return int
	 */
	private function orderByCustom($a, $b)
	{
		$arrCustom = array_keys($this->customLanguageText);

		$key1 = array_search($a['language'], $arrCustom);
		$key2 = array_search($b['language'], $arrCustom);

	    return ($key1 < $key2) ? -1 : 1;
	}


	private function getLabel($strLanguage)
	{
		if ($this->customLanguage && strlen($this->customLanguageText[$strLanguage]))
		{
			return $this->replaceInsertTags($this->customLanguageText[$strLanguage]);
		}

		return strtoupper($strLanguage);
	}
}

