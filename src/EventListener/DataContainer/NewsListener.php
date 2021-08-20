<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\Date;
use Contao\Model;
use Contao\Model\Collection;
use Contao\NewsModel;

class NewsListener extends AbstractChildTableListener
{
    /**
     * {@inheritdoc}
     */
    protected function getTitleField()
    {
        return 'headline';
    }

    /**
     * {@inheritdoc}
     */
    protected function getSorting()
    {
        return 'date DESC, time DESC';
    }

    /**
     * {@inheritdoc}
     *
     * @param NewsModel             $current
     * @param Collection<NewsModel> $models
     */
    protected function formatOptions(Model $current, Collection $models)
    {
        $sameDay = $GLOBALS['TL_LANG']['tl_news']['sameDay'];
        $otherDay = $GLOBALS['TL_LANG']['tl_news']['otherDay'];
        $dayBegin = strtotime('0:00', (int) $current->date);
        $options = [$sameDay => [], $otherDay => []];

        foreach ($models as $model) {
            $group = strtotime('0:00', $model->date) === $dayBegin ? $sameDay : $otherDay;

            $options[$group][$model->id] = sprintf(
                '%s [%s]',
                $model->headline,
                Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $model->time)
            );
        }

        return $options;
    }
}
