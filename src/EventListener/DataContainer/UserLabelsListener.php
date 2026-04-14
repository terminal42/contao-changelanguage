<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
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
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser) {
            return [];
        }

        if ($user->isAdmin) {
            return $this->connection->fetchAllKeyValue("SELECT id, title FROM tl_page WHERE type='root' AND (fallback='' OR languageRoot!=0) ORDER BY pid, sorting");
        }

        if (empty($user->pagemounts)) {
            return [];
        }

        return $this->connection->fetchAllKeyValue(
            "SELECT id, title FROM tl_page WHERE type='root' AND (fallback='' OR languageRoot!=0) AND id IN (?) ORDER BY pid, sorting",
            [$user->pagemounts],
            [ArrayParameterType::INTEGER]
        );
    }
}
