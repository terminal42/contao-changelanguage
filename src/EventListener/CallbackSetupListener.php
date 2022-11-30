<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Terminal42\ChangeLanguage\EventListener\BackendView\ArticleViewListener;
use Terminal42\ChangeLanguage\EventListener\BackendView\PageViewListener;
use Terminal42\ChangeLanguage\EventListener\BackendView\ParentChildViewListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\ArticleListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\CalendarEventsListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\FaqListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\NewsListener;
use Terminal42\ChangeLanguage\EventListener\DataContainer\ParentTableListener;

/**
 * @Hook("loadDataContainer")
 */
class CallbackSetupListener
{
    private static array $listeners = [
        'tl_page' => [
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

    public function __invoke(string $table): void
    {
        if (\array_key_exists($table, self::$listeners)) {
            foreach (self::$listeners[$table] as $class) {
                $listener = new $class($table);
                $listener->register();
            }
        }
    }
}
