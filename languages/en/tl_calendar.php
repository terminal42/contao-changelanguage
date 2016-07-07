<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

System::loadLanguageFile('tl_page');

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_calendar']['master']   = array('Master calendar', 'Please define the master calendar to allow language switching.');
$GLOBALS['TL_LANG']['tl_calendar']['language'] = &$GLOBALS['TL_LANG']['tl_page']['language'];


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_calendar']['language_legend'] = 'Language settings';


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_calendar']['isMaster'] = 'This is a master calendar';
$GLOBALS['TL_LANG']['tl_calendar']['isSlave']  = 'Master calendar is "%s"';
