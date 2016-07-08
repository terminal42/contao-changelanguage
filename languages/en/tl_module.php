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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['hideActiveLanguage'] = array('Hide active language', 'Please check here if you want to hide the active language on your site.');
$GLOBALS['TL_LANG']['tl_module']['hideNoFallback']     = array('Hide languages without direct fallback', 'Do not show languages that have no direct fallback assigned.');
$GLOBALS['TL_LANG']['tl_module']['customLanguage']     = array('Custom language texts', 'Please check here if you want to have custom texts for your languages (not uppercase language shortcuts).');
$GLOBALS['TL_LANG']['tl_module']['customLanguageText'] = array('Language texts', 'Please enter a replacement for every language. Use lower case shortcuts.');


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_module']['customLanguageText']['value'] = 'Language shortcut';
$GLOBALS['TL_LANG']['tl_module']['customLanguageText']['label'] = 'Replacement text';
