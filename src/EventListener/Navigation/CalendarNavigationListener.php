<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\Navigation;

use Contao\CalendarBundle\ContaoCalendarBundle;
use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\PageModel;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;

/**
 * Translate URL parameters for calendar events.
 *
 * @Hook("changelanguageNavigation")
 */
class CalendarNavigationListener extends AbstractNavigationListener
{
    public function __invoke(ChangelanguageNavigationEvent $event): void
    {
        if (!class_exists(ContaoCalendarBundle::class)) {
            return;
        }

        $this->onChangelanguageNavigation($event);
    }

    protected function getUrlKey(): string
    {
        return 'events';
    }

    protected function findCurrent(): ?CalendarEventsModel
    {
        if (!class_exists(ContaoCalendarBundle::class)) {
            return null;
        }

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

    protected function findPublishedBy(array $columns, array $values = [], array $options = [])
    {
        return CalendarEventsModel::findOneBy(
            $this->addPublishedConditions($columns, CalendarEventsModel::getTable()),
            $values,
            $options
        );
    }
}
