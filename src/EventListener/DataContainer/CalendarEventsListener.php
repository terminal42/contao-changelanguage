<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;

class CalendarEventsListener extends AbstractChildTableListener
{
    protected function getTitleField(): string
    {
        return 'title';
    }

    protected function getSorting(): string
    {
        return 'startTime DESC';
    }

    /**
     * @return array<int|string, string>
     */
    protected function formatOptions(Model $current, Collection $models): array
    {
        $options = [];

        foreach ($models as $model) {
            $options[$model->id] = \sprintf(
                '%s [%s]',
                $model->title,
                Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $model->startTime),
            );
        }

        return $options;
    }
}
