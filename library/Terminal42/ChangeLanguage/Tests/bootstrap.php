<?php

/**
 * changelanguage Extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-changelanguage
 */

namespace {

    ini_set('error_reporting', E_ALL & ~E_NOTICE);
    ini_set('display_errors', true);
    ini_set('display_startup_errors', true);

    spl_autoload_register(
        function ($class) {
            if (class_exists($class, false) || interface_exists($class, false) || trait_exists($class, false)) {
                return;
            }

            if (strpos($class, '\\') !== false && strpos($class, 'Model\\') === false && strpos($class, 'Database\\') === false) {
                return;
            }

            $namespaced = 'Contao\\'.$class;

            if (class_exists($namespaced) || interface_exists($namespaced) || trait_exists($namespaced)) {
                class_alias($namespaced, $class);
            }
        }
    );

    define('TL_ROOT', __DIR__ . '/../../../../vendor/contao/core');
    #require_once __DIR__ . '/../../../../vendor/contao/core/system/helper/ide_compat.php';
    require_once TL_ROOT . '/system/helper/functions.php';

    // Container in Contao 4
    if (method_exists('System', 'getContainer')) {
        $container = new \Symfony\Component\DependencyInjection\ContainerBuilder(
            new \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag(
                [
                    'kernel.cache_dir' => sys_get_temp_dir(),
                    'kernel.bundles'   => ['ChangeLanguage']
                ]
            )
        );

        $container->set('contao.resource_locator', new \Symfony\Component\Config\FileLocator([__DIR__ . '/../../../../']));
        $container->set('contao.resource_finder', new \Contao\CoreBundle\Config\ResourceFinder([__DIR__ . '/../../../../']));

        System::setContainer($container);
    }


    class Config extends \Contao\Config
    {
        protected function initialize()
        {
            parent::initialize();

            $GLOBALS['TL_CONFIG']['dbDriver']   = 'MySQLi';
            $GLOBALS['TL_CONFIG']['dbUser']     = $GLOBALS['DB_USER'];
            $GLOBALS['TL_CONFIG']['dbPass']     = $GLOBALS['DB_PASSWD'];
            $GLOBALS['TL_CONFIG']['dbHost']     = $GLOBALS['DB_HOST'];
            $GLOBALS['TL_CONFIG']['dbDatabase'] = $GLOBALS['DB_DBNAME'];
            $GLOBALS['TL_CONFIG']['dbPort']     = $GLOBALS['DB_PORT'];
        }
    }
}

namespace Model {
    class Registry extends \Contao\Model\Registry
    {
        /**
         * @inheritDoc
         */
        public function register(\Model $objModel)
        {
            // Do not register models
        }
    }
}
