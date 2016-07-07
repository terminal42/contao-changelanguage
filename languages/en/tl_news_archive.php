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
$GLOBALS['TL_LANG']['tl_news_archive']['master']   = ['Master archive', 'Please define the master archive to allow language switching.'];
$GLOBALS['TL_LANG']['tl_news_archive']['language'] = &$GLOBALS['TL_LANG']['tl_page']['language'];


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_news_archive']['language_legend'] = 'Language settings';


/**
 * References
 */
$GLOBALS['TL_LANG']['tl_news_archive']['isMaster'] = 'This is a master archive';
$GLOBALS['TL_LANG']['tl_news_archive']['isSlave']  = 'Master archive is "%s"';
