<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php74\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonyLevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/../../src',
    ]);

    $rectorConfig->skip([
        ClassPropertyAssignToConstructorPromotionRector::class => [
            '/src/Entity/*',
        ],
        ChangeSwitchToMatchRector::class,
    ]);

    $services = $rectorConfig->services();
    $services->set(ArraySpreadInsteadOfArrayMergeRector::class);
    $services->set(CompactToVariablesRector::class);
    $services->set(RemoveUnusedPrivateMethodParameterRector::class);
    $services->set(RestoreDefaultNullToNullableTypePropertyRector::class);
    $services->set(TypedPropertyRector::class);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_74,
        DoctrineSetList::DOCTRINE_DBAL_30,
        DoctrineSetList::DOCTRINE_ORM_29,
        SymfonyLevelSetList::UP_TO_SYMFONY_54,
    ]);
};
