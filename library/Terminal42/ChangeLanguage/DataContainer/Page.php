<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\DataContainer;

use Contao\Backend;

class Page extends Backend
{

    /**
     * Inject fields if appropriate.
     *
     * @access public
     * @return void
     */
    public function showSelectbox($dc)
    {
        if (\Input::get('act') == 'edit')
        {
            $objPage = $this->getPageDetails($dc->id);

            if ($objPage->type == 'root' && $objPage->fallback)
            {
                $GLOBALS['TL_DCA']['tl_page']['palettes']['root'] = preg_replace('@([,|;]fallback)([,|;])@','$1,languageRoot$2', $GLOBALS['TL_DCA']['tl_page']['palettes']['root']);
            }
            elseif ($objPage->type != 'root')
            {
                $objRootPage = $this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->limit(1)->execute($objPage->rootId);

                $objFallback = $this->Database->prepare("SELECT id FROM tl_page WHERE type='root' AND fallback='1' AND id!=? AND (dns=? OR id=?)")->limit(1)->execute($objRootPage->id, $objPage->domain, ($objRootPage->fallback ? $objRootPage->languageRoot : 0));

                if($objFallback->numRows)
                {
                    $GLOBALS['TL_DCA']['tl_page']['palettes'][$objPage->type] = str_replace('type;', 'type;{language_legend},languageMain;', $GLOBALS['TL_DCA']['tl_page']['palettes'][$objPage->type]);
                }
            }
        }
        elseif (\Input::get('act') == 'editAll')
        {
            foreach( $GLOBALS['TL_DCA']['tl_page']['palettes'] as $name => $palette )
            {
                if ($name == '__selector__' || $name == 'root')
                    continue;

                $GLOBALS['TL_DCA']['tl_page']['palettes'][$name] = str_replace('type;', 'type;{language_legend},languageMain;', $palette);
            }
        }
    }


    /**
     * Reset the fallback assignment if it's moved to the fallback root
     * @param integer
     */
    public function resetFallback($intId)
    {
        $objPage = \PageModel::findWithDetails($intId);
        $objRoot = \PageModel::findByPk($objPage->rootId);

        if ($objRoot->fallback)
        {
            $arrSubpages = $this->Database->getChildRecords($objPage->id, 'tl_page', true);
            $arrSubpages[] = $objPage->id;
            $this->Database->execute("UPDATE tl_page SET languageMain=0 WHERE id IN(" . implode(',', $arrSubpages) . ")");
        }
    }


    /**
     * Reset fallback with other callbacks
     * @param object
     */
    public function resetFallbackAll($dc)
    {
        $this->resetFallback($dc->id);
    }


    /**
     * Reset fallback with oncopy_callback
     * @param integer
     */
    public function resetFallbackCopy($intId)
    {
        $this->resetFallback($intId);
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
            $objPage = new \tl_page_cacheicon();
            $label = $objPage->addImage($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);
        }
        elseif (in_array('Avisota', $this->Config->getActiveModules()))
        {
            $objPage = new \tl_page_avisota();
            $label = $objPage->addIcon($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);
        }
        else
        {
            $objPage = new \tl_page();
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
        $objPages = $this->Database->prepare("SELECT * FROM tl_page WHERE type='root' AND fallback='1' AND languageRoot=0 AND id!=?")->execute($dc->id);

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
        $objPages = $this->Database->prepare("SELECT id, title FROM tl_page WHERE pid=? AND type != 'root' AND type != 'error_403' AND type != 'error_404' ORDER BY sorting")
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

