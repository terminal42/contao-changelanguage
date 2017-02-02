<?php

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\ArticleModel;
use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\FaqCategoryModel;
use Contao\FaqModel;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\PageModel;
use Terminal42\ChangeLanguage\Helper\LabelCallback;

class MissingLanguageIconListener
{
    private static $callbacks = [
        'tl_page'            => 'onPageLabel',
        'tl_article'         => 'onArticleLabel',
        'tl_news'            => 'onNewsChildRecords',
        'tl_calendar_events' => 'onCalendarEventChildRecords',
        'tl_faq'             => 'onFaqChildRecords',
    ];

    /**
     * Override core labels to show missing language information.
     *
     * @param string $table
     */
    public function register($table)
    {
        if (array_key_exists($table, self::$callbacks)) {
            LabelCallback::createAndRegister(
                $table,
                function (array $args, $previousResult) use ($table) {
                    return $this->{self::$callbacks[$table]}($args, $previousResult);
                }
            );
        }
    }

    /**
     * Adds missing translation warning to page tree.
     *
     * @param array $args
     * @param mixed $previousResult
     *
     * @return string
     */
    public function onPageLabel(array $args, $previousResult = null)
    {
        list($row, $label) = $args;

        if ($previousResult) {
            $label = $previousResult;
        }

        if ('root' === $row['type'] || 'folder' === $row['type'] || 'page' !== \Input::get('do')) {
            return $label;
        }

        $page = PageModel::findWithDetails($row['id']);
        $root = PageModel::findByPk($page->rootId);

        if ((!$root->fallback || $root->languageRoot > 0)
            && (!$page->languageMain || null === PageModel::findByPk($page->languageMain))
        ) {
            return $this->generateLabelWithWarning($label);
        }

        return $label;
    }

    /**
     * Adds missing translation warning to article tree.
     *
     * @param array $args
     * @param mixed $previousResult
     *
     * @return string
     */
    public function onArticleLabel(array $args, $previousResult = null)
    {
        list($row, $label) = $args;

        if ($previousResult) {
            $label = $previousResult;
        }

        if ($row['showTeaser']) {
            $page = PageModel::findWithDetails($row['pid']);
            $root = PageModel::findByPk($page->rootId);

            if ((!$root->fallback || $root->languageRoot > 0)
                && $page->languageMain > 0 && null !== PageModel::findByPk($page->languageMain)
                && (!$row['languageMain'] || null === ArticleModel::findByPk($row['languageMain']))
            ) {
                return $this->generateLabelWithWarning($label);
            }
        }

        return $label;
    }

    /**
     * Generate missing translation warning for news child records.
     *
     * @param array $args
     * @param mixed $previousResult
     *
     * @return string
     */
    public function onNewsChildRecords(array $args, $previousResult = null)
    {
        $row   = $args[0];
        $label = (string) $previousResult;

        $archive = NewsArchiveModel::findByPk($row['pid']);

        if ($archive->master &&
            (!$row['languageMain'] || null === NewsModel::findByPk($row['languageMain']))
        ) {
            return $this->generateLabelWithWarning($label);
        }

        return $label;
    }

    /**
     * Generate missing translation warning for calendar events child records.
     *
     * @param array $args
     * @param mixed $previousResult
     *
     * @return string
     */
    public function onCalendarEventChildRecords(array $args, $previousResult = null)
    {
        $row   = $args[0];
        $label = (string) $previousResult;

        $calendar = CalendarModel::findByPk($row['pid']);

        if ($calendar->master
            && (!$row['languageMain'] || null === CalendarEventsModel::findByPk($row['languageMain']))
        ) {
            return $this->generateLabelWithWarning($label);
        }

        return $label;
    }

    /**
     * Generate missing translation warning for faq child records.
     *
     * @param array $args
     * @param mixed $previousResult
     *
     * @return string
     */
    public function onFaqChildRecords(array $args, $previousResult = null)
    {
        $row   = $args[0];
        $label = (string) $previousResult;

        $category = FaqCategoryModel::findByPk($row['pid']);

        if ($category->master
            && (!$row['languageMain'] || null === FaqModel::findByPk($row['languageMain']))
        ) {
            return preg_replace(
                '#</div>#',
                $this->generateLabelWithWarning('', 'position:absolute;top:6px') . '</div>',
                $label,
                1
            );
        }

        return $label;
    }

    /**
     * @param string $label
     * @param string $imgStyle
     *
     * @return string
     */
    private function generateLabelWithWarning($label, $imgStyle = '')
    {
        return $label . sprintf(
            '<span style="padding-left:3px"><img src="%s" alt="%s" title="%s" style="%s"></span>',
            'system/modules/changelanguage/assets/language-warning.png',
            $GLOBALS['TL_LANG']['MSC']['noMainLanguage'],
            $GLOBALS['TL_LANG']['MSC']['noMainLanguage'],
            $imgStyle
        );
    }
}
