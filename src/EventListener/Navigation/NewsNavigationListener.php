<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\Navigation;

use Contao\Input;
use Contao\NewsArchiveModel;
use Contao\NewsModel;

/**
 * Translate URL parameters for news items.
 */
class NewsNavigationListener extends AbstractNavigationListener
{
    /**
     * {@inheritdoc}
     */
    protected function getUrlKey()
    {
        return 'items';
    }

    /**
     * {@inheritdoc}
     */
    protected function findCurrent()
    {
        $alias = (string) Input::get($this->getUrlKey(), false, true);

        if ('' === $alias) {
            return null;
        }

        /** @var PageModel $objPage */
        global $objPage;

        if (null === ($archives = NewsArchiveModel::findBy('jumpTo', $objPage->id))) {
            return null;
        }

        // Fix Contao bug that returns a collection (see contao-changelanguage#71)
        $options = ['limit' => 1, 'return' => 'Model'];

        return NewsModel::findPublishedByParentAndIdOrAlias($alias, $archives->fetchEach('id'), $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function findPublishedBy(array $columns, array $values = [], array $options = [])
    {
        return NewsModel::findOneBy(
            $this->addPublishedConditions($columns, NewsModel::getTable()),
            $values,
            $options
        );
    }
}
