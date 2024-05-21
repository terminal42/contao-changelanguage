<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\ArticleModel;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\PageModel;
use Terminal42\ChangeLanguage\EventListener\AbstractTableListener;
use Terminal42\ChangeLanguage\PageFinder;

class ArticleListener extends AbstractTableListener
{
    use LanguageMainTrait;

    public function register(): void
    {
        $GLOBALS['TL_DCA'][$this->table]['config']['onload_callback'][] = function (DataContainer $dc): void {
            $this->onLoad($dc);
        };

        $this->addLanguageMainField();
    }

    public function onLoad(DataContainer $dc): void
    {
        $action = Input::get('act');

        if ('editAll' === $action) {
            $this->addFieldsToPalettes();
        } elseif ('edit' === $action) {
            $article = ArticleModel::findById($dc->id);

            if (null !== $article) {
                $page = PageModel::findById($article->pid);

                if (null !== $page && null !== (new PageFinder())->findAssociatedInMaster($page)) {
                    $this->addFieldsToPalettes();
                }
            }
        }
    }

    public function onLanguageMainOptions(DataContainer $dc): array
    {
        $pageFinder = new PageFinder();
        $current = ArticleModel::findById($dc->id);
        $page = PageModel::findById($current->pid);

        if (null === $page || null === ($master = $pageFinder->findAssociatedInMaster($page))) {
            return [];
        }

        $options = [];
        $result = Database::getInstance()
            ->prepare('
                SELECT id, title
                FROM tl_article
                WHERE pid=? AND id NOT IN (
                    SELECT languageMain FROM tl_article WHERE id!=? AND pid=? AND languageMain > 0
                )
            ')
            ->execute($master->id, $current->id, $page->id)
        ;

        while ($result->next()) {
            $options[$result->id] = sprintf('%s [ID %s]', $result->title, $result->id);
        }

        return $options;
    }

    private function addFieldsToPalettes(): void
    {
        $GLOBALS['TL_DCA'][$this->table]['fields']['title']['eval']['tl_class'] = 'w50';

        PaletteManipulator::create()
            ->addField('languageMain', 'title', PaletteManipulator::POSITION_AFTER, 'title_legend')
            ->applyToPalette('default', 'tl_article')
        ;
    }
}
