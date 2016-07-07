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

use Contao\BackendUser;
use Contao\Database;
use Contao\DataContainer;
use Contao\System;
use Haste\Dca\PaletteManipulator;

class ParentTableListener
{
    private $table;

    /**
     * Constructor.
     *
     * @param $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    public function onLoad(DataContainer $dc)
    {
        if (null === $this->table) {
            return;
        }

        // TODO
        // - cannot have master field if another archive has this as master
        // -
        $this->addFieldsToDca();
    }

    public function onMasterOptions(DataContainer $dc)
    {
        if (null === $this->table) {
            return [];
        }

        $options = [];
        $result = Database::getInstance()
            ->prepare('SELECT id, title FROM ' . $this->table . ' WHERE id!=? AND language!=? AND master=0 ORDER BY title')
            ->execute($dc->id, $dc->activeRecord->language)
        ;

        while ($result->next()) {
            $options[$result->id] = sprintf($GLOBALS['TL_LANG'][$this->table]['isSlave'], $result->title);
        }

        return $options;
    }

    protected function addFieldsToDca()
    {
        $self = $this;
        $user = BackendUser::getInstance();

        System::loadLanguageFile('tl_page');

        $GLOBALS['TL_DCA'][$this->table]['fields']['language'] = [
            'label'     => &$GLOBALS['TL_LANG'][$this->table]['language'],
            'exclude'   => !$user->hasAccess($this->table . '::language', 'alexf'),
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => [
                'mandatory' => true,
                'rgxp'      => 'language',
                'maxlength' => 5,
                'nospace'   => true,
                'tl_class'  => 'w50',
            ],
            'sql'       => "varchar(5) NOT NULL default ''",
        ];

        $GLOBALS['TL_DCA'][$this->table]['fields']['master'] = [
            'label'            => &$GLOBALS['TL_LANG'][$this->table]['master'],
            'exclude'          => !$user->hasAccess($this->table . '::master', 'alexf'),
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) use ($self) {
                return $self->onMasterOptions($dc);
            },
            'eval'             => [
                'includeBlankOption' => true,
                'blankOptionLabel'   => &$GLOBALS['TL_LANG'][$this->table]['isMaster'],
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
        ];

        PaletteManipulator::create()
            ->addLegend('language_legend', 'title_legend')
            ->addField('language', 'language_legend', PaletteManipulator::POSITION_APPEND)
            ->addField('master', 'language_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', $this->table)
        ;

    }
}
