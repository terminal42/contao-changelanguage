<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Database;
use Contao\DataContainer;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Terminal42\ChangeLanguage\PageFinder;

/**
 * @Hook("loadDataContainer")
 */
class PageOperationListener
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(string $table): void
    {
        if ('tl_page' !== $table) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_page']['config']['oncopy_callback'][] = fn (...$args) => $this->onCopy(...$args);
        $GLOBALS['TL_DCA']['tl_page']['config']['oncut_callback'][] = fn (...$args) => $this->onCut(...$args);
        $GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][] = fn (...$args) => $this->onSubmit(...$args);
        $GLOBALS['TL_DCA']['tl_page']['config']['ondelete_callback'][] = fn (...$args) => $this->onDelete(...$args);
        $GLOBALS['TL_DCA']['tl_page']['config']['onundo_callback'][] = fn ($table, array $row) => $this->onUndo($row);
    }

    /**
     * Handles submitting a page and resets tl_page.languageMain if necessary.
     */
    private function onSubmit(DataContainer $dc): void
    {
        if (
            'root' === $dc->activeRecord->type
            && $dc->activeRecord->fallback
            && (!$dc->activeRecord->languageRoot || null === PageModel::findByPk($dc->activeRecord->languageRoot))
        ) {
            $this->resetPageAndChildren((int) $dc->id);
        }
    }

    /**
     * Handles copying a page and resets tl_page.languageMain if necessary.
     *
     * @param int $insertId
     */
    private function onCopy($insertId): void
    {
        $this->validateLanguageMainForPage((int) $insertId);
    }

    /**
     * Handles moving a page and resets tl_page.languageMain if necessary.
     */
    private function onCut(DataContainer $dc): void
    {
        $this->validateLanguageMainForPage((int) $dc->id);
    }

    /**
     * Handles deleting a page and resets tl_page.languageMain if necessary.
     */
    private function onDelete(DataContainer $dc): void
    {
        $this->resetPageAndChildren((int) $dc->id);
    }

    /**
     * Handles undo of a deleted page and resets tl_page.languageMain if necessary.
     */
    private function onUndo(array $row): void
    {
        $this->validateLanguageMainForPage((int) $row['id']);
    }

    private function validateLanguageMainForPage(int $pageId): void
    {
        $page = PageModel::findWithDetails($pageId);

        // Moving a root page does not affect language assignments
        if (null === $page || !$page->languageMain || 'root' === $page->type) {
            return;
        }

        $duplicates = PageModel::countBy(
            [
                'id IN ('.implode(',', Database::getInstance()->getChildRecords($page->rootId, 'tl_page')).')',
                'languageMain=?',
                'id!=?',
            ],
            [$page->languageMain, $page->id],
        );

        // Reset languageMain if another page in the new page tree has the same languageMain
        if ($duplicates > 0) {
            $this->resetPageAndChildren((int) $page->id);

            return;
        }

        $pageFinder = new PageFinder();
        $masterRoot = $pageFinder->findMasterRootForPage($page);

        // Reset languageMain if current tree has no master or if it's the master tree
        if (null === $masterRoot || $masterRoot->id === $page->rootId) {
            $this->resetPageAndChildren((int) $page->id);

            return;
        }

        // Reset languageMain if the current value is not a valid ID of the master tree
        if (!\in_array($page->languageMain, Database::getInstance()->getChildRecords($masterRoot->id, 'tl_page'), false)) {
            $this->connection->update('tl_page', ['languageMain' => 0], ['id' => $page->id]);
        }
    }

    private function resetPageAndChildren(int $pageId): void
    {
        $resetIds = Database::getInstance()->getChildRecords($pageId, 'tl_page');
        $resetIds[] = $pageId;

        $this->connection->executeStatement(
            'UPDATE tl_page SET languageMain = 0 WHERE id IN (?)',
            [$resetIds],
            [Connection::PARAM_INT_ARRAY],
        );
    }
}
