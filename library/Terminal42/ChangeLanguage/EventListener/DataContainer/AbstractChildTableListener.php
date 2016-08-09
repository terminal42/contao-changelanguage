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

use Contao\DataContainer;
use Contao\Model;
use Haste\Dca\PaletteManipulator;
use Terminal42\ChangeLanguage\EventListener\AbstractTableListener;

abstract class AbstractChildTableListener extends AbstractTableListener
{
    use LanguageMainTrait;

    public function register()
    {
        $GLOBALS['TL_DCA'][$this->table]['config']['onload_callback'][] = function (DataContainer $dc) {
            $this->onLoad($dc);
        };

        $this->addLanguageMainField();
    }

    public function onLoad(DataContainer $dc)
    {
        $action = \Input::get('act');

        if ('editAll' === $action || ('edit' === $action && $this->getModel($dc->id)->getRelated('pid')->master > 0)) {
            $this->addFieldsToPalettes();
        }
    }

    public function onLanguageMainOptions(DataContainer $dc)
    {
        if (($current = $this->getModel($dc->id)) === null
            || ($master = $current->getRelated('pid')->getRelated('master')) === null
        ) {
            return [];
        }

        /** @var Model $class */
        $class  = Model::getClassFromTable($this->table);
        $models = $class::findBy(
            [
                $this->table.'.pid=?',
                sprintf('%s.id NOT IN (SELECT languageMain FROM %s WHERE pid=? AND id!=?)', $this->table, $this->table),
            ],
            [$master->id, $current->pid, $current->id]
        );

        return $models instanceof Model\Collection ? $this->formatOptions($current, $models) : [];
    }

    protected function addFieldsToPalettes()
    {
        $GLOBALS['TL_DCA'][$this->table]['fields'][$this->getTitleField()]['eval']['tl_class'] = 'w50';

        $pm = PaletteManipulator::create()
            ->addField('languageMain', $this->getTitleField(), PaletteManipulator::POSITION_AFTER, 'title_legend')
        ;

        $palettes = array_diff(
            array_keys($GLOBALS['TL_DCA'][$this->table]['palettes']),
            ['__selector__']
        );

        foreach ($palettes as $palette) {
            $pm->applyToPalette($palette, $this->table);
        }
    }

    protected function getModel($id)
    {
        /** @var Model $class */
        $class = Model::getClassFromTable($this->table);

        return $class::findByPk($id);
    }

    /**
     * @return string
     */
    abstract protected function getTitleField();

    /**
     * @return string
     */
    abstract protected function getSorting();

    /**
     * @param Model            $current
     * @param Model\Collection $models
     *
     * @return array
     */
    abstract protected function formatOptions(Model $current, Model\Collection $models);
}
