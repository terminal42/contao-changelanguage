<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Composer\InstalledVersions;
use Contao\Backend;
use Contao\BackendUser;
use Contao\Config;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Date;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Service\ResetInterface;
use Terminal42\ChangeLanguage\Helper\LabelCallback;

/**
 * @Hook("loadDataContainer")
 */
class MissingLanguageIconListener implements ResetInterface
{
    private AuthorizationCheckerInterface $authorizationChecker;

    /**
     * @var array<string, string>|null
     */
    private static ?array $callbacks = null;

    private TokenStorageInterface $tokenStorage;

    private Connection $connection;

    /**
     * @var array<int|string, array<int|string>>|null
     */
    private ?array $pageCache = null;

    /**
     * @var array<string, array<int, int>>|null
     */
    private ?array $translationCache = null;

    public function __construct(TokenStorageInterface $tokenStorage, Connection $connection, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->connection = $connection;
        $this->authorizationChecker = $authorizationChecker;
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
     *
     * @param array{0: array<string, int|string>, 1: string} $args
     * @param mixed                                          $previousResult
     */
    private function onPageLabel(array $args, $previousResult = null): string
    {
        [$row, $label] = $args;

        if ($previousResult) {
            $label = $previousResult;
        }

        if (
            'root' === $row['type']
            || 'folder' === $row['type']
            || 'page' !== Input::get('do')
            || !$this->authorizationChecker->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELD_OF_TABLE, 'tl_page.languageMain')
        ) {
            return $label;
        }

        $translation = $this->getPageTranslation((int) $row['id']);
        $languageMain = $translation['languageMain'] ?? null;

        if (null !== $languageMain && 0 === (int) $languageMain) {
            return $this->generateLabelWithWarning($label);
        }

        $token = $this->tokenStorage->getToken();
        $user = $token ? $token->getUser() : null;

        if (
            ($translation['languageMain'] ?? null) > 0
            && $user instanceof BackendUser
            && \is_array($user->pageLanguageLabels)
            && \in_array($translation['rootId'] ?? null, $user->pageLanguageLabels, false)
        ) {
            return \sprintf(
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
     *
     * @param array{0: array<string, int|string>, 1: string} $args
     * @param mixed                                          $previousResult
     */
    private function onArticleLabel(array $args, $previousResult = null): string
    {
        [$row, $label] = $args;

        if ($previousResult) {
            $label = $previousResult;
        }

        if (
            !$row['showTeaser']
            || !$this->authorizationChecker->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELD_OF_TABLE, 'tl_article.languageMain')
        ) {
            return $label;
        }

        if (0 === $this->getChildTranslation((int) $row['id'], 'tl_article', 'tl_page', 'languageMain')) {
            return $this->generateLabelWithWarning($label);
        }

        return $label;
    }

    /**
     * Generate missing translation warning for news child records.
     *
     * @param array{0: array<string, int|string>} $args
     * @param mixed                               $previousResult
     */
    private function onNewsChildRecords(array $args, $previousResult = null): string
    {
        $row = $args[0];
        $label = (string) $previousResult;

        if (empty($label)) {
            $label = '<div class="tl_content_left">'.$row['headline'].' <span style="color:#999;padding-left:3px">['.Date::parse(Config::get('datimFormat'), $row['date']).']</span></div>';
        }

        if (!$this->authorizationChecker->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELD_OF_TABLE, 'tl_news.languageMain')) {
            return $label;
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
     *
     * @param array{0: array<string, int|string>} $args
     * @param mixed                               $previousResult
     */
    private function onCalendarEventChildRecords(array $args, $previousResult = null): string
    {
        $row = $args[0];
        $label = (string) $previousResult;

        if (!$this->authorizationChecker->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELD_OF_TABLE, 'tl_calendar_events.languageMain')) {
            return $label;
        }

        if (0 === $this->getChildTranslation((int) $row['id'], 'tl_calendar_events', 'tl_calendar', 'master')) {
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
     *
     * @param array{0: array<string, int|string>} $args
     * @param mixed                               $previousResult
     */
    private function onFaqChildRecords(array $args, $previousResult = null): string
    {
        $row = $args[0];
        $label = (string) $previousResult;

        if (!$this->authorizationChecker->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELD_OF_TABLE, 'tl_faq.languageMain')) {
            return $label;
        }

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

    private function generateLabelWithWarning(string $label, string $imgStyle = ''): string
    {
        return $label.\sprintf(
            '<span style="padding-left:3px"><img src="%s" alt="%s" title="%s" style="%s"></span>',
            'bundles/terminal42changelanguage/language-warning.png',
            $GLOBALS['TL_LANG']['MSC']['noMainLanguage'],
            $GLOBALS['TL_LANG']['MSC']['noMainLanguage'],
            $imgStyle,
        );
    }

    /**
     * @return array<string, string>
     */
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
                [ArrayParameterType::INTEGER],
            );

            if ($children) {
                $return = $children + $childRecords($children, $rootId, $return);
            }

            return $return;
        };

        $this->pageCache = [];
        $rootIds = $this->connection->fetchFirstColumn("SELECT id FROM tl_page WHERE type='root' AND (fallback='' OR languageRoot>0)");
        $rootIds = array_map('intval', $rootIds);

        foreach ($rootIds as $rootId) {
            $this->pageCache += $childRecords([$rootId => []], $rootId);
        }

        return $this->pageCache[$id] ?? null;
    }

    private function getChildTranslation(int $id, string $table, string $ptable, string $parentField): ?int
    {
        if (isset($this->translationCache[$table])) {
            return $this->translationCache[$table][$id] ?? null;
        }

        $this->translationCache[$table] = array_map('intval', $this->connection->fetchAllKeyValue(
            <<<SQL
                    SELECT
                        c.id,
                        IFNULL(ct.id, 0)
                    FROM $table c
                        JOIN $ptable p ON c.pid=p.id
                        LEFT JOIN $table ct ON c.languageMain=ct.id
                        LEFT JOIN $ptable pt ON p.$parentField=pt.id
                    WHERE pt.id > 0
                SQL,
        ));

        return $this->translationCache[$table][$id] ?? null;
    }
}
