<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\FaqModel;
use Contao\Model;
use Contao\Model\Collection;

class FaqListener extends AbstractChildTableListener
{
    protected function getTitleField(): string
    {
        return 'question';
    }

    protected function getSorting(): string
    {
        return 'sorting';
    }

    /**
     * @param Collection<FaqModel> $models
     */
    protected function formatOptions(Model $current, Collection $models): array
    {
        $options = [];

        foreach ($models as $model) {
            $options[$model->id] = sprintf('%s [ID %s]', $model->question, $model->id);
        }

        return $options;
    }
}
