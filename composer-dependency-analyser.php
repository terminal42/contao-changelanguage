<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    // Optional integrations
    ->ignoreErrorsOnPackage('contao/calendar-bundle', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackage('contao/faq-bundle', [ErrorType::DEV_DEPENDENCY_IN_PROD])
    ->ignoreErrorsOnPackage('contao/news-bundle', [ErrorType::DEV_DEPENDENCY_IN_PROD])

    // Ignore test setup
    ->addPathToExclude(__DIR__ . '/tests/Fixtures')
    ->ignoreErrorsOnPackage('symfony/var-exporter', [ErrorType::UNUSED_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/monolog-bundle', [ErrorType::UNUSED_DEPENDENCY])
;
