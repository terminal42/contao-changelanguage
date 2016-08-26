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
$GLOBALS['TL_HOOKS']['replaceInsertTags'][]        = ['Terminal42\ChangeLanguage\EventListener\InsertTagsListener', 'onReplaceInsertTags'];
$GLOBALS['TL_HOOKS']['loadDataContainer'][]        = ['Terminal42\ChangeLanguage\EventListener\CallbackSetupListener', 'onLoadDataContainer'];
$GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = ['Terminal42\ChangeLanguage\EventListener\Navigation\ArticleNavigationListener', 'onChangelanguageNavigation'];

if (in_array('calendar', ModuleLoader::getActive(), true)) {
    $GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = ['Terminal42\ChangeLanguage\EventListener\Navigation\CalendarNavigationListener', 'onChangelanguageNavigation'];
}

if (in_array('faq', ModuleLoader::getActive(), true)) {
    $GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = ['Terminal42\ChangeLanguage\EventListener\Navigation\FaqNavigationListener', 'onChangelanguageNavigation'];
}

if (in_array('news', ModuleLoader::getActive(), true)) {
    $GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = ['Terminal42\ChangeLanguage\EventListener\Navigation\NewsNavigationListener', 'onChangelanguageNavigation'];
}
