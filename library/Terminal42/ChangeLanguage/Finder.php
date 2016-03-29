<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage;

use Contao\Database;
use Contao\FrontendUser;
use Contao\PageModel;

class Finder
{
    /**
     * Find the main language page associated with the given page ID or page object
     * @param Database\Result|int
     * @return array|false
     */
    public static function findMainLanguagePageForPage($objPage)
    {
        if (is_numeric($objPage)) {
            $objPage = PageModel::findWithDetails($objPage);
        }

        // If main langugae is not set, either this is the fallback or we have no fallback
        if (0 === (int) $objPage->languageMain) {
            return false;
        }

        $objMain = PageModel::findWithDetails($objPage->languageMain);

        // Check permission
        if (!static::checkPagePermission($objMain)) {
            return false;
        }

        return $objMain->row();
    }

    /**
     * Find the main language root page for a page ID or page object
     * @param Database_Result|int
     * @return array|false
     */
    public static function findMainLanguageRootForPage($objPage)
    {
        if (is_numeric($objPage)) {
            $objPage = PageModel::findWithDetails($objPage);
        }

        $arrRoot = self::findFallbackRootForDomain($objPage->domain);

        if ($arrRoot === false) {
            return false;
        }

        if ($arrRoot['languageRoot'] > 0) {
            $objRoot = PageModel::findWithDetails($arrRoot['languageRoot']);

            if (null === $objRoot || !static::checkPagePermission($objRoot)) {
                return false;
            }

            return $objRoot->row();

        } elseif ($arrRoot['fallback']) {
            return $arrRoot;
        }

        return false;
    }


    /**
     * Find all associated root pages for a given domain (use $objPage->domain)
     * @param string
     * @return array
     */
    public static function findLanguageRootsForDomain($strDomain)
    {
        $arrFallback = static::findFallbackRootForDomain($strDomain);

        if ($arrFallback === false) {
            return array();
        }

        $arrPages = array();
        $objPages = Database::getInstance()->prepare("SELECT DISTINCT * FROM tl_page WHERE type='root' AND (dns=? OR dns IN (SELECT dns FROM tl_page WHERE type='root' AND fallback='1' AND (id=? OR languageRoot=? OR (languageRoot>0 && languageRoot=?)))) ORDER BY sorting")->execute($arrFallback['dns'], $arrFallback['languageRoot'], $arrFallback['id'], $arrFallback['languageRoot']);

        while ($objPages->next()) {
            if (!static::checkPagePermission($objPages)) {
                continue;
            }

            $arrPages[$objPages->id] = $objPages->row();
        }

        return $arrPages;
    }


    /**
     * Find the fallback page for a given domain (use $objPage->domain)
     * @param string
     * @return array|false
     */
    public static function findFallbackRootForDomain($strDomain)
    {
        $objPage = Database::getInstance()->prepare("SELECT * FROM tl_page WHERE type='root' AND fallback='1' AND dns=?")
                                          ->limit(1)
                                          ->execute($strDomain);

        if (!$objPage->numRows || !static::checkPagePermission($objPage)) {
            return false;
        }

        return $objPage->row();
    }

    /**
     * Check the page permission
     *
     * @param PageModel|Database\Result $objPage
     *
     * @return bool
     */
    private static function checkPagePermission($objPage)
    {
        $objPage = PageModel::findWithDetails($objPage->id);

        if ($objPage->protected && true !== BE_USER_LOGGED_IN) {
            if (true !== FE_USER_LOGGED_IN) {
                return false;
            }

            $arrGroups = $objPage->groups;

            if (0 === count($arrGroups)) {
                return false;
            }

            if (count(array_intersect(FrontendUser::getInstance()->groups, $arrGroups)) < 0) {
                return false;
            }
        }

        return true;
    }
}

