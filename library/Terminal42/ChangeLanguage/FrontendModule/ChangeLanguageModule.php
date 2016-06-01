<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\FrontendModule;

use Contao\BackendTemplate;
use Contao\Controller;
use Contao\Database;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Module;
use Contao\PageModel;
use Contao\System;
use Terminal42\ChangeLanguage\Finder;

/**
 * Class ChangeLanguageModule
 *
 * @property bool  $hideActiveLanguage
 * @property bool  $hideNoFallback
 * @property bool  $keepUrlParams
 * @property bool  $customLanguage
 * @property array $customLanguageText
 */
class ChangeLanguageModule extends Module
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_changelanguage';

    /**
     * Generate the frontend module.
     *
     * @return string
     */
    public function generate()
    {
        if ('BE' === TL_MODE) {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . $GLOBALS['TL_LANG']['FMD'][$this->type] . ' ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Prepare custom language texts
        $this->customLanguageText = deserialize($this->customLanguageText, true);
        $customLanguageText = array();
        foreach ($this->customLanguageText as $arrText) {
            $customLanguageText[strtolower($arrText['value'])] = $arrText['label'];
        }
        $this->customLanguageText = $customLanguageText;

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
        global $objPage;

        // Required for the current pagetree language
        $objRootPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($objPage->rootId);

        $arrRootPages = Finder::findLanguageRootsForDomain($objPage->domain);

        // Check if there are foreign languages of this page
        $arrLanguagePages = array();
        $mainLanguageID = $objPage->languageMain != 0 ? $objPage->languageMain : $objPage->id;
        $arrPageIds =  Database::getInstance()
            ->prepare("SELECT id FROM tl_page WHERE languageMain=? OR id=?")
            ->execute($mainLanguageID, $mainLanguageID)
            ->fetchEach('id')
        ;

        foreach ($arrPageIds as $intId) {
            $objLanguagePage = PageModel::findPublishedById($intId);

            if (null === $objLanguagePage) {
                continue;
            }

            $objLanguagePage->loadDetails();

            // Do not add pages without root pages
            if ($arrRootPages[$objLanguagePage->rootId]) {
                $arrLanguagePages[$arrRootPages[$objLanguagePage->rootId]['language']] = $objLanguagePage->row();
            }
        }

        $arrParams = array('url'=>array(), 'get'=>array());

        // Keep the URL parameters
        if ($this->keepUrlParams) {
            foreach (array_keys($_GET) as $strKey) {
                $strValue = Input::get($strKey);

                // Do not keep empty parameters and arrays
                if ('' != $strValue && 'language' !== $strKey && 'auto_item' !== $strKey) {
                    // Parameter passed after "?"
                    if (strpos(Environment::get('request'), $strKey . '=' . $strValue) !== false) {
                        $arrParams['get'][$strKey] = $strValue;
                    } else {
                        $arrParams['url'][$strKey] = $strValue;
                    }
                }
            }
        }

        // Always keep search parameters
        if (Input::get('keywords') != '') {
            $arrParams['get']['keywords'] = \Input::get('keywords');
        }

        $c        = 0;
        $count    = count($arrRootPages);
        $arrItems = array();

        foreach ($arrRootPages as $arrRootPage) {
            $absoluteUrl = false;
            $blnDirectFallback = true;
            $domain = '';

            if ($arrRootPage['dns'] != '') {
                $domain = ($this->Environment->ssl ? 'https://' : 'http://') . $arrRootPage['dns'] . TL_PATH . '/';
            }

            if ($objPage->domain != $arrRootPage['dns']) {
                $absoluteUrl = true;
            }

            // If the root isn't published, continue with the next page
            if (true !== BE_USER_LOGGED_IN
                && (
                    !$arrRootPage['published']
                    || ($arrRootPage['start'] > 0 && $arrRootPage['start'] > time())
                    || ($arrRootPage['stop'] > 0 && $arrRootPage['stop'] < time())
                )
            ) {
                continue;
            }

            // Search for foreign language
            else
            {
                $addToNavigation = true;
                $active = false;
                $target = '';
                $arrTranslatedParams = $arrParams;

                if ($arrRootPage['language'] == $objRootPage->language) {
                    // If it is the active page, and we want to hide this, continue with the next page
                    if ($this->hideActiveLanguage) {
                        $addToNavigation = false;
                    }

                    $active = true;
                    $strCssClass = 'lang-' . $arrRootPage['language'];

                    // Add CSS class to the current page HTML
                    if (strpos($objPage->cssClass, $strCssClass) === false) {
                        $objPage->cssClass = trim($objPage->cssClass . ' ' . $strCssClass);
                    }
                }

                // HOOK: allow extensions to modify url parameters
                if (isset($GLOBALS['TL_HOOKS']['translateUrlParameters'])
                    && is_array($GLOBALS['TL_HOOKS']['translateUrlParameters'])
                ) {
                    foreach ($GLOBALS['TL_HOOKS']['translateUrlParameters'] as $callback) {
                        $arrTranslatedParams = System::importStatic($callback[0])->{$callback[1]}(
                            $arrTranslatedParams,
                            $arrRootPage['language'],
                            $arrRootPage,
                            $addToNavigation
                        );
                    }
                }

                $arrRequest = array();

                // Make sure $strParam is empty, otherwise previous pages could affect url
                $strParam = '';

                // Build the URL
                foreach ($arrTranslatedParams['url'] as $k => $v) {
                    if ($GLOBALS['TL_CONFIG']['useAutoItem'] && in_array($k, $GLOBALS['TL_AUTO_ITEM'], true)) {
                        if (isset($arrParams['url']['auto_item'])) {
                            continue;
                        }

                        $strParam .= '/' . $v;

                    } elseif ('auto_item' === $k) {
                        $strParam .= '/' . $v;
                    } else {
                        $strParam .= '/' . $k . '/' . $v;
                    }
                }

                // Append the query
                foreach ($arrTranslatedParams['get'] as $k => $v) {
                    $arrRequest[] = $k . '=' . $v;
                }

                if (array_key_exists($arrRootPage['language'], $arrLanguagePages)) {
                    // Matching language page found
                    $pageTitle = $arrLanguagePages[$arrRootPage['language']]['title'];
                    $href = Controller::generateFrontendUrl($arrLanguagePages[$arrRootPage['language']], $strParam, $arrRootPage['language']) . (count($arrRequest) ? ('?'.implode('&amp;', $arrRequest)) : '');

                    if ($arrLanguagePages[$arrRootPage['language']]['target']) {
                        $target = ' target="_blank"';
                    }

                } else {
                    // Step up in the current page trail until we find a page with valid languageMain
                    $blnDirectFallback = false;
                    $blnFound = false;
                    $arrTrail = $objPage->trail;

                    // last id in trail is the current page, we don't need to search that
                    for ($i = count($arrTrail) - 2; $i >= 0; $i--) {
                        if ($objRootPage->fallback) {
                            // Fallback tree, search for trail id
                            $objTrailPage = Database::getInstance()
                                ->prepare('SELECT * FROM tl_page WHERE id=? OR languageMain=?')
                                ->execute($arrTrail[$i], $arrTrail[$i])
                            ;

                        } else {
                            // not fallback tree, search for trail languageMain
                            $objTPage = Database::getInstance()
                                ->prepare('SELECT * FROM tl_page WHERE id=?')
                                ->execute($arrTrail[$i])
                            ;

                            // Basically impossible, but DB would throw exception
                            if (!$objTPage->numRows || $objTPage->languageMain == 0) {
                                continue;
                            }

                            $objTrailPage = Database::getInstance()
                                ->prepare('SELECT * FROM tl_page WHERE id=? OR languageMain=?')
                                ->execute($objTPage->languageMain, $objTPage->languageMain)
                            ;
                        }

                        if ($objTrailPage->numRows) {
                            while ($objTrailPage->next()) {
                                if ('root' === $objTrailPage->type) {
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

                        if ($blnFound) {
                            break;
                        }
                    }

                    // We found a trail page
                    if ($blnFound) {
                        $pageTitle = $objTrailPage->title;
                        $href = Controller::generateFrontendUrl($objTrailPage->row(), $strParam, $arrRootPage['language']);

                        if ($objTrailPage->target) {
                            $target = ' target="_blank"';
                        }
                    } else {
                        $pageTitle = $arrRootPage['title'];
                        $href = Controller::generateFrontendUrl($arrRootPage, null, $arrRootPage['language']);
                    }
                }
            }

            // Hide languages without direct fallback
            if ($this->hideNoFallback && !$blnDirectFallback) {
                $addToNavigation = false;
            }

            if ($addToNavigation) {
                $arrItems[$c] = array(
                    'isActive'  => $active,
                    'class'     => 'lang-' . $arrRootPage['language'] . ($blnDirectFallback ? '' : ' nofallback') . ($active ? ' active' : '') . ($c == 0 ? ' first' : '') . ($c == $count - 1 ? ' last' : ''),
                    'link'      => $this->getLabel($arrRootPage['language']),
                    'subitems'  => '',
                    'href'      => specialchars(($absoluteUrl ? $domain : '') . $href),
                    'pageTitle' => strip_tags($pageTitle),
                    'accesskey' => '',
                    'tabindex'  => '',
                    'nofollow'  => false,
                    'target'    => $target . ' hreflang="' . $arrRootPage['language'] . '" lang="' . $arrRootPage['language'] . '"',
                    'language'  => $arrRootPage['language'],
                );
            }

            if ($blnDirectFallback) {
                $GLOBALS['TL_HEAD'][] = '<link rel="alternate" hreflang="' . $arrRootPage['language'] . '" lang="' . $arrRootPage['language'] . '" href="' . specialchars($domain . $href) . '" title="' . specialchars($pageTitle, true) . '">';
            }

            $c++;
        }

        if ($c > 0) {
            if ($this->customLanguage) {
                usort($arrItems, array($this, 'orderByCustom'));
            }

            /** @var FrontendTemplate|object $objTemplate */
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

        $key1 = array_search(strtolower($a['language']), $arrCustom, true);
        $key2 = array_search(strtolower($b['language']), $arrCustom, true);

        return ($key1 < $key2) ? -1 : 1;
    }


    private function getLabel($strLanguage)
    {
        if ($this->customLanguage && strlen($this->customLanguageText[strtolower($strLanguage)])) {
            return Controller::replaceInsertTags($this->customLanguageText[strtolower($strLanguage)]);
        }

        return strtoupper($strLanguage);
    }
}

