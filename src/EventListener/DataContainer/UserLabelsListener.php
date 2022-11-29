<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\EventListener\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Doctrine\DBAL\Connection;

/**
 * @Callback(table="tl_user", target="fields.pageLanguageLabels.options")
 */
class UserLabelsListener
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke()
    {
        $pages = $this->connection->fetchAllAssociative("SELECT id, title FROM tl_page WHERE type='root' AND (fallback='' OR languageRoot!=0) ORDER BY pid, sorting");

        return array_combine(array_column($pages, 'id'), array_column($pages, 'title'));
    }
}
