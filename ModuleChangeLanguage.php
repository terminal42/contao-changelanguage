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


class ModuleChangelanguage extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_changelanguage';


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

        $this->import('ChangeLanguage');

        $strBuffer = parent::generate();

        if ($this->Template->items == '')
        {
            return '';
        }

        return $strBuffer;
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

        // Keep the URL parameters
        if ($this->keepUrlParams)
        {
            foreach (array_keys($_GET) as $strKey)
            {
                $strValue = \Input::get($strKey);

                // Do not keep empty parameters and arrays
                if ($strValue != '' && $strKey != 'language' && $strKey != 'auto_item')
                {
                    // Parameter passed after "?"
                    if (strpos(\Environment::get('request'), $strKey.'='.$strValue) !== false)
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
        if (\Input::get('keywords') != '')
        {
            $arrParams['get']['keywords'] = \Input::get('keywords');
        }

        $time = time();
        $arrItems = array();
        $c = 0;
        $count = count($arrRootPages);

        foreach ($arrRootPages as $arrRootPage)
        {
            $domain = '';

            // Get the new domain if it differs from the current one
            if ($objPage->domain != $arrRootPage['dns'])
            {
                $domain  = (\Environment::get('ssl') ? 'https://' : 'http://') . $arrRootPage['dns'] . '/';

                if (strlen(TL_PATH))
                {
                    $domain .= TL_PATH . '/';
                }
            }

            $blnDirectFallback = true;
            $strCssClass = 'lang-' . $arrRootPage['language'];

            // If the root isn't published, continue with the next page
            if ((!$arrRootPage['published'] || ($arrRootPage['start'] > 0 && $arrRootPage['start'] > time()) || ($arrRootPage['stop'] > 0 && $arrRootPage['stop'] < time())) && !BE_USER_LOGGED_IN)
            {
                continue;
            }

            // Active page
            if ($arrRootPage['language'] == $objRootPage->language)
            {
                // If it is the active page, and we want to hide this, continue with the next page
                if ($this->hideActiveLanguage)
                {
                    continue;
                }

                $active = true;
                $pageTitle = $arrRootPage['title'];
                $href = "";

                // Support the articlelanguage extension
                // @todo - does it work? do we need it?
                if (in_array('articlelanguage', \ModuleLoader::getActive()) && strlen($_SESSION['ARTICLE_LANGUAGE']))
                {
                    $objArticle = $this->Database->prepare("SELECT * FROM tl_article WHERE (pid=? OR pid=?) AND language=?")
                                                 ->execute($objPage->id, $objPage->languageMain, $_SESSION['ARTICLE_LANGUAGE']);

                    if ($objArticle->numRows)
                    {
                        $strCssClass = 'lang-' . $_SESSION['ARTICLE_LANGUAGE'];
                    }
                }

                // Make sure that the class is only added once
                if (strpos($objPage->cssClass, $strCssClass) === false)
                {
                    $objPage->cssClass = trim($objPage->cssClass . ' ' . $strCssClass);
                }
            }

            // Search for foreign language
            else
            {
                $active = false;
                $target = '';
                $arrTranslatedParams = $arrParams;

                // HOOK: allow extensions to modify url parameters
                if (isset($GLOBALS['TL_HOOKS']['translateUrlParameters']) && is_array($GLOBALS['TL_HOOKS']['translateUrlParameters']))
                {
                    foreach ($GLOBALS['TL_HOOKS']['translateUrlParameters'] as $callback)
                    {
                        $this->import($callback[0]);
                        $arrTranslatedParams = $this->$callback[0]->$callback[1]($arrParams, $arrRootPage['language'], $arrRootPage);
                    }
                }

                // Try to find matching article
                if (isset($arrParams['url']['articles']))
                {
                    $objArticle = null;

                    // Get the fallback article
                    if ($arrRootPage['fallback'])
                    {
                        $objArticle = $this->Database->prepare("SELECT id, alias FROM tl_article WHERE id=(SELECT languageMain FROM tl_article WHERE pid=? AND alias=?)")
                                                     ->execute($objPage->id, $arrParams['url']['articles']);
                    }
                    else
                    {
                        $arrSubpages = $this->Database->getChildRecords($arrRootPage['id'], 'tl_page', true);

                        if (!empty($arrSubpages))
                        {
                            // Find foreign article by fallback alias
                            if ($objRootPage->fallback)
                            {
                                $objArticle = $this->Database->prepare("SELECT id, alias FROM tl_article WHERE languageMain=(SELECT id FROM tl_article WHERE alias=?) AND pid IN (" . implode(',', $arrSubpages) . ")")
                                                             ->execute($arrParams['url']['articles']);
                            }
                            // Find foreign article by current article alias
                            else
                            {
                                $objArticle = $this->Database->prepare("SELECT id, alias FROM tl_article WHERE languageMain=(SELECT languageMain FROM tl_article WHERE pid=? AND alias=?) AND pid IN (" . implode(',', $arrSubpages) . ")")
                                                             ->execute($objPage->id, $arrParams['url']['articles']);
                            }
                        }
                    }

                    if ($objArticle->numRows)
                    {
                        $arrTranslatedParams['url']['articles'] = $objArticle->alias;
                    }
                }

                // Check for other modules
                if (($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item'])) || isset($arrTranslatedParams['url']['items']))
                {
                    $blnFound = false;

                    // News
                    if (in_array('news', \ModuleLoader::getActive()))
                    {
                        $objNewsArchive = \NewsArchiveModel::findByJumpTo($objPage->id);

                        if ($objNewsArchive !== null)
                        {
                            $objNews = \NewsModel::findPublishedByParentAndIdOrAlias(($GLOBALS['TL_CONFIG']['useAutoItem'] ? \Input::get('auto_item') : \Input::get('items')), array($objNewsArchive->id));

                            // News item exists, find foreign item
                            if ($objNews !== null)
                            {
                                if (!$objNewsArchive->master)
                                {
                                    $objNewsForeign = $this->Database->prepare("SELECT * FROM tl_news WHERE languageMain=? AND pid=(SELECT id FROM tl_news_archive WHERE language=?)" . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : ""))
                                                                     ->limit(1)
                                                                     ->execute($objNews->id, $arrRootPage['language']);
                                }
                                else
                                {
                                    $objNewsForeign = $this->Database->prepare("SELECT * FROM tl_news WHERE (id=? OR languageMain=?) AND pid=(SELECT id FROM tl_news_archive WHERE language=?)" . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : ""))
                                                                     ->limit(1)
                                                                     ->execute($objNews->languageMain, $objNews->languageMain, $arrRootPage['language']);
                                }

                                if ($objNewsForeign->numRows)
                                {
                                    $blnFound = true;
                                    $arrTranslatedParams['url']['items'] = (($objNewsForeign->alias != '' && !$GLOBALS['TL_CONFIG']['disableAlias']) ? $objNewsForeign->alias : $objNewsForeign->id);
                                }
                            }
                        }
                    }

                    // Events
                    if (!$blnFound && in_array('calendar', \ModuleLoader::getActive()))
                    {
                        $objCalendar = \CalendarModel::findByJumpTo($objPage->id);

                        if ($objCalendar !== null)
                        {
                            $objEvent = \CalendarEventsModel::findPublishedByParentAndIdOrAlias(($GLOBALS['TL_CONFIG']['useAutoItem'] ? \Input::get('auto_item') : \Input::get('items')), array($objCalendar->id));

                            // Event exists, find foreign item
                            if ($objEvent !== null)
                            {
                                if (!$objCalendar->master)
                                {
                                    $objEventForeign = $this->Database->prepare("SELECT * FROM tl_calendar_events WHERE languageMain=? AND pid=(SELECT id FROM tl_calendar WHERE language=?)" . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : ""))
                                                                     ->limit(1)
                                                                     ->execute($objEvent->id, $arrRootPage['language']);
                                }
                                else
                                {
                                    $objEventForeign = $this->Database->prepare("SELECT * FROM tl_calendar_events WHERE (id=? OR languageMain=?) AND pid=(SELECT id FROM tl_calendar WHERE language=?)" . (!BE_USER_LOGGED_IN ? " AND (start='' OR start<$time) AND (stop='' OR stop>$time) AND published=1" : ""))
                                                                     ->limit(1)
                                                                     ->execute($objEvent->languageMain, $objEvent->languageMain, $arrRootPage['language']);
                                }

                                if ($objEventForeign->numRows)
                                {
                                    $blnFound = true;
                                    $arrTranslatedParams['url']['items'] = ((!$GLOBALS['TL_CONFIG']['disableAlias'] && $objEventForeign->alias != '') ? $objEventForeign->alias : $objEventForeign->id);
                                }
                            }
                        }
                    }

                    // FAQ
                    if (!$blnFound && in_array('faq', \ModuleLoader::getActive()))
                    {
                        $objFaqCategory = \FaqCategoryModel::findByJumpTo($objPage->id);

                        if ($objFaqCategory !== null)
                        {
                            $objFaq = \FaqModel::findPublishedByParentAndIdOrAlias(($GLOBALS['TL_CONFIG']['useAutoItem'] ? \Input::get('auto_item') : \Input::get('items')), array($objFaqCategory->id));

                            // FAQ item exists, find foreign item
                            if ($objFaq !== null)
                            {
                                if (!$objFaqCategory->master)
                                {
                                    $objFaqForeign = $this->Database->prepare("SELECT * FROM tl_faq WHERE languageMain=? AND pid=(SELECT id FROM tl_faq_category WHERE language=?)" . (!BE_USER_LOGGED_IN ? " AND published=1" : ""))
                                                                     ->limit(1)
                                                                     ->execute($objFaq->id, $arrRootPage['language']);
                                }
                                else
                                {
                                    $objFaqForeign = $this->Database->prepare("SELECT * FROM tl_faq WHERE (id=? OR languageMain=?) AND pid=(SELECT id FROM tl_faq_category WHERE language=?)" . (!BE_USER_LOGGED_IN ? " AND published=1" : ""))
                                                                     ->limit(1)
                                                                     ->execute($objFaq->languageMain, $objFaq->languageMain, $arrRootPage['language']);
                                }

                                if ($objFaqForeign->numRows)
                                {
                                    $blnFound = true;
                                    $arrTranslatedParams['url']['items'] = ((!$GLOBALS['TL_CONFIG']['disableAlias'] && $objFaqForeign->alias != '') ? $objFaqForeign->alias : $objFaqForeign->id);
                                }
                            }
                        }
                    }
                }

                $arrRequest = array();

                // Make sure $strParam is empty, otherwise previous pages could affect url
                $strParam = '';

                // Build the URL
                foreach ($arrTranslatedParams['url'] as $k => $v)
                {
                    if ($GLOBALS['TL_CONFIG']['useAutoItem'] && in_array($k, $GLOBALS['TL_AUTO_ITEM']))
                    {
                        $strParam .= '/' . $v;
                    }
                    else
                    {
                        $strParam .= '/' . $k . '/' . $v;
                    }
                }

                // Append the query
                foreach ($arrTranslatedParams['get'] as $k => $v)
                {
                    $arrRequest[] = $k . '=' . $v;
                }

                // Matching language page found
                if (array_key_exists($arrRootPage['language'], $arrLanguagePages))
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
                'isActive'    => $active,
                'class'        => 'lang-' . $arrRootPage['language'] . ($blnDirectFallback ? '' : ' nofallback') . ($active ? ' active' : '') . ($c == 0 ? ' first' : '') . ($c == $count-1 ? ' last' : ''),
                'link'        => $this->getLabel($arrRootPage['language']),
                'subitems'    => '',
                'href'        => ($domain . $href),
                'pageTitle' => strip_tags($pageTitle),
                'accesskey'    => '',
                'tabindex'    => '',
                'nofollow'    => false,
                'target'    => $target . ' hreflang="' . $arrRootPage['language'] . '"',
                'language'    => $arrRootPage['language'],
            );

            // Inject <link rel=""> for the alternate language
            if (!$active && $blnDirectFallback)
            {
                $GLOBALS['TL_HEAD'][] = '<link rel="alternate" hreflang="' . $arrRootPage['language'] . '" lang="' . $arrRootPage['language'] . '" href="' . ($domain . $href) . '" title="' . specialchars($pageTitle, true) . '"' . ($objPage->outputFormat == 'html5' ? '>' : ' />');
            }

            $c++;
        }

        if ($c > 0)
        {
            if ($this->customLanguage)
            {
                usort($arrItems, array($this, 'orderByCustom'));
            }


            $objTemplate = new FrontendTemplate($this->navigationTpl);
            $objTemplate->setData($this->arrData);
            $objTemplate->level = 'level_1';
            $objTemplate->items = $arrItems;

            $this->Template->items = $objTemplate->parse();
        }

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
        if ($this->customLanguage && strlen($this->customLanguageText[strtolower($strLanguage)]))
        {
            return $this->replaceInsertTags($this->customLanguageText[strtolower($strLanguage)]);
        }

        return strtoupper($strLanguage);
    }
}

