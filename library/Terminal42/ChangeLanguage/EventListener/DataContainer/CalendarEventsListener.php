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
