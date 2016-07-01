<?php

namespace Terminal42\ChangeLanguage;

use Contao\Date;
use Contao\PageModel;

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
            [$page->dns, $page->dns, $page->dns],
            ['order' => 'sorting']
        );
    }

    /**
     * @param PageModel $page
     *
     * @return PageModel[]
     */
    public function findAssociatedForPage(PageModel $page)
    {
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
