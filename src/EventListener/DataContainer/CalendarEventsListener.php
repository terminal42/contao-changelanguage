<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\CalendarEventsModel;
use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;

class CalendarEventsListener extends AbstractChildTableListener
{
    /**
     * {@inheritdoc}
     */
    protected function getTitleField()
    {
        return 'title';
    }

    /**
     * {@inheritdoc}
     */
    protected function getSorting()
    {
        return 'startTime DESC';
    }

    /**
     * {@inheritdoc}
     *
     * @param CalendarEventsModel             $current
     * @param Collection<CalendarEventsModel> $models
     */
    protected function formatOptions(Model $current, Collection $models)
    {
        $options = [];

        foreach ($models as $model) {
            $options[$model->id] = sprintf(
                '%s [%s]',
                $model->title,
                Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $model->startTime)
            );
        }

        return $options;
    }
}
