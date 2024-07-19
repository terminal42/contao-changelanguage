<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Terminal42\ChangeLanguage\Tests\Fixtures\app;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\NewsBundle\ContaoNewsBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Knp\Bundle\MenuBundle\KnpMenuBundle;
use Knp\Bundle\TimeBundle\KnpTimeBundle;
use Psr\Log\NullLogger;
use Scheb\TwoFactorBundle\SchebTwoFactorBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Terminal42\ChangeLanguage\Terminal42ChangeLanguageBundle;
use Terminal42\ServiceAnnotationBundle\Terminal42ServiceAnnotationBundle;

class AppKernel extends Kernel
{
    /**
     * @return array<BundleInterface>
     */
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new SecurityBundle(),
            new TwigBundle(),
            new MonologBundle(),
            new DoctrineBundle(),
            new SchebTwoFactorBundle(),
            new KnpTimeBundle(),
            new KnpMenuBundle(),
            new CmfRoutingBundle(),
            new Terminal42ServiceAnnotationBundle(),
            new ContaoCoreBundle(),
            new ContaoNewsBundle(),
            new Terminal42ChangeLanguageBundle(),
        ];
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__, 3).'/var';
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir().'/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        if (InstalledVersions::satisfies(new VersionParser(), 'contao/core-bundle', '^5')) {
            $loader->load(__DIR__.'/config/config_5.yml');
        } else {
            $loader->load(__DIR__.'/config/config_4.yml');
        }
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->register('monolog.logger.contao', NullLogger::class);
    }
}
