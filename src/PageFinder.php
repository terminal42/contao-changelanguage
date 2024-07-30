<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage;

use Contao\Date;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\System;

class PageFinder
{
    /**
     * @return array<PageModel>
     */
    public function findRootPagesForPage(PageModel $page, bool $skipCurrent = false, bool $publishedOnly = true): array
    {
        $page->loadDetails();
        $t = $page::getTable();

        $columns = [
            "$t.type='root'",
            "(
                $t.dns=?
                OR $t.dns IN (
                    SELECT dns
                    FROM tl_page
                    WHERE type='root' AND fallback='1' AND id = (
                        SELECT languageRoot FROM tl_page WHERE type='root' AND fallback='1' AND dns=? LIMIT 1
                    )
                )
                OR $t.dns IN (
                    SELECT dns
                    FROM tl_page
                    WHERE type='root' AND fallback='1' AND languageRoot = (
                        SELECT id FROM tl_page WHERE type='root' AND fallback='1' AND dns=? LIMIT 1
                    )
                )
                OR $t.dns IN (
                    SELECT dns
                    FROM tl_page
                    WHERE type='root' AND fallback='1' AND languageRoot != 0 AND languageRoot = (
                        SELECT languageRoot FROM tl_page WHERE type='root' AND fallback='1' AND dns=? LIMIT 1
                    )
                )
            )",
        ];

        $values = [$page->domain, $page->domain, $page->domain, $page->domain];

        if ($skipCurrent) {
            $columns[] = "$t.id!=?";
            $values[] = $page->rootId;
        }

        if ($publishedOnly) {
            $this->addPublishingConditions($columns, $t);
        }

        return $this->findPages($columns, $values, ['order' => 'sorting']);
    }

    /**
     * Finds the root page of fallback language for the given page.
     */
    public function findMasterRootForPage(PageModel $page): ?PageModel
    {
        $page->loadDetails();
        $t = $page::getTable();

        $columns = [
            "$t.type='root'",
            "$t.fallback='1'",
            "$t.languageRoot=0",
            "(
                $t.dns=?
                OR $t.dns IN (SELECT dns FROM tl_page WHERE type='root' AND fallback='1' AND id IN (SELECT languageRoot FROM tl_page WHERE type='root' AND fallback='1' AND dns=?))
                OR $t.dns IN (SELECT dns FROM tl_page WHERE type='root' AND fallback='1' AND languageRoot IN (SELECT id FROM tl_page WHERE type='root' AND fallback='1' AND dns=?))
            )",
        ];

        return PageModel::findOneBy(
            $columns,
            [$page->domain, $page->domain, $page->domain],
        );
    }

    /**
     * @param array<PageModel>|null $rootPages
     *
     * @return array<PageModel>
     */
    public function findAssociatedForPage(PageModel $page, bool $skipCurrent = false, ?array $rootPages = null, bool $publishedOnly = true): array
    {
        if ('root' === $page->type) {
            return $this->findRootPagesForPage($page, $skipCurrent, $publishedOnly);
        }

        if (null === $rootPages) {
            $rootPages = $this->findRootPagesForPage($page, $skipCurrent, $publishedOnly);
        }

        $page->loadDetails();
        $t = $page::getTable();

        if ($page->rootIsFallback && null !== ($root = PageModel::findById($page->rootId)) && !$root->languageRoot) {
            $values = [$page->id, $page->id];
        } elseif (!$page->languageMain) {
            return $skipCurrent ? [] : [$page];
        } else {
            $values = [$page->languageMain, $page->languageMain];
        }

        $columns = ["($t.id=? OR $t.languageMain=?)"];

        if ($skipCurrent) {
            $columns[] = "$t.id!=?";
            $values[] = $page->id;
        }

        if ($publishedOnly) {
            $this->addPublishingConditions($columns, $t);
        }

        return array_filter(
            $this->findPages($columns, $values),
            static function (PageModel $page) use ($rootPages) {
                $page->loadDetails();

                return \array_key_exists($page->rootId, $rootPages);
            },
        );
    }

    public function findAssociatedForLanguage(PageModel $page, string $language): PageModel
    {
        $language = Language::toLocaleID($language);
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

    public function findAssociatedInMaster(PageModel $page): ?PageModel
    {
        $page->loadDetails();
        $masterRoot = $this->findMasterRootForPage($page);

        if ($masterRoot->id === $page->rootId) {
            return null;
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
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function findAssociatedParentForLanguage(PageModel $page, string $language): PageModel
    {
        // Stop loop if we're at the top
        if (0 === $page->pid || 'root' === $page->type) {
            $rootPages = $this->findRootPagesForPage($page);

            foreach ($rootPages as $model) {
                if (Language::toLocaleID($model->language) === $language) {
                    return $model;
                }
            }

            throw new \InvalidArgumentException(\sprintf('There\'s no language "%s" related to root page ID "%s"', $language, $page->id));
        }

        $parent = PageModel::findPublishedById($page->pid);

        if (!$parent instanceof PageModel) {
            throw new \RuntimeException(\sprintf('Parent page for page ID "%s" not found', $page->id));
        }

        return $this->findAssociatedForLanguage($parent, $language);
    }

    /**
     * @param array<string> $columns
     */
    private function addPublishingConditions(array &$columns, string $table): void
    {
        if (!System::getContainer()->get('contao.security.token_checker')->isPreviewMode()) {
            $start = Date::floorToMinute();
            $stop = $start + 60;

            $columns[] = "$table.published='1'";
            $columns[] = "($table.start='' OR $table.start<$start)";
            $columns[] = "($table.stop='' OR $table.stop>$stop)";
        }
    }

    /**
     * @param array<string>         $columns
     * @param array<string>         $values
     * @param array<string, string> $options
     *
     * @return array<PageModel>
     */
    private function findPages(array $columns, array $values, array $options = []): array
    {
        $collection = PageModel::findBy($columns, $values, $options);

        if (!$collection instanceof Collection) {
            return [];
        }

        $models = [];

        foreach ($collection as $model) {
            $models[$model->id] = $model;
        }

        return $models;
    }
}
