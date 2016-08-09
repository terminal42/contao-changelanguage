<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage\EventListener\Navigation;

use Contao\ArticleModel;
use Contao\Database;
use Contao\PageModel;
use Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent;

class ArticleNavigationListener
{
    /**
     * Translate URL parameters for articles.
     *
     * @param ChangelanguageNavigationEvent $event
     */
    public function onChangelanguageNavigation(ChangelanguageNavigationEvent $event)
    {
        // Try to find matching article
        if ($event->getNavigationItem()->isCurrentPage()
            || !$event->getUrlParameterBag()->hasUrlAttribute('articles')
        ) {
            return;
        }

        /** @var PageModel $objPage */
        global $objPage;

        $targetRoot     = $event->getNavigationItem()->getRootPage();
        $currentAlias   = $event->getUrlParameterBag()->getUrlAttribute('articles');
        $currentArticle = ArticleModel::findByIdOrAliasAndPid($currentAlias, $objPage->id);

        if (null === $currentArticle
            || ($currentArticle->languageMain < 1
                && ($targetRoot->fallback || !$objPage->rootIsFallback)
            )
        ) {
            return;
        }

        $targetArticle = $this->findTargetArticle($targetRoot, $currentArticle, $objPage->rootIsFallback);

        if (null === $targetArticle) {
            return;
        }

        $event->getUrlParameterBag()->setUrlAttribute('articles', $targetArticle->alias);
    }

    /**
     * Find target article for a root page and current article.
     *
     * @param PageModel    $targetRoot
     * @param ArticleModel $currentArticle
     * @param bool         $isFallback
     *
     * @return \ArticleModel|null
     */
    private function findTargetArticle(PageModel $targetRoot, ArticleModel $currentArticle, $isFallback)
    {
        // If the target root is fallback, the article ID will match our current "languageMain"
        if ($targetRoot->fallback) {
            return $this->findPublishedArticle(array('tl_article.id = '.$currentArticle->languageMain));
        }

        $subpages = Database::getInstance()->getChildRecords($targetRoot->id, 'tl_page', true);

        if (0 === count($subpages)) {
            return null;
        }

        return $this->findPublishedArticle(
            array(
                'tl_article.languageMain = ?',
                'tl_article.pid IN (' . implode(',', $subpages) . ')'
            ),
            array(
                $isFallback ? $currentArticle->id : $currentArticle->languageMain
            )
        );
    }

    /**
     * Find a published article with additional conditions.
     *
     * @param array $columns
     * @param array $values
     * @param array $options
     *
     * @return \ArticleModel|null
     */
    private function findPublishedArticle(array $columns, array $values = array(), array $options = array())
    {
        $t = ArticleModel::getTable();

        if (true !== BE_USER_LOGGED_IN) {
            $time      = \Date::floorToMinute();
            $columns[] = "($t.start='' OR $t.start<='$time')";
            $columns[] = "($t.stop='' OR $t.stop>'" . ($time + 60) . "')";
            $columns[] = "$t.published='1'";
        }

        return ArticleModel::findOneBy($columns, $values, $options);
    }
}
