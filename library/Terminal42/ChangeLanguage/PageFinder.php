<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace Terminal42\ChangeLanguage;

use Contao\Date;
use Contao\PageModel;
use Terminal42\ChangeLanguage\Language;

class PageFinder
{
    /**
     * @param PageModel $page
     *
     * @return PageModel[]
     */
    public function findRootPagesForPage(PageModel $page)
    {
        $page->loadDetails();

        $columns = [
            "type='root'",
            "(
                dns=? 
                OR dns IN (SELECT dns FROM tl_page WHERE type='root' AND fallback='1' AND id IN (SELECT languageRoot FROM tl_page WHERE type='root' AND fallback='1' AND dns=?)) 
                OR dns IN (SELECT dns FROM tl_page WHERE type='root' AND fallback='1' AND languageRoot IN (SELECT id FROM tl_page WHERE type='root' AND fallback='1' AND dns=?))
            )"
        ];

        $this->addPublishingConditions($columns);

        return $this->findPages(
            $columns,
            [$page->domain, $page->domain, $page->domain],
            ['order' => 'sorting']
        );
    }

    /**
     * Finds the root page of fallback language for the given page.
     *
     * @param PageModel $page
     *
     * @return \PageModel|null
     */
    public function findMasterRootForPage(PageModel $page)
    {
        $page->loadDetails();

        $columns = [
            "type='root'",
            "fallback='1'",
            'languageRoot=0',
            "(
                dns=? 
                OR dns IN (SELECT dns FROM tl_page WHERE type='root' AND fallback='1' AND id IN (SELECT languageRoot FROM tl_page WHERE type='root' AND fallback='1' AND dns=?)) 
                OR dns IN (SELECT dns FROM tl_page WHERE type='root' AND fallback='1' AND languageRoot IN (SELECT id FROM tl_page WHERE type='root' AND fallback='1' AND dns=?))
            )"
        ];

        return PageModel::findOneBy(
            $columns,
            [$page->domain, $page->domain, $page->domain]
        );
    }

    /**
     * @param PageModel $page
     *
     * @return PageModel[]
     */
    public function findAssociatedForPage(PageModel $page)
    {
        if ('root' === $page->type) {
            return $this->findRootPagesForPage($page);
        }

        $page->loadDetails();

        if ($page->rootIsFallback) {
            $values = [$page->id, $page->id];
        } elseif (!$page->languageMain) {
            return [$page];
        } else {
            $values = [$page->languageMain, $page->languageMain];
        }

        $columns = ['(id=? OR languageMain=?)'];

        $this->addPublishingConditions($columns);

        return $this->findPages($columns, $values);
    }

    /**
     * @param PageModel $page
     * @param string    $language
     *
     * @return PageModel
     */
    public function findAssociatedForLanguage(PageModel $page, $language)
    {
        $language   = Language::toLocaleID($language);
        $associated = $this->findAssociatedForPage($page);

        foreach ($associated as $model) {
            $model->loadDetails();

            if (Language::toLocaleID($model->language) === $language) {
                return $model;
            }
        }

        // No page found, find for parent
        return $this->findAssociatedParentForLanguage($page, $language);
    }

    public function findAssociatedInMaster(PageModel $page)
    {
        $page->loadDetails();
        $masterRoot = $this->findMasterRootForPage($page);

        if ($masterRoot->id === $page->rootId) {
            return;
        }

        $associated = $this->findAssociatedForPage($page);

        foreach ($associated as $model) {
            $model->loadDetails();

            if ($model->rootId === $masterRoot->id) {
                return $model;
            }
        }

        return null;
    }

    /**
     * @param PageModel $page
     * @param string    $language
     *
     * @return PageModel
     */
    public function findAssociatedParentForLanguage(PageModel $page, $language)
    {
        // Stop loop if we're at the top
        if (0 === $page->pid || 'root' === $page->type) {
            $rootPages = $this->findRootPagesForPage($page);

            foreach ($rootPages as $model) {
                if (Language::toLocaleID($model->language) === $language) {
                    return $model;
                }
            }

            throw new \InvalidArgumentException(
                sprintf('There\'s no language "%s" related to root page ID "%s"', $language, $page->id)
            );
        }

        $parent = PageModel::findPublishedById($page->pid);

        if (!$parent instanceof PageModel) {
            throw new \UnderflowException(sprintf('Parent page for page ID "%s" not found', $page->id));
        }

        return $this->findAssociatedForLanguage($parent, $language);
    }

    /**
     * @param array $columns
     */
    private function addPublishingConditions(array &$columns)
    {
        if (true !== BE_USER_LOGGED_IN) {
            $start = Date::floorToMinute();
            $stop  = $start + 60;

            $columns[] = "published='1'";
            $columns[] = "(start='' OR start<$start)";
            $columns[] = "(stop='' OR stop>$stop)";
        }
    }

    /**
     * @param array $columns
     * @param array $values
     * @param array $options
     *
     * @return \Contao\PageModel[]
     */
    private function findPages(array $columns, array $values, array $options = [])
    {
        $collection = PageModel::findBy($columns, $values, $options);

        if (null === $collection) {
            return [];
        }

        $models = [];

        foreach ($collection as $model) {
            $models[$model->id] = $model;
        }

        return $models;
    }
}
