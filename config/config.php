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
 * Frontend modules
 */
$GLOBALS['FE_MOD']['miscellaneous']['changelanguage']   = 'Terminal42\ChangeLanguage\FrontendModule\ChangeLanguageModule';


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['translateUrlParameters'][] = array('Terminal42\ChangeLanguage\EventListener\ArticleParameterListener', 'onTranslateUrlParameters');
