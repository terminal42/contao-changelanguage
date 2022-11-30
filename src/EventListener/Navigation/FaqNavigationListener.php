<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\Navigation;

use Contao\FaqCategoryModel;
use Contao\FaqModel;
use Contao\PageModel;

/**
 * Translate URL parameters for faq items.
 */
class FaqNavigationListener extends AbstractNavigationListener
{
    protected function getUrlKey(): string
    {
        return 'items';
    }

    protected function findCurrent(): ?FaqModel
    {
        $alias = $this->getAutoItem();

        if ('' === $alias) {
            return null;
        }

        /** @var PageModel $objPage */
        global $objPage;

        if (null === ($calendars = FaqCategoryModel::findBy('jumpTo', $objPage->id))) {
            return null;
        }

        return FaqModel::findPublishedByParentAndIdOrAlias($alias, $calendars->fetchEach('id'));
    }

    protected function findPublishedBy(array $columns, array $values = [], array $options = []): ?FaqModel
    {
        return FaqModel::findOneBy(
            $this->addPublishedConditions($columns, FaqModel::getTable(), false),
            $values,
            $options
        );
    }
}
