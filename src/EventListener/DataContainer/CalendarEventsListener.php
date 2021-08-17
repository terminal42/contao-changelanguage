<?php

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\CalendarEventsModel;
use Contao\Date;
use Contao\Model;

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
     * @param CalendarEventsModel   $current
     * @param CalendarEventsModel[] $models
     */
    protected function formatOptions(Model $current, Model\Collection $models)
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
