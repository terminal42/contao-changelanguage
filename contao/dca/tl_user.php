<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addLegend('changelanguage_legend', '', PaletteManipulator::POSITION_APPEND)
    ->addField('pageLanguageLabels', 'changelanguage_legend')
    ->applyToPalette('login', 'tl_user')
;

$GLOBALS['TL_DCA']['tl_user']['fields']['pageLanguageLabels'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['multiple' => true, 'tl_class' => 'w50'],
    'sql' => "text NULL",
];
