<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\Navigation;

use Contao\FaqCategoryModel;
use Contao\FaqModel;
use Contao\Input;

/**
 * Translate URL parameters for faq items.
 */
class FaqNavigationListener extends AbstractNavigationListener
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

        if (null === ($calendars = FaqCategoryModel::findBy('jumpTo', $objPage->id))) {
            return null;
        }

        return FaqModel::findPublishedByParentAndIdOrAlias($alias, $calendars->fetchEach('id'));
    }

    /**
     * {@inheritdoc}
     */
    protected function findPublishedBy(array $columns, array $values = [], array $options = [])
    {
        return FaqModel::findOneBy(
            $this->addPublishedConditions($columns, FaqModel::getTable(), false),
            $values,
            $options
        );
    }
}
