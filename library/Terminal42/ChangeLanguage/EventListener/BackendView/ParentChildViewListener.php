<?php
/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\BackendView;

use Contao\Controller;
use Contao\Input;
use Contao\Model;
use Contao\PageModel;
use Haste\Util\Url;

class ParentChildViewListener extends AbstractViewListener
{
    /**
     * @var \Model
     */
    private $current = false;

    /**
     * @inheritdoc
     */
    protected function isSupported()
    {
        return $this->getTable() === Input::get('table');
    }

    /**
     * @inheritdoc
     */
    protected function getCurrentPage()
    {
        /** @var Model $class */
        $class = $this->getModelClass();

        if (false === $this->current) {
            $this->current = $class::findByPk($this->dataContainer->id);
        }

        if (null === $this->current) {
            return null;
        }

        if ($this->current->pid) {
            try {
                $parent = $this->current->getRelated('pid');
            } catch (\Exception $e) {
                $parent = null;
            }
        }

        $pageId = $parent ? $parent->jumpTo : $this->current->jumpTo;

        return PageModel::findWithDetails($pageId);
    }

    /**
     * @inheritdoc
     */
    protected function getAvailableLanguages(PageModel $page)
    {
        $options    = [];
        $masterRoot = $this->pageFinder->findMasterRootForPage($page);
        $parent     = $this->hasParent() ? 'languageMain' : 'master';
        $id         = $page->rootId === $masterRoot->id ? $this->current->id : $this->current->{$parent};

        foreach ($this->pageFinder->findAssociatedForPage($page, true) as $associated) {
            $associated->loadDetails();
            $model = $this->findRelatedForPageAndId($associated, $id);

            if (null !== $model) {
                $options[$model->id] = $this->getLanguageLabel($associated->language);
            }
        }

        return $options;
    }

    /**
     * @inheritdoc
     *
     * @throws \InvalidArgumentException
     */
    protected function doSwitchView($id)
    {
        $url = Url::removeQueryString(['switchLanguage']);
        $url = Url::addQueryString('id='.$id, $url);

        Controller::redirect($url);
    }

    /**
     * Finds related item for a given page.
     *
     * @param PageModel $page
     * @param int       $id
     *
     * @return Model|null
     */
    private function findRelatedForPageAndId(PageModel $page, $id)
    {
        /** @var Model $class */
        $class = $this->getModelClass();
        $table = $class::getTable();

        if ($this->hasParent()) {
            $ptable = $GLOBALS['TL_DCA'][$table]['config']['ptable'];
            $columns = [
                "$table.pid IN (SELECT id FROM $ptable WHERE jumpTo=?)",
                "$table.id!=?",
                "($table.id=? OR $table.languageMain=?)"
            ];
        } else {
            $columns = [
                "$table.jumpTo=?",
                "$table.id!=?",
                "($table.id=? OR $table.master=?)"
            ];
        }


        return $class::findOneBy(
            $columns,
            [
                $page->id,
                $this->current->id,
                $id,
                $id
            ]
        );
    }

    private function getModelClass()
    {
        Controller::loadDataContainer($this->getTable());

        return Model::getClassFromTable($GLOBALS['TL_DCA'][$this->getTable()]['config']['ptable']);
    }

    private function hasParent()
    {
        /** @var Model $class */
        $class = $this->getModelClass();
        $table = $class::getTable();

        Controller::loadDataContainer($table);

        return '' !== (string) $GLOBALS['TL_DCA'][$table]['config']['ptable'];
    }
}
