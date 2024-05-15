<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Composer\InstalledVersions;
use Contao\Backend;
use Contao\BackendUser;
use Contao\Config;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Date;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Service\ResetInterface;
use Terminal42\ChangeLanguage\Helper\LabelCallback;

/**
 * @Hook("loadDataContainer")
 */
class MissingLanguageIconListener implements ResetInterface
{
    private static $callbacks;

    private Security $security;
    private Connection $connection;

    private ?array $pageCache = null;
    private ?array $translationCache = null;

    public function __construct(Security $security, Connection $connection)
    {
        $this->security = $security;
        $this->connection = $connection;
    }

    /**
     * Override core labels to show missing language information.
     */
    public function __invoke(string $table): void
    {
        $callbacks = self::getCallbacks();

        if (\array_key_exists($table, $callbacks)) {
            LabelCallback::createAndRegister(
                $table,
                fn (array $args, $previousResult) => $this->{$callbacks[$table]}($args, $previousResult),
            );
        }
    }

    public function reset(): void
    {
        $this->pageCache = null;
        $this->translationCache = null;
    }

    /**
     * Adds missing translation warning to page tree.
     */
    private function onPageLabel(array $args, $previousResult = null): string
    {
        [$row, $label] = $args;

        if ($previousResult) {
            $label = $previousResult;
        }

        if ('root' === $row['type'] || 'folder' === $row['type'] || 'page' !== Input::get('do')) {
            return $label;
        }

        $translation = $this->getPageTranslation((int) $row['id']);

        if (0 === ($translation['languageMain'] ?? null)) {
            return $this->generateLabelWithWarning($label);
        }

        $user = $this->security->getUser();

        if (
            ($translation['languageMain'] ?? null) > 0
            && $user instanceof BackendUser
            && \is_array($user->pageLanguageLabels)
            && \in_array(($translation['rootId'] ?? null), $user->pageLanguageLabels, false)
        ) {
            return sprintf(
                '%s <span style="color:#999;padding-left:3px">(<a href="%s" title="%s" style="color:#999">%s</a>)</span>',
                $label,
                Backend::addToUrl('pn='.$translation['languageMain']),
                StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['selectNode']),
                $translation['mainTitle'] ?? '',
            );
        }

        return $label;
    }

    /**
     * Adds missing translation warning to article tree.
     */
    private function onArticleLabel(array $args, $previousResult = null): string
    {
        [$row, $label] = $args;

        if ($previousResult) {
            $label = $previousResult;
        }

        if (!$row['showTeaser']) {
            return $label;
        }

        if (0 === $this->getChildTranslation((int) $row['id'], 'tl_article', 'tl_page', 'languageMain')) {
            return $this->generateLabelWithWarning($label);
        }

        return $label;
    }

    /**
     * Generate missing translation warning for news child records.
     */
    private function onNewsChildRecords(array $args, $previousResult = null): string
    {
        $row = $args[0];
        $label = (string) $previousResult;

        if (empty($label)) {
            $label = '<div class="tl_content_left">'.$row['headline'].' <span style="color:#999;padding-left:3px">['.Date::parse(Config::get('datimFormat'), $row['date']).']</span></div>';
        }

        if (0 === $this->getChildTranslation((int) $row['id'], 'tl_news', 'tl_news_archive', 'master')) {
            return preg_replace(
                '#</div>#',
                $this->generateLabelWithWarning('').'</div>',
                $label,
                1,
            );
        }

        return $label;
    }

    /**
     * Generate missing translation warning for calendar events child records.
     */
    private function onCalendarEventChildRecords(array $args, $previousResult = null): string
    {
        $row = $args[0];
        $label = (string) $previousResult;

        if (0 === $this->getChildTranslation((int) $row['id'], 'tl_calendar_events', 'tl_calendar', 'master')) {
            //return $this->generateLabelWithWarning($label);
            return preg_replace(
                '#</div>#',
                $this->generateLabelWithWarning('', 'position:absolute;top:6px').'</div>',
                $label,
                1,
            );
        }

        return $label;
    }

    /**
     * Generate missing translation warning for faq child records.
     */
    private function onFaqChildRecords(array $args, $previousResult = null): string
    {
        $row = $args[0];
        $label = (string) $previousResult;

        if (0 === $this->getChildTranslation((int) $row['id'], 'tl_faq', 'tl_faq_category', 'master')) {
            return preg_replace(
                '#</div>#',
                $this->generateLabelWithWarning('').'</div>',
                $label,
                1,
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
        return $label.sprintf(
            '<span style="padding-left:3px"><img src="%s" alt="%s" title="%s" style="%s"></span>',
            'bundles/terminal42changelanguage/language-warning.png',
            $GLOBALS['TL_LANG']['MSC']['noMainLanguage'],
            $GLOBALS['TL_LANG']['MSC']['noMainLanguage'],
            $imgStyle,
        );
    }

    private static function getCallbacks(): array
    {
        if (null !== self::$callbacks) {
            return self::$callbacks;
        }

        $callbacks = [
            'tl_page' => 'onPageLabel',
            'tl_article' => 'onArticleLabel',
        ];

        if (InstalledVersions::isInstalled('contao/news-bundle')) {
            $callbacks['tl_news'] = 'onNewsChildRecords';
        }

        if (InstalledVersions::isInstalled('contao/calendar-bundle')) {
            $callbacks['tl_calendar_events'] = 'onCalendarEventChildRecords';
        }

        if (InstalledVersions::isInstalled('contao/faq-bundle')) {
            $callbacks['tl_faq'] = 'onFaqChildRecords';
        }

        return self::$callbacks = $callbacks;
    }

    /**
     * @return array{languageMain: int, mainTitle: string|null}|null
     */
    private function getPageTranslation(int $id): ?array
    {
        if (null !== $this->pageCache) {
            return $this->pageCache[$id] ?? null;
        }

        $childRecords = function (array $parentIds, int $rootId, $return = []) use (&$childRecords) {
            $children = $this->connection->fetchAllAssociativeIndexed(
                <<<SQL
                    SELECT
                        c.id,
                        IFNULL(t.id, 0) AS languageMain,
                        t.title AS mainTitle,
                        $rootId as rootId
                    FROM tl_page c
                        LEFT JOIN tl_page t ON c.languageMain=t.id
                    WHERE c.pid IN(?)
                SQL,
                [array_keys($parentIds)],
                [ArrayParameterType::INTEGER]
            );

            if ($children) {
                $return = $children + $childRecords($children, $rootId, $return);
            }

            return $return;
        };

        $rootIds = $this->connection->fetchFirstColumn("SELECT id FROM tl_page WHERE type='root' AND (fallback='' OR languageRoot>0)");

        foreach ($rootIds as $rootId) {
            $this->pageCache = $childRecords([$rootId => []], $rootId);
        }

        return $this->pageCache[$id] ?? null;
    }

    private function getChildTranslation(int $id, string $table, string $ptable, string $parentField): ?int
    {
        if (isset($this->translationCache[$table])) {
            return $this->translationCache[$table][$id] ?? null;
        }

        $this->translationCache[$table] = $this->connection->fetchAllKeyValue(
            <<<SQL
                SELECT
                    c.id,
                    IFNULL(ct.id, 0)
                FROM $table c
                    JOIN $ptable p ON c.pid=p.id
                    LEFT JOIN $table ct ON c.languageMain=ct.id
                    LEFT JOIN $ptable pt ON p.$parentField=pt.id
                WHERE pt.id > 0
            SQL
        );

        return $this->translationCache[$table][$id] ?? null;
    }
}
