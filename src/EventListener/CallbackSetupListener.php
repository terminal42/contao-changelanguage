<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener;

use Composer\InstalledVersions;
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
    /**
     * @var array<string, array<string>>|null
     */
    private static ?array $listeners = null;

    public function __invoke(string $table): void
    {
        $listeners = self::getListeners();

        if (\array_key_exists($table, $listeners)) {
            foreach ($listeners[$table] as $class) {
                $listener = new $class($table);
                $listener->register();
            }
        }
    }

    /**
     * @return array<string, array<string>>
     */
    private static function getListeners(): array
    {
        if (null !== self::$listeners) {
            return self::$listeners;
        }

        $listeners = [
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
        ];

        if (InstalledVersions::isInstalled('contao/news-bundle')) {
            $listeners['tl_news_archive'] = [ParentTableListener::class];
            $listeners['tl_news'] = [NewsListener::class, ParentChildViewListener::class];
        }

        if (InstalledVersions::isInstalled('contao/calendar-bundle')) {
            $listeners['tl_calendar'] = [ParentTableListener::class];
            $listeners['tl_calendar_events'] = [CalendarEventsListener::class, ParentChildViewListener::class];
        }

        if (InstalledVersions::isInstalled('contao/faq-bundle')) {
            $listeners['tl_faq_category'] = [ParentTableListener::class];
            $listeners['tl_faq'] = [FaqListener::class, ParentChildViewListener::class];
        }

        return self::$listeners = $listeners;
    }
}
