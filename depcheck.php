<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    ->ignoreErrorsOnPackage('composer/semver', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('doctrine/dbal', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('doctrine/doctrine-bundle', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('knplabs/knp-menu-bundle', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('knplabs/knp-time-bundle', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('psr/log', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('scheb/2fa-bundle', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony-cmf/routing-bundle', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/config', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/dependency-injection', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/event-dispatcher-contracts', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/filesystem', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/framework-bundle', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/http-kernel', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/routing', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/security-bundle', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/security-core', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/service-contracts', [ErrorType::SHADOW_DEPENDENCY])
    ->ignoreErrorsOnPackage('symfony/twig-bundle', [ErrorType::SHADOW_DEPENDENCY])
;
