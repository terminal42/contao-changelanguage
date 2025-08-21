<?php

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'customLanguage';
$GLOBALS['TL_DCA']['tl_module']['palettes']['changelanguage'] = '{title_legend},name,headline,type;{config_legend},hideActiveLanguage,hideNoFallback,customLanguage;{template_legend:hide},navigationTpl,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['customLanguage'] = 'customLanguageText';

$GLOBALS['TL_DCA']['tl_module']['fields']['hideActiveLanguage'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['hideActiveLanguage'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hideNoFallback'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['hideNoFallback'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['customLanguage'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['customLanguage'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true, 'tl_class' => 'clr'],
    'sql' => "char(1) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['customLanguageText'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['customLanguageText'],
    'exclude' => true,
    'inputType' => 'keyValueWizard',
    'eval' => ['mandatory' => true, 'allowHtml' => true, 'tl_class' => 'clr'],
    'sql' => 'text NULL',
];
