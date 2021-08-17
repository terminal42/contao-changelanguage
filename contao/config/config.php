<?php

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

if (class_exists(Contao\CalendarBundle\ContaoCalendarBundle::class)) {
    $GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = ['Terminal42\ChangeLanguage\EventListener\Navigation\CalendarNavigationListener', 'onChangelanguageNavigation'];
}

if (class_exists(Contao\FaqBundle\ContaoFaqBundle::class)) {
    $GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = ['Terminal42\ChangeLanguage\EventListener\Navigation\FaqNavigationListener', 'onChangelanguageNavigation'];
}

if (class_exists(Contao\NewsBundle\ContaoNewsBundle::class)) {
    $GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = ['Terminal42\ChangeLanguage\EventListener\Navigation\NewsNavigationListener', 'onChangelanguageNavigation'];
}
