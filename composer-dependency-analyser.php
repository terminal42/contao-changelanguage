<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->ignoreErrorsOnPackage('doctrine/dbal', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/config', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/dependency-injection', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/event-dispatcher-contracts', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/http-kernel', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/routing', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/security-core', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/service-contracts', [ErrorType::SHADOW_DEPENDENCY])

    // Optional integrations
    ->ignoreErrorsOnPackage('contao/calendar-bundle', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackage('contao/faq-bundle', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackage('contao/news-bundle', [ErrorType::DEV_DEPENDENCY_IN_PROD])

    // Ignore test setup
    ->addPathToExclude(__DIR__ . '/tests/Fixtures')
    ->addPathToExclude(__DIR__ . '/tests/ClearCachePhpunitExtension.php')
    ->ignoreErrorsOnPackage('symfony/monolog-bundle', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/phpunit-bridge', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('terminal42/service-annotation-bundle', [ErrorType::UNUSED_DEPENDENCY])
;
