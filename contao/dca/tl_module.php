<?php

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'customLanguage';
$GLOBALS['TL_DCA']['tl_module']['palettes']['changelanguage'] = '{title_legend},name,headline,type;{config_legend},hideActiveLanguage,hideNoFallback,customLanguage;{template_legend:collapsed},navigationTpl,customTpl;{protected_legend:collapsed},protected;{expert_legend:collapsed},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['customLanguage'] = 'customLanguageText';

$GLOBALS['TL_DCA']['tl_module']['fields']['hideActiveLanguage'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hideNoFallback'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['customLanguage'] = [
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['customLanguageText'] = [
    'inputType' => 'keyValueWizard',
    'eval' => ['mandatory' => true, 'allowHtml' => true, 'tl_class' => 'clr'],
    'sql' => 'text NULL',
];
