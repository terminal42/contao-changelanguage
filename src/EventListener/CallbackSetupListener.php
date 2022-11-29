<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener;

use Terminal42\ChangeLanguage\EventListener\BackendView\ArticleViewListener;
use Terminal42\ChangeLanguage\EventListener\BackendView\PageViewListener;
use Terminal42\ChangeLanguage\EventListener\BackendView\ParentChildViewListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\ArticleListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\CalendarEventsListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\FaqListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\MissingLanguageIconListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\NewsListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\PageInitializationListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\PageOperationListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener;

class CallbackSetupListener
{
    private static array $listeners = [
        'tl_page' => [
            PageInitializationListener::class,
            PageOperationListener::class,
            PageViewListener::class,
        ],
        'tl_article' => [
            ArticleListener::class,
            PageViewListener::class,
            ArticleViewListener::class,
        ],
        'tl_content' => [
            ArticleViewListener::class,
            ParentChildViewListener::class,
        ],
        'tl_news_archive' => [ParentTableListener::class],
        'tl_calendar' => [ParentTableListener::class],
        'tl_faq_category' => [ParentTableListener::class],
        'tl_news' => [
            NewsListener::class,
            ParentChildViewListener::class,
        ],
        'tl_calendar_events' => [
            CalendarEventsListener::class,
            ParentChildViewListener::class,
        ],
        'tl_faq' => [
            FaqListener::class,
            ParentChildViewListener::class,
        ],
    ];

    private MissingLanguageIconListener $labelListener;

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
    public function onLoadDataContainer($table): void
    {
        $this->labelListener->register($table);

        if (\array_key_exists($table, self::$listeners)) {
            foreach (self::$listeners[$table] as $class) {
                /** @var AbstractTableListener $listener */
                $listener = new $class($table);
                $listener->register();
            }
        }
    }
}
