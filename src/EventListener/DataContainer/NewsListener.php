<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;

class NewsListener extends AbstractChildTableListener
{
    protected function getTitleField(): string
    {
        return 'headline';
    }

    protected function getSorting(): string
    {
        return 'date DESC, time DESC';
    }

    protected function formatOptions(Model $current, Collection $models): array
    {
        $sameDay = $GLOBALS['TL_LANG']['tl_news']['sameDay'];
        $otherDay = $GLOBALS['TL_LANG']['tl_news']['otherDay'];
        $dayBegin = strtotime('0:00', (int) $current->date);
        $options = [$sameDay => [], $otherDay => []];

        foreach ($models as $model) {
            $group = strtotime('0:00', (int) $model->date) === $dayBegin ? $sameDay : $otherDay;

            $options[$group][$model->id] = sprintf(
                '%s [%s]',
                $model->headline,
                Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $model->time)
            );
        }

        return $options;
    }
}
