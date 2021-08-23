<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\BackendView;

use Contao\Controller;
use Contao\Input;
use Contao\Model;
use Contao\PageModel;
use Haste\Util\Url;

class ParentChildViewListener extends AbstractViewListener
{
    /**
     * @var Model
     */
    private $current = false;

    /**
     * {@inheritdoc}
     */
    protected function isSupported()
    {
        return $this->getTable() === Input::get('table')
            && \in_array(Input::get('do'), ['calendar', 'faq', 'news'], true);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCurrentPage()
    {
        if (false === $this->current) {
            /** @var Model $class */
            $class = $this->getModelClass();

            $this->current = class_exists($class) ? $class::findByPk($this->dataContainer->id) : null;
        }

        if (null === $this->current) {
            return null;
        }

        $pageId = $this->current->pid ? $this->current->getRelated('pid')->jumpTo : $this->current->jumpTo;

        return PageModel::findWithDetails($pageId);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAvailableLanguages(PageModel $page)
    {
        $options = [];
        $masterRoot = $this->pageFinder->findMasterRootForPage($page);
        $parent = $this->hasParent() ? 'languageMain' : 'master';
        $id = $page->rootId === $masterRoot->id ? $this->current->id : $this->current->{$parent};

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
     * @throws \InvalidArgumentException
     */
    protected function doSwitchView($id): void
    {
        $url = Url::removeQueryString(['switchLanguage']);
        $url = Url::addQueryString('id='.$id, $url);

        Controller::redirect($url);
    }

    /**
     * Finds related item for a given page.
     *
     * @param int $id
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
                "($table.id=? OR $table.languageMain=?)",
            ];
        } else {
            $columns = [
                "$table.jumpTo=?",
                "$table.id!=?",
                "($table.id=? OR $table.master=?)",
            ];
        }

        return $class::findOneBy(
            $columns,
            [
                $page->id,
                $this->current->id,
                $id,
                $id,
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

        return !empty($GLOBALS['TL_DCA'][$table]['config']['ptable']);
    }
}
