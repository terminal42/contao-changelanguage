<?php
/**
 * Config.
 */
$GLOBALS['TL_DCA']['tl_page']['config']['sql']['keys']['languageMain'] = 'index';

/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['fallback']['eval']['submitOnChange'] = true;

$GLOBALS['TL_DCA']['tl_page']['fields']['languageMain'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_page']['languageMain'],
    'exclude' => true,
    'inputType' => 'pageTree',
    'eval' => ['fieldType' => 'radio', 'multiple' => false, 'rootNodes' => [0], 'tl_class' => 'w50 clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
    // 'load_callback'           => [['Terminal42\ChangeLanguage\EventListener\DataContainer\PageFieldsListener', 'onLoadLanguageMain']],
    // 'save_callback'           => [['Terminal42\ChangeLanguage\EventListener\DataContainer\PageFieldsListener', 'onSaveLanguageMain']],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['languageRoot'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_page']['languageRoot'],
    'exclude' => true,
    'inputType' => 'select',
    // 'options_callback'        => array('Terminal42\ChangeLanguage\EventListener\DataContainer\PageFieldsListener', 'onLanguageRootOptions'),
    'eval' => ['includeBlankOption' => true, 'blankOptionLabel' => &$GLOBALS['TL_LANG']['tl_page']['languageRoot'][2], 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];

$GLOBALS['TL_DCA']['tl_page']['fields']['languageQuery'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_page']['languageQuery'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['tl_class' => 'w50'],
    'sql' => "varchar(255) NOT NULL default ''",
];
