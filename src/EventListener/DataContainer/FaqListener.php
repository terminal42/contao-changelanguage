<?php

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\FaqModel;
use Contao\Model;

class FaqListener extends AbstractChildTableListener
{
    /**
     * {@inheritdoc}
     */
    protected function getTitleField()
    {
        return 'question';
    }

    /**
     * {@inheritdoc}
     */
    protected function getSorting()
    {
        return 'sorting';
    }

    /**
     * {@inheritdoc}
     *
     * @param FaqModel   $current
     * @param FaqModel[] $models
     */
    protected function formatOptions(Model $current, Model\Collection $models)
    {
        $options = [];

        foreach ($models as $model) {
            $options[$model->id] = sprintf('%s [ID %s]', $model->question, $model->id);
        }

        return $options;
    }
}
