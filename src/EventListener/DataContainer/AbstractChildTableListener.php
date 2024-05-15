<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use Contao\Input;
use Contao\Model;
use Contao\Model\Collection;
use Terminal42\ChangeLanguage\EventListener\AbstractTableListener;

abstract class AbstractChildTableListener extends AbstractTableListener
{
    use LanguageMainTrait;

    public function register(): void
    {
        $GLOBALS['TL_DCA'][$this->table]['config']['onload_callback'][] = function (DataContainer $dc): void {
            $this->onLoad($dc);
        };

        $this->addLanguageMainField();
    }

    public function onLoad(DataContainer $dc): void
    {
        $action = Input::get('act');

        if ('editAll' === $action || ('edit' === $action && null !== ($model = $this->getModel($dc->id)) && $model->getRelated('pid')->master > 0)) {
            $this->addFieldsToPalettes();
        }
    }

    /**
     * @return array
     */
    public function onLanguageMainOptions(DataContainer $dc)
    {
        try {
            if (
                null === ($current = $this->getModel($dc->id))
                || null === ($master = $current->getRelated('pid')->getRelated('master'))
            ) {
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }

        /** @var Model $class */
        $class = Model::getClassFromTable($this->table);
        $models = $class::findBy(
            [
                $this->table.'.pid=?',
                sprintf('%s.id NOT IN (SELECT languageMain FROM %s WHERE pid=? AND id!=?)', $this->table, $this->table),
            ],
            [$master->id, $current->pid, $current->id],
        );

        return $models instanceof Collection ? $this->formatOptions($current, $models) : [];
    }

    protected function addFieldsToPalettes(): void
    {
        $GLOBALS['TL_DCA'][$this->table]['fields'][$this->getTitleField()]['eval']['tl_class'] = 'w50';

        $pm = PaletteManipulator::create()
            ->addField('languageMain', $this->getTitleField(), PaletteManipulator::POSITION_AFTER, 'title_legend')
        ;

        $palettes = array_diff(
            array_keys($GLOBALS['TL_DCA'][$this->table]['palettes']),
            ['__selector__'],
        );

        foreach ($palettes as $palette) {
            $pm->applyToPalette($palette, $this->table);
        }
    }

    /**
     * @param int|string $id
     *
     * @return Model
     */
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
     * @param Collection<Model> $models
     *
     * @return array
     */
    abstract protected function formatOptions(Model $current, Collection $models);
}
