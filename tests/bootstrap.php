<?php

declare(strict_types=1);

namespace;

use Contao\CoreBundle\Config\ResourceFinder;
use Contao\System;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\Log\Logger;

include_once __DIR__.'/../vendor/autoload.php';

ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);

spl_autoload_register(
    static function ($class): void {
        if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false)) {
            return;
        }

        if (false !== strpos($class, '\\') && false === strpos($class, 'Model\\') && false === strpos($class, 'Database\\')) {
            return;
        }

        $namespaced = 'Contao\\'.$class;

        if (class_exists($namespaced) || interface_exists($namespaced) || trait_exists($namespaced)) {
            class_alias($namespaced, $class);
        }
    }
);

define('TL_ROOT', __DIR__.'/../');
define('TL_MODE', '');
define('TL_ERROR', 'error');
define('BE_USER_LOGGED_IN', false);

$container = new ContainerBuilder(
    new ParameterBag(
        [
            'kernel.debug' => false,
            'kernel.cache_dir' => sys_get_temp_dir(),
            'kernel.bundles' => ['ChangeLanguage'],
        ]
    )
);

$container->setParameter('kernel.project_dir', __DIR__.'/../');
$container->set('contao.resource_locator', new FileLocator([__DIR__.'/../contao']));
$container->set('contao.resource_finder', new ResourceFinder([__DIR__.'/../contao/']));
$container->set('monolog.logger.contao', new Logger());
$container->set('database_connection', DriverManager::getConnection([
    'driver' => 'pdo_mysql',
    'url' => $_ENV['DATABASE_URL'],
]));

System::setContainer($container);
