<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener;

use Terminal42\ChangeLanguage\EventListener\DataContainer\MissingLanguageIconListener;

class CallbackSetupListener
{
    private static $listeners = [
        'tl_page' => [
            'Terminal42\ChangeLanguage\EventListener\DataContainer\PageInitializationListener',
            'Terminal42\ChangeLanguage\EventListener\DataContainer\PageOperationListener',
            'Terminal42\ChangeLanguage\EventListener\BackendView\PageViewListener',
        ],
        'tl_article' => [
            'Terminal42\ChangeLanguage\EventListener\DataContainer\ArticleListener',
            'Terminal42\ChangeLanguage\EventListener\BackendView\PageViewListener',
            'Terminal42\ChangeLanguage\EventListener\BackendView\ArticleViewListener',
        ],
        'tl_content' => [
            'Terminal42\ChangeLanguage\EventListener\BackendView\ArticleViewListener',
            'Terminal42\ChangeLanguage\EventListener\BackendView\ParentChildViewListener',
        ],
        'tl_news_archive' => ['Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener'],
        'tl_calendar' => ['Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener'],
        'tl_faq_category' => ['Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener'],
        'tl_news' => [
            'Terminal42\ChangeLanguage\EventListener\DataContainer\NewsListener',
            'Terminal42\ChangeLanguage\EventListener\BackendView\ParentChildViewListener',
        ],
        'tl_calendar_events' => [
            'Terminal42\ChangeLanguage\EventListener\DataContainer\CalendarEventsListener',
            'Terminal42\ChangeLanguage\EventListener\BackendView\ParentChildViewListener',
        ],
        'tl_faq' => [
            'Terminal42\ChangeLanguage\EventListener\DataContainer\FaqListener',
            'Terminal42\ChangeLanguage\EventListener\BackendView\ParentChildViewListener',
        ],
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
