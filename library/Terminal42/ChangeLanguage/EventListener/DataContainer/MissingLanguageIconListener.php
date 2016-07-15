<?php

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\ArticleModel;
use Contao\Backend;
use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\FaqCategoryModel;
use Contao\FaqModel;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\PageModel;

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
            $callback = function () use ($table) {
                return call_user_func_array(
                    [$this, self::$callbacks[$table]],
                    func_get_args()
                );
            };

            if (4 === $GLOBALS['TL_DCA'][$table]['list']['sorting']['mode']) {
                $GLOBALS['TL_DCA'][$table]['list']['sorting']['child_record_callback'] = $callback;
            } else {
                $GLOBALS['TL_DCA'][$table]['list']['label']['label_callback'] = $callback;
            }
        }
    }

    /**
     * Adds missing translation warning to page tree.
     *
     * @param array               $row
     * @param string              $label
     * @param \DataContainer|null $dc
     * @param string              $imageAttribute
     * @param bool                $blnReturnImage
     * @param bool                $blnProtected
     *
     * @return string
     */
    public function onPageLabel(
        array $row,
        $label,
        $dc = null,
        $imageAttribute = '',
        $blnReturnImage = false,
        $blnProtected = false
    ) {
        $label = Backend::addPageIcon($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);

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
     * @param array  $row
     * @param string $label
     *
     * @return string
     */
    public function onArticleLabel(array $row, $label)
    {
        $tl_article = new \tl_article();
        $label = $tl_article->addIcon($row, $label);

        $page = PageModel::findWithDetails($row['pid']);
        $root = PageModel::findByPk($page->rootId);

        if ((!$root->fallback || $root->languageRoot > 0)
            && $page->languageMain > 0 && null !== PageModel::findByPk($page->languageMain)
            && (!$row['languageMain'] || null === ArticleModel::findByPk($row['languageMain']))
        ) {
            return $this->generateLabelWithWarning($label);
        }

        return $label;
    }

    /**
     * Generate missing translation warning for news child records.
     *
     * @param array $row
     *
     * @return string
     */
    public function onNewsChildRecords(array $row)
    {
        $tl_news = new \tl_news();
        $label = $tl_news->listNewsArticles($row);

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
     * @param array $row
     *
     * @return string
     */
    public function onCalendarEventChildRecords(array $row)
    {
        $tl_calendar_events = new \tl_calendar_events();
        $label = $tl_calendar_events->listEvents($row);

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
     * @param array $row
     *
     * @return string
     */
    public function onFaqChildRecords(array $row)
    {
        $tl_faq = new \tl_faq();
        $label = $tl_faq->listQuestions($row);

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
