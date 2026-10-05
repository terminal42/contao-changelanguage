<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\User;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;

#[AsCallback('tl_user', 'fields.pageLanguageLabels.options')]
class UserLabelsListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Security $security,
    ) {
    }

    /**
     * @return array<int|string, string>
     */
    public function __invoke(): array
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return $this->connection->fetchAllKeyValue("SELECT id, title FROM tl_page WHERE type='root' AND (fallback='' OR languageRoot!=0) ORDER BY pid, sorting");
        }

        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser || empty($user->pagemounts)) {
            return [];
        }

        $pagemounts = [];

        foreach ($user->pagemounts as $pageId) {
            $pagemounts[] = Database::getInstance()->getParentRecords($pageId, 'tl_page');
        }

        return $this->connection->fetchAllKeyValue(
            "SELECT id, title FROM tl_page WHERE type='root' AND (fallback='' OR languageRoot!=0) AND id IN (?) ORDER BY pid, sorting",
            [array_unique(array_merge(...$pagemounts))],
            [ArrayParameterType::INTEGER]
        );
    }
}
