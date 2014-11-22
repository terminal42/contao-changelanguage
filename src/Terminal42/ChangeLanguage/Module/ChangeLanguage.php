<?php

namespace Terminal42\ChangeLanguage\Module;

use ContaoCommunityAlliance\Contao\LanguageRelations\LanguageRelations;

class Changelanguage extends \Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_changelanguage';

    /**
     * Custom language labels
     * @var array
     */
    protected $customLangLabels = array();


    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### CHANGE LANGUAGE ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Prepare custom language texts
        $this->customLanguageText = deserialize($this->customLanguageText, true);

        foreach ($this->customLanguageText as $arrText) {
            $this->customLangLabels[strtolower($arrText['value'])] = $arrText['label'];
        }

        if ($this->navigationTpl == '') {
            $this->navigationTpl = 'nav_default';
        }

        $strBuffer = parent::generate();

        if ($this->Template->items == '') {
            return '';
        }

        return $strBuffer;
    }


    /**
     * Generate module
     */
    protected function compile()
    {
        $items = array();
        global $objPage;

        $pages = LanguageRelations::getRelations($objPage->id);

        // If active language should not be hidden, include it
        if (!$this->hideActiveLanguage) {
            $pages = array_merge(array($objPage->id), $pages);
        }

        if (empty($pages)) {
            return;
        }

        // Load all the models into the registry
        $pageModels = array();
        foreach ($pages as $pageId) {
            $pageModels[$pageId] = \PageModel::findWithDetails($pageId);
        }

        // Sort the models
        $this->sortPages($pageModels);

        foreach ($pageModels as $pageModel) {

            // If the page isn't published, continue with the next page
            if ((!$pageModel->published
                    || ($pageModel->start > 0 && $pageModel->start > time())
                    || ($pageModel->stop > 0 && $pageModel->stop < time()))
                && !BE_USER_LOGGED_IN
            ) {
                continue;
            }

            // Active
            $active = ($pageModel->rootLanguage === $objPage->rootLanguage);

            // Href
            $href = $pageModel->getFrontendUrl(null, $pageModel->rootLanguage);

            // Page title
            $pageTitle = $pageModel->pageTitle ?: $pageModel->title;

            // Build template array
            $items[] = array
            (
                'isActive'  => $active,
                'class'     => 'lang-' . $pageModel->rootLanguage . (($active) ? ' active' : ''),
                'link'      => $this->getLabel($pageModel->rootLanguage),
                'subitems'  => '',
                'href'      => $href,
                'pageTitle' => strip_tags($pageTitle),
                'accesskey' => '',
                'tabindex'  => '',
                'nofollow'  => false,
                //'target'    => $target . ' hreflang="' . $arrRootPage['language'] . '"',
                'language'  => $pageModel->rootLanguage,
            );

            // Inject <link rel=""> for the alternate language
            if (!$active) {
                $GLOBALS['TL_HEAD'][] = sprintf('<link rel="alternate" hreflang="%s" lang="%s" href="%s" title="%s"%s',
                    $pageModel->rootLanguage,
                    $pageModel->rootLanguage,
                    $href,
                    specialchars($pageTitle, true),
                    ($objPage->outputFormat == 'html5') ? '>' : ' />'
                );
            }
        }

        $objTemplate = new \FrontendTemplate($this->navigationTpl);
        $objTemplate->setData($this->arrData);
        $objTemplate->level = 'level_1';
        $objTemplate->items = $items;

        $this->Template->items = $objTemplate->parse();

        /*

        // Check if there are foreign languages of this page
        $arrLanguagePages = array();
        $mainLanguageID = $objPage->languageMain != 0 ? $objPage->languageMain : $objPage->id;
        $arrPageIds = $this->Database->prepare("SELECT id FROM tl_page WHERE languageMain=? OR id=?")
            ->execute($mainLanguageID, $mainLanguageID)
            ->fetchEach('id');

        foreach ($arrPageIds as $intId) {
            $objLanguagePage = $this->getPageDetails($intId);

            // If the page isn't published, continue with the next page
            if ((!$objLanguagePage->published || ($objLanguagePage->start > 0 && $objLanguagePage->start > time()) || ($objLanguagePage->stop > 0 && $objLanguagePage->stop < time())) && !BE_USER_LOGGED_IN) {

                continue;
            }

            // Do not add pages without root pages
            if ($arrRootPages[$objLanguagePage->rootId]) {
                $arrLanguagePages[$arrRootPages[$objLanguagePage->rootId]['language']] = $objLanguagePage->row();
            }
        }

        $arrParams = array('url' => array(), 'get' => array());
        if ($this->keepUrlParams) {
            foreach (array_keys($_GET) as $strKey) {
                $strValue = $this->Input->get($strKey);

                // Do not keep empty parameters and arrays
                if ($strValue != '' && $strKey != 'language' && $strKey !== 'auto_item') {
                    // Parameter passed after "?"
                    if (strpos($this->Environment->request, $strKey . '=' . $strValue) !== false) {
                        $arrParams['get'][$strKey] = $strValue;
                    } else {
                        $arrParams['url'][$strKey] = $strValue;
                    }
                }
            }
        }

        // Always keep search parameters
        if ($this->Input->get('keywords') != '') {
            $arrParams['get']['keywords'] = $this->Input->get('keywords');
        }

        $arrItems = array();
        $c = 0;
        $count = count($arrRootPages);

        foreach ($arrRootPages as $arrRootPage) {
            $domain = '';
            if ($objPage->domain != $arrRootPage['dns']) {
                $domain = ($this->Environment->ssl ? 'https://' : 'http://') . $arrRootPage['dns'] . '/';

                if (strlen(TL_PATH)) {
                    $domain .= TL_PATH . '/';
                }
            }


            $blnDirectFallback = true;
            $strCssClass = 'lang-' . $arrRootPage['language'];

            // If the root isn't published, continue with the next page
            if ((!$arrRootPage['published'] || ($arrRootPage['start'] > 0 && $arrRootPage['start'] > time()) || ($arrRootPage['stop'] > 0 && $arrRootPage['stop'] < time())) && !BE_USER_LOGGED_IN) {
                continue;
            } // Active page
            else if ($arrRootPage['language'] == $objRootPage->language) {
                // If it is the active page, and we want to hide this, continue with the next page
                if ($this->hideActiveLanguage)
                    continue;

                $active = true;
                $pageTitle = $arrRootPage['title'];
                $href = "";

                if (in_array('articlelanguage', $this->Config->getActiveModules()) && strlen($_SESSION['ARTICLE_LANGUAGE'])) {
                    $objArticle = $this->Database->prepare("SELECT * FROM tl_article WHERE (pid=? OR pid=?) AND language=?")
                        ->execute($objPage->id, $objPage->languageMain, $_SESSION['ARTICLE_LANGUAGE']);

                    if ($objArticle->numRows) {
                        $strCssClass = 'lang-' . $_SESSION['ARTICLE_LANGUAGE'];
                    }
                }

                // make sure that the class is only added once
                if (strpos($objPage->cssClass, $strCssClass) === false) {
                    $objPage->cssClass = trim($objPage->cssClass . ' ' . $strCssClass);
                }
            } // Search for foreign language
            else {
                $active = false;
                $target = '';

                // HOOK: allow extensions to modify url parameters
                if (isset($GLOBALS['TL_HOOKS']['translateUrlParameters']) && is_array($GLOBALS['TL_HOOKS']['translateUrlParameters'])) {
                    foreach ($GLOBALS['TL_HOOKS']['translateUrlParameters'] as $callback) {
                        $this->import($callback[0]);
                        $arrParams = $this->$callback[0]->$callback[1]($arrParams, $arrRootPage['language'], $arrRootPage);
                    }
                }

                // Make sure $strParam is empty, otherwise previous pages could affect url
                $strParam = '';
                $arrRequest = array();

                foreach ($arrParams['url'] as $k => $v) {
                    if ($GLOBALS['TL_CONFIG']['useAutoItem'] && in_array($k, $GLOBALS['TL_AUTO_ITEM'])) {
                        $strParam .= '/' . $v;
                    } else {
                        $strParam .= '/' . $k . '/' . $v;
                    }
                }

                foreach ($arrParams['get'] as $k => $v) {
                    $arrRequest[] = $k . '=' . $v;
                }


                // Matching language page found
                if (array_key_exists($arrRootPage['language'], $arrLanguagePages)) {
                    $pageTitle = $arrLanguagePages[$arrRootPage['language']]['title'];
                    $href = $this->generateFrontendUrl($arrLanguagePages[$arrRootPage['language']], $strParam, $arrRootPage['language']) . (count($arrRequest) ? ('?' . implode('&amp;', $arrRequest)) : '');

                    if ($arrLanguagePages[$arrRootPage['language']]['target']) {
                        $target = ($objPage->outputFormat == 'html5') ? ' target="_blank"' : ' onclick="window.open(this.href); return false;"';
                    }
                } // Step up in the current page trail until we find a page with valid languageMain
                else {
                    $blnDirectFallback = false;
                    $blnFound = false;
                    $arrTrail = $objPage->trail;

                    // last id in trail is the current page, we don't need to search that
                    for ($i = count($arrTrail) - 2; $i >= 0; $i--) {
                        // Fallback tree, search for trail id
                        if ($objRootPage->fallback) {
                            $objTrailPage = $this->Database->prepare("SELECT * FROM tl_page WHERE (id=? OR languageMain=?)")
                                ->execute($arrTrail[$i], $arrTrail[$i]);
                        } // not fallback tree, search for trail languageMain
                        else {
                            $objTPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($arrTrail[$i]);

                            // Basically impossible, but DB would throw exception
                            if (!$objTPage->numRows)
                                continue;

                            if ($objTPage->languageMain == 0)
                                continue;

                            $objTrailPage = $this->Database->prepare("SELECT * FROM tl_page WHERE (id=? OR languageMain=?)")
                                ->execute($objTPage->languageMain, $objTPage->languageMain);
                        }

                        if ($objTrailPage->numRows) {
                            while ($objTrailPage->next()) {
                                if ($objTrailPage->type == 'root') {
                                    if ($objTrailPage->id == $arrRootPage['id']) {
                                        $blnFound = true;
                                        break;
                                    }

                                    continue;
                                }

                                $objPageDetails = $this->getPageDetails($objTrailPage->id);

                                // We found a page in the correct page tree
                                if ($objPageDetails->rootId == $arrRootPage['id']) {
                                    $blnFound = true;
                                    break;
                                }
                            }
                        }

                        if ($blnFound)
                            break;
                    }

                    // We found a trail page
                    if ($blnFound) {
                        $pageTitle = $objTrailPage->title;
                        $href = $this->generateFrontendUrl($objTrailPage->row(), $strParam, $arrRootPage['language']);

                        if ($objTrailPage->target) {
                            $target = ($objPage->outputFormat == 'html5') ? ' target="_blank"' : ' onclick="window.open(this.href); return false;"';
                        }
                    } else {
                        $pageTitle = $arrRootPage['title'];
                        $href = $this->generateFrontendUrl($arrRootPage, null, $arrRootPage['language']);
                    }
                }
            }

            // Hide languages without direct fallback
            if ($this->hideNoFallback && !$blnDirectFallback) {
                continue;
            }

            // Build template array
            $arrItems[$c] = array
            (
                'isActive'  => $active,
                'class'     => 'lang-' . $arrRootPage['language'] . ($blnDirectFallback ? '' : ' nofallback') . (($active && version_compare(VERSION, '3.0', '>=')) ? ' active' : '') . ($c == 0 ? ' first' : '') . ($c == $count - 1 ? ' last' : ''),
                'link'      => $this->getLabel($arrRootPage['language']),
                'subitems'  => '',
                'href'      => ($domain . $href),
                'pageTitle' => strip_tags($pageTitle),
                'accesskey' => '',
                'tabindex'  => '',
                'nofollow'  => false,
                'target'    => $target . ' hreflang="' . $arrRootPage['language'] . '"',
                'language'  => $arrRootPage['language'],
            );

            // Inject <link rel=""> for the alternate language
            if (!$active && $blnDirectFallback) {
                $GLOBALS['TL_HEAD'][] = '<link rel="alternate" hreflang="' . $arrRootPage['language'] . '" lang="' . $arrRootPage['language'] . '" href="' . ($domain . $href) . '" title="' . specialchars($pageTitle, true) . '"' . ($objPage->outputFormat == 'html5' ? '>' : ' />');
            }

            $c++;
        }

        if ($c > 0) {
            if ($this->customLanguage) {
                usort($arrItems, array($this, 'orderByCustom'));
            }


            $objTemplate = new FrontendTemplate($this->navigationTpl);
            $objTemplate->setData($this->arrData);
            $objTemplate->level = 'level_1';
            $objTemplate->items = $arrItems;

            $this->Template->items = $objTemplate->parse();
        }

        // Fix contao problem with date/time formats...
        $this->getPageDetails($objPage->id);*/
    }


    /**
     * Re-order pages so the navigation stays consistent
     *
     * @param   array
     */
    private function sortPages(&$pageModels)
    {
        if ($this->customLangLabels) {
            $custom = array_keys($this->customLangLabels);

            usort($pageModels, function($a, $b) use ($custom) {
                $key1 = array_search($a->language, $custom);
                $key2 = array_search($b->language, $custom);

                return ($key1 < $key2) ? -1 : 1;
            });
        } else {
            ksort($pageModels);
        }
    }


    /**
     * Get the label for a language
     * @param   string
     * @return  string
     */
    protected function getLabel($language)
    {
        $language = strtolower($language);
        if ($this->customLanguage && $this->customLangLabels[$language]) {
            return \Controller::replaceInsertTags($this->customLangLabels[$language]);
        }

        return strtoupper($language);
    }
}