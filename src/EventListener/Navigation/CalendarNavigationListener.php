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

    protected function findPublishedBy(array $columns, array $values = [], array $options = [])
    {
        return CalendarEventsModel::findOneBy(
            $this->addPublishedConditions($columns, CalendarEventsModel::getTable()),
            $values,
            $options
        );
    }

    protected function setTitles($event, $translated): void
    {
        $event->getNavigationItem()->setTitle($translated->title);
        $event->getNavigationItem()->setPageTitle($translated->pageTitle);
    }
}
