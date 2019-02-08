<?php

/*
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2019, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\Date;
use Contao\Model;
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
     * @param NewsModel   $current
     * @param NewsModel[] $models
     */
    protected function formatOptions(Model $current, Model\Collection $models)
    {
        $sameDay = $GLOBALS['TL_LANG']['tl_news']['sameDay'];
        $otherDay = $GLOBALS['TL_LANG']['tl_news']['otherDay'];
        $dayBegin = strtotime('0:00', $current->date);
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
