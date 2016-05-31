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
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\ModuleLoader;
use Contao\PageModel;
use Haste\Dca\PaletteManipulator;
use Terminal42\ChangeLanguage\Finder;

class Page
{
    /**
     * Inject fields if appropriate.
     *
     * @param DataContainer $dc
     */
    public function showSelectbox($dc)
    {
        if ('edit' === Input::get('act')) {
            $objPage = PageModel::findWithDetails($dc->id);

            if ('root' === $objPage->type && $objPage->fallback) {
                PaletteManipulator::create()
                    ->addField('languageRoot', 'fallback')
                    ->applyToPalette('root', 'tl_page')
                ;
            } elseif ('root' !== $objPage->type) {
                $objRootPage = Database::getInstance()
                    ->prepare('SELECT * FROM tl_page WHERE id=?')
                    ->execute($objPage->rootId)
                ;

                $objFallback = Database::getInstance()
                    ->prepare("SELECT id FROM tl_page WHERE type='root' AND fallback='1' AND id!=? AND (dns=? OR id=?)")
                    ->limit(1)
                    ->execute(
                        $objRootPage->id,
                        $objPage->domain,
                        ($objRootPage->fallback ? $objRootPage->languageRoot : 0)
                    )
                ;

                if ($objFallback->numRows) {
                    PaletteManipulator::create()
                        ->addLegend('language_legend', 'title_legend')
                        ->addField('languageMain', 'language_legend', PaletteManipulator::POSITION_APPEND)
                        ->applyToPalette($objPage->type, 'tl_page')
                    ;
                }
            }

        } elseif ('editAll' === Input::get('act')) {
            foreach ($GLOBALS['TL_DCA']['tl_page']['palettes'] as $name => &$palette) {
                $pm = PaletteManipulator::create()
                    ->addLegend('language_legend', 'title_legend')
                    ->addField('languageMain', 'language_legend', PaletteManipulator::POSITION_APPEND)
                ;

                if ('__selector__' === $name || 'root' === $name) {
                    continue;
                }

                $pm->applyToPalette($palette, 'tl_page');
            }
        }
    }

    /**
     * Reset fallback with other callbacks
     *
     * @param DataContainer $dc
     */
    public function resetFallbackAll($dc)
    {
        $this->resetFallback($dc->id);
    }

    /**
     * Reset fallback with oncopy_callback
     *
     * @param int
     */
    public function resetFallbackCopy($intId)
    {
        $this->resetFallback($intId);
    }

    /**
     * Reset the language main when the fallback is deleted
     *
     * @param DataContainer $dc
     */
    public function resetLanguageMain($dc)
    {
        $arrIds   = Database::getInstance()->getChildRecords($dc->id, 'tl_page');
        $arrIds[] = $dc->id;

        Database::getInstance()->execute(
            'UPDATE tl_page SET languageMain=0 WHERE languageMain IN (' . implode(',', $arrIds) . ')'
        );
    }

    /**
     * Show notice if no fallback page is set
     *
     * @param array              $row
     * @param string             $label
     * @param DataContainer|null $dc
     * @param string             $imageAttribute
     * @param bool               $blnReturnImage
     * @param bool               $blnProtected
     *
     * @return string
     */
    public function addFallbackNotice(
        $row,
        $label,
        $dc = null,
        $imageAttribute = '',
        $blnReturnImage = false,
        $blnProtected = false
    ) {
        $label = Backend::addPageIcon($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);

        if (!$row['languageMain']) {
            // Skip if we are not a regular page
            if ('root' === $row['type'] || 'folder' === $row['type']) {
                return $label;
            }

            $objPage = PageModel::findWithDetails($row['id']);

            $objRootPage = Database::getInstance()
                ->prepare('SELECT * FROM tl_page WHERE id=?')
                ->execute($objPage->rootId)
            ;

            $objFallback = Database::getInstance()
                ->prepare("SELECT id FROM tl_page WHERE type='root' AND fallback='1' AND id!=? AND (dns=? OR id=?)")
                ->limit(1)
                ->execute($objRootPage->id, $objPage->domain, ($objRootPage->fallback ? $objRootPage->languageRoot : 0))
            ;

            if ($objFallback->numRows) {
                $label .= '<span style="color:#b3b3b3; padding-left:3px;">[' . $GLOBALS['TL_LANG']['MSC']['noMainLanguage'] . ']</span>';
            }
        }

        return $label;
    }

    /**
     * Return all fallback pages for the current page (used as options_callback).
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getFallbackPages($dc)
    {
        $arrPages = array();
        $arrRoot = Finder::findMainLanguageRootForPage($dc->id);

        if (false !== $arrRoot) {
            $this->generatePageOptions($arrPages, $arrRoot['id'], 0);
        }

        return $arrPages;
    }

    /**
     * Get alternative fallback root pages
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getRootPages($dc)
    {
        $arrPages = array();
        $objPages = Database::getInstance()
            ->prepare(
                "SELECT * FROM tl_page WHERE type='root' AND fallback='1' AND languageRoot=0 AND id!=?"
            )
            ->execute($dc->id)
        ;

        while ($objPages->next()) {
            $arrPages[$objPages->id] = sprintf(
                '%s%s [%s]',
                $objPages->title,
                (strlen($objPages->dns) ? (' (' . $objPages->dns . ')') : ''),
                $objPages->language
            );
        }

        return $arrPages;
    }

    /**
     * Generates a list of all subpages
     *
     * @param array $arrPages
     * @param int   $intId
     * @param int   $level
     */
    private function generatePageOptions(&$arrPages, $intId = 0, $level = -1)
    {
        // Add child pages
        $objPages = Database::getInstance()
            ->prepare("
                SELECT id, title 
                FROM tl_page 
                WHERE pid=? AND type != 'root' AND type != 'error_403' AND type != 'error_404' 
                ORDER BY sorting
            ")
            ->execute($intId)
        ;

        if ($objPages->numRows < 1) {
            return;
        }

        ++$level;

        while ($objPages->next()) {
            $arrPages[$objPages->id] = str_repeat('&nbsp;', 3 * $level) . $objPages->title;

            $this->generatePageOptions($arrPages, $objPages->id, $level);
        }
    }

    /**
     * Reset the fallback assignment if it's moved to the fallback root
     *
     * @param int $intId
     */
    private function resetFallback($intId)
    {
        $objPage = \PageModel::findWithDetails($intId);
        $objRoot = \PageModel::findByPk($objPage->rootId);

        if ($objRoot->fallback) {
            $arrSubpages   = Database::getInstance()->getChildRecords($objPage->id, 'tl_page');
            $arrSubpages[] = $objPage->id;

            Database::getInstance()->execute(
                'UPDATE tl_page SET languageMain=0 WHERE id IN(' . implode(',', $arrSubpages) . ')'
            );
        }
    }
}
