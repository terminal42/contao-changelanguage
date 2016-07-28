<?php
/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\FaqModel;
use Contao\Model;

class FaqListener extends AbstractChildTableListener
{
    /**
     * @inheritdoc
     */
    protected function getTitleField()
    {
        return 'question';
    }

    /**
     * @inheritdoc
     */
    protected function getSorting()
    {
        return 'sorting';
    }

    /**
     * @inheritdoc
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
