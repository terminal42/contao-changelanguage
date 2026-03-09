<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Doctrine\DBAL\Connection;

#[AsCallback('tl_user', 'fields.pageLanguageLabels.options')]
class UserLabelsListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<int|string, string>
     */
    public function __invoke(): array
    {
        return $this->connection->fetchAllKeyValue("SELECT id, title FROM tl_page WHERE type='root' AND (fallback='' OR languageRoot!=0) ORDER BY pid, sorting");
    }
}
