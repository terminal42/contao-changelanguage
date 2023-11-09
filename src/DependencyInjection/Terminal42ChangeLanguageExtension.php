<?php

declare(strict_types=1);

namespace Terminal42\ChangeLanguage\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Terminal42\ChangeLanguage\EventListener\Navigation\CalendarNavigationListener;
use Terminal42\ChangeLanguage\EventListener\Navigation\FaqNavigationListener;
use Terminal42\ChangeLanguage\EventListener\Navigation\NewsNavigationListener;

class Terminal42ChangeLanguageExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config'),
        );

        $loader->load('services.yaml');

        $this->removeExtensionListeners($container);
    }

    private function removeExtensionListeners(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        $conditions = [
            'ContaoCalendarBundle' => [CalendarNavigationListener::class],
            'ContaoNewsBundle' => [NewsNavigationListener::class],
            'ContaoFaqBundle' => [FaqNavigationListener::class],
        ];

        foreach ($conditions as $name => $listeners) {
            if (!isset($bundles[$name])) {
                foreach ($listeners as $listener) {
                    $container->removeDefinition($listener);
                }
            }
        }
    }
}
