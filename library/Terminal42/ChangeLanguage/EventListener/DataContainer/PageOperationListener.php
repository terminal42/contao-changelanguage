<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\Database;
use Contao\DataContainer;
use Contao\PageModel;
use Terminal42\ChangeLanguage\PageFinder;

class PageOperationListener
{
    public function register()
    {
        $GLOBALS['TL_DCA']['tl_page']['config']['oncopy_callback'][]   = $this->selfCallback('onCopy');
        $GLOBALS['TL_DCA']['tl_page']['config']['oncut_callback'][]    = $this->selfCallback('onCut');
        $GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][] = $this->selfCallback('onSubmit');
        $GLOBALS['TL_DCA']['tl_page']['config']['ondelete_callback'][] = $this->selfCallback('onDelete');
        $GLOBALS['TL_DCA']['tl_page']['config']['onundo_callback'][]   = $this->selfCallback('onUndo');
    }

    /**
     * Handles submitting a page and resets tl_page.languageMain if necessary.
     *
     * @param DataContainer $dc
     */
    public function onSubmit(DataContainer $dc)
    {
        if ('root' === $dc->activeRecord->type
            && $dc->activeRecord->fallback
            && (!$dc->activeRecord->languageRoot || (PageModel::findByPk($dc->activeRecord->languageRoot)) === null)
        ) {
            $this->resetPageAndChildren($dc->id);
        }
    }

    /**
     * Handles copying a page and resets tl_page.languageMain if necessary.
     *
     * @param int $insertId
     */
    public function onCopy($insertId)
    {
        $this->validateLanguageMainForPage($insertId);
    }

    /**
     * Handles moving a page and resets tl_page.languageMain if necessary.
     *
     * @param DataContainer $dc
     */
    public function onCut(DataContainer $dc)
    {
        $this->validateLanguageMainForPage($dc->id);
    }

    /**
     * Handles deleting a page and resets tl_page.languageMain if necessary.
     *
     * @param DataContainer $dc
     */
    public function onDelete(DataContainer $dc)
    {
        $this->resetPageAndChildren($dc->id);
    }

    /**
     * Handles undo of a deleted page and resets tl_page.languageMain if necessary.
     *
     * @param string $table
     * @param array  $row
     */
    public function onUndo($table, array $row)
    {
        $this->validateLanguageMainForPage($row['id']);
    }

    private function validateLanguageMainForPage($pageId)
    {
        $page = PageModel::findWithDetails($pageId);

        // Moving a root page does not affect language assignments
        if (null === $page || !$page->languageMain || 'root' === $page->type) {
            return;
        }

        $duplicates = PageModel::countBy(
            [
                'id IN (' . implode(',', Database::getInstance()->getChildRecords($page->rootId, 'tl_page')) . ')',
                'languageMain=?',
                'id!=?'
            ],
            [$page->languageMain, $page->id]
        );

        // Reset languageMain if another page in the new page tree has the same languageMain
        if ($duplicates > 0) {
            $this->resetPageAndChildren($page->id);
            return;
        }

        $pageFinder = new PageFinder();
        $masterRoot = $pageFinder->findMasterRootForPage($page);

        // Reset languageMain if current tree has no master or if it's the master tree
        if (null === $masterRoot || $masterRoot->id === $page->rootId) {
            $this->resetPageAndChildren($page->id);
            return;
        }

        // Reset languageMain if the current value is not a valid ID of the master tree
        if (!in_array($page->id, Database::getInstance()->getChildRecords($masterRoot->id, 'tl_page'), false)) {
            $this->resetPageAndChildren($page->id);
        }
    }

    private function resetPageAndChildren($pageId)
    {
        $resetIds   = Database::getInstance()->getChildRecords($pageId, 'tl_page');
        $resetIds[] = $pageId;

        Database::getInstance()->query(
            'UPDATE tl_page SET languageMain=0 WHERE id IN (' . implode(',', $resetIds) . ')'
        );
    }

    private function selfCallback($method)
    {
        return function () use ($method) {
            return call_user_func_array(
                [$this, $method],
                func_get_args()
            );
        };
    }
}
