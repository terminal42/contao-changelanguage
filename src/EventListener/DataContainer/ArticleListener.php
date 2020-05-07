<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2019, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\ArticleModel;
use Contao\Database;
use Contao\DataContainer;
use Contao\PageModel;
use Haste\Dca\PaletteManipulator;
use Terminal42\ChangeLanguage\EventListener\AbstractTableListener;
use Terminal42\ChangeLanguage\PageFinder;

class ArticleListener extends AbstractTableListener
{
    use LanguageMainTrait;

    public function register()
    {
        $GLOBALS['TL_DCA'][$this->table]['config']['onload_callback'][] = function (DataContainer $dc) {
            $this->onLoad($dc);
        };

        $this->addLanguageMainField();
    }

    public function onLoad(DataContainer $dc)
    {
        $action = \Input::get('act');

        if ('editAll' === $action) {
            $this->addFieldsToPalettes();
        } elseif ('edit' === $action) {
            $pageFinder = new PageFinder();
            $article = ArticleModel::findByPk($dc->id);
            $page = PageModel::findByPk($article->pid);

            if (null !== $page && null !== $pageFinder->findAssociatedInMaster($page)) {
                $this->addFieldsToPalettes();
            }
        }
    }

    public function onLanguageMainOptions(DataContainer $dc)
    {
        $pageFinder = new PageFinder();
        $current = ArticleModel::findByPk($dc->id);
        $page = PageModel::findByPk($current->pid);

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

    private function addFieldsToPalettes()
    {
        $GLOBALS['TL_DCA'][$this->table]['fields']['title']['eval']['tl_class'] = 'w50';

        PaletteManipulator::create()
            ->addField('languageMain', 'title', PaletteManipulator::POSITION_AFTER, 'title_legend')
            ->applyToPalette('default', 'tl_article')
        ;
    }
}
