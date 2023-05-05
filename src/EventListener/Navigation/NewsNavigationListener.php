<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\Navigation;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\NewsArchiveModel;
use Contao\NewsBundle\ContaoNewsBundle;
use Contao\NewsModel;
use Contao\PageModel;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;

/**
 * Translate URL parameters for news items.
 *
 * @Hook("changelanguageNavigation")
 */
class NewsNavigationListener extends AbstractNavigationListener
{
    protected function getUrlKey(): string
    {
        return isset($GLOBALS['TL_CONFIG']['useAutoItem']) ? 'items' : 'auto_item';
    }

    protected function findCurrent(): ?NewsModel
    {
        $alias = $this->getAutoItem();

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

    protected function findPublishedBy(array $columns, array $values = [], array $options = []): ?NewsModel
    {
        return NewsModel::findOneBy(
            $this->addPublishedConditions($columns, NewsModel::getTable()),
            $values,
            $options
        );
    }
}
