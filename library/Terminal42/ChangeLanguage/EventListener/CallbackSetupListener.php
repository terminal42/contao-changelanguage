<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener;

use Terminal42\ChangeLanguage\EventListener\DataContainer\MissingLanguageIconListener;

class CallbackSetupListener
{
    private static $listeners = [
        'tl_page'            => [
            'Terminal42\ChangeLanguage\EventListener\DataContainer\PageInitializationListener',
            'Terminal42\ChangeLanguage\EventListener\DataContainer\PageOperationListener',
            'Terminal42\ChangeLanguage\EventListener\BackendView\PageViewListener',
        ],
        'tl_article'         => [
            'Terminal42\ChangeLanguage\EventListener\DataContainer\ArticleListener',
            'Terminal42\ChangeLanguage\EventListener\BackendView\PageViewListener',
        ],
        'tl_news_archive'    => ['Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener'],
        'tl_calendar'        => ['Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener'],
        'tl_faq_category'    => ['Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener'],
        'tl_news'            => ['Terminal42\ChangeLanguage\EventListener\DataContainer\NewsListener'],
        'tl_calendar_events' => ['Terminal42\ChangeLanguage\EventListener\DataContainer\CalendarEventsListener'],
        'tl_faq'             => ['Terminal42\ChangeLanguage\EventListener\DataContainer\FaqListener'],
    ];

    /**
     * @var MissingLanguageIconListener
     */
    private $labelListener;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->labelListener = new MissingLanguageIconListener();
    }

    /**
     * Callback for loadDataContainer hook.
     *
     * @param string $table
     */
    public function onLoadDataContainer($table)
    {
        $this->labelListener->register($table);

        if (array_key_exists($table, self::$listeners)) {
            foreach (self::$listeners[$table] as $class) {

                /** @var AbstractTableListener $listener */
                $listener = new $class($table);
                $listener->register();
            }
        }
    }
}
