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
use Contao\Database;
use Contao\PageModel;
use Haste\Dca\PaletteManipulator;
use Terminal42\ChangeLanguage\PageFinder;

class ParentTableListener
{
    /**
     * @var string
     */
    private $table;

    /**
     * Constructor.
     *
     * @param string $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    public function register()
    {
        if (!isset($GLOBALS['TL_DCA'][$this->table]['palettes']['default'])) {
            return;
        }

        $GLOBALS['TL_DCA'][$this->table]['fields']['master'] = [
            'label'            => &$GLOBALS['TL_LANG'][$this->table]['master'],
            'exclude'          => true,
            'inputType'        => 'select',
            'options_callback' => function (DataContainer $dc) {
                return $this->onMasterOptions($dc);
            },
            'eval'             => [
                'includeBlankOption' => true,
                'blankOptionLabel'   => &$GLOBALS['TL_LANG'][$this->table]['isMaster'],
            ],
            'sql'              => "int(10) unsigned NOT NULL default '0'",
            'relation'         => ['type' => 'hasOne', 'table' => $this->table],
        ];

        PaletteManipulator::create()
            ->addLegend('language_legend', 'title_legend')
            ->addField('master', 'language_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', $this->table)
        ;
    }

    public function onMasterOptions(DataContainer $dc)
    {
        if (($jumpTo = PageModel::findByPk($dc->activeRecord->jumpTo)) === null) {
            return [];
        }

        $associated = [];
        $pageFinder = new PageFinder();

        foreach ($pageFinder->findAssociatedForPage($jumpTo, true) as $page) {
            $associated[] = $page->id;
        }

        if (0 === count($associated)) {
            return [];
        }

        $options = [];
        $result = Database::getInstance()
            ->prepare('
                SELECT id, title 
                FROM ' . $this->table . ' 
                WHERE jumpTo IN (' . implode(',', $associated) . ') AND master=0 
                ORDER BY title
            ')
            ->execute($dc->activeRecord->language)
        ;

        while ($result->next()) {
            $options[$result->id] = sprintf($GLOBALS['TL_LANG'][$this->table]['isSlave'], $result->title);
        }

        return $options;
    }
}
