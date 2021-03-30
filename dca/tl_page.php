<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

/**
 * Config
 */
$GLOBALS['TL_DCA']['tl_page']['config']['sql']['keys']['languageMain'] = 'index';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['fallback']['eval']['submitOnChange'] = true;

$GLOBALS['TL_DCA']['tl_page']['fields']['languageMain'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['languageMain'],
    'exclude'                 => true,
    'inputType'               => 'pageTree',
    'eval'                    => array('fieldType'=>'radio', 'multiple'=>false, 'rootNodes'=>[0], 'tl_class'=>'w50 clr', 'doNoCopy'=>true),
    'sql'                     => "int(10) unsigned NOT NULL default '0'",
    'load_callback'           => [['Terminal42\ChangeLanguage\EventListener\DataContainer\PageFieldsListener', 'onLoadLanguageMain']],
    'save_callback'           => [['Terminal42\ChangeLanguage\EventListener\DataContainer\PageFieldsListener', 'onSaveLanguageMain']],
);

$GLOBALS['TL_DCA']['tl_page']['fields']['languageRoot'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['languageRoot'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options_callback'        => array('Terminal42\ChangeLanguage\EventListener\DataContainer\PageFieldsListener', 'onLanguageRootOptions'),
    'eval'                    => array('includeBlankOption'=>true, 'blankOptionLabel'=>&$GLOBALS['TL_LANG']['tl_page']['languageRoot'][2], 'tl_class'=>'w50'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_page']['fields']['languageQuery'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_page']['languageQuery'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''",
);
