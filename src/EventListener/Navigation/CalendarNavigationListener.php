<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\Navigation;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Model;
use Contao\PageModel;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;

/**
 * Translate URL parameters for calendar events.
 *
 * @Hook("changelanguageNavigation")
 */
class CalendarNavigationListener extends AbstractNavigationListener implements NavigationHandlerInterface
{
    /**
     * @param CalendarEventsModel $model
     */
    public function handleNavigation(ChangelanguageNavigationEvent $event, Model $model): void
    {
        $event->getUrlParameterBag()->setUrlAttribute($this->getUrlKey(), $model->alias ?: $model->id);
        $event->getNavigationItem()->setTitle($model->title);
        $event->getNavigationItem()->setPageTitle($model->pageTitle);
    }

    protected function getUrlKey(): string
    {
        return isset($GLOBALS['TL_CONFIG']['useAutoItem']) ? 'events' : 'auto_item';
    }

    protected function findCurrent(): ?CalendarEventsModel
    {
        $alias = $this->getAutoItem();

        if ('' === $alias) {
            return null;
        }

        /** @var PageModel $objPage */
        global $objPage;

        if (null === ($calendars = CalendarModel::findBy('jumpTo', $objPage->id))) {
            return null;
        }

        return CalendarEventsModel::findPublishedByParentAndIdOrAlias($alias, $calendars->fetchEach('id'));
    }

    /**
     * @param array<string> $columns
     * @param array<string> $values
     * @param array<string, string> $options
     */
    protected function findPublishedBy(array $columns, array $values = [], array $options = [])
    {
        return CalendarEventsModel::findOneBy(
            $this->addPublishedConditions($columns, CalendarEventsModel::getTable()),
            $values,
            $options,
        );
    }
}
