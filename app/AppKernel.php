<?php

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Symfony\WebpackEncoreBundle\WebpackEncoreBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Alex\AsseticExtraBundle\AlexAsseticExtraBundle(),

            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new JMS\I18nRoutingBundle\JMSI18nRoutingBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
            new Http\HttplugBundle\HttplugBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new EWZ\Bundle\RecaptchaBundle\EWZRecaptchaBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Misd\PhoneNumberBundle\MisdPhoneNumberBundle(),
            new Burgov\Bundle\KeyValueFormBundle\BurgovKeyValueFormBundle(),
            new Webit\DoctrineJsonBundle\WebitDoctrineJsonBundle(),
            new Sentry\SentryBundle\SentryBundle(),

            new Azimut\Bundle\DoctrineExtraBundle\AzimutDoctrineExtraBundle(),
            new Azimut\Bundle\FormExtraBundle\AzimutFormExtraBundle(),
            new Azimut\Bundle\BackofficeBundle\AzimutBackofficeBundle(),
            new Azimut\Bundle\MediacenterBundle\AzimutMediacenterBundle(),
            new Azimut\Bundle\CmsBundle\AzimutCmsBundle(),
            new Azimut\Bundle\CmsContactBundle\AzimutCmsContactBundle(),
            new Azimut\Bundle\CmsMapBundle\AzimutCmsMapBundle(),
            new Azimut\Bundle\FrontofficeBundle\AzimutFrontofficeBundle(),
            new Azimut\Bundle\FrontofficeCustomBundle\AzimutFrontofficeCustomBundle(),
            new Azimut\Bundle\SecurityBundle\AzimutSecurityBundle(),
            new Azimut\Bundle\DemoBundle\AzimutDemoBundle(),
            new Azimut\Bundle\DemoAngularJsBundle\AzimutDemoAngularJsBundle(),
            new Azimut\Bundle\DemoExternalAppBundle\AzimutDemoExternalAppBundle(),
            new Azimut\Bundle\DemoSecurityInjectionBundle\AzimutDemoSecurityInjectionBundle(),
            new Azimut\Bundle\TwigExtraBundle\AzimutTwigExtraBundle(),
            new Azimut\Bundle\ConsoleExtraBundle\AzimutConsoleExtraBundle(),
            new Azimut\Bundle\HttpKernelExtraBundle\AzimutHttpKernelExtraBundle(),
            new Azimut\Bundle\AzimutLoginBundle\AzimutAzimutLoginBundle(),
            new Azimut\Bundle\FrontofficeSecurityBundle\AzimutFrontofficeSecurityBundle(),
            new Azimut\Bundle\ModerationBundle\AzimutModerationBundle(),
            new Azimut\Bundle\I18nRoutingExtraBundle\AzimutI18nRoutingExtraBundle(),
            new Azimut\Bundle\ShopBundle\AzimutShopBundle(),
            new Azimut\Bundle\DemoPaymentBundle\AzimutDemoPaymentBundle(),
            new Azimut\Bundle\DemoShopExtraBundle\AzimutDemoShopExtraBundle(),
            new Azimut\Bundle\FrontofficeAzimailingBundle\AzimutFrontofficeAzimailingBundle(),
            new Azimut\Bundle\MontgolfiereAppBundle\AzimutMontgolfiereAppBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Nelmio\ApiDocBundle\NelmioApiDocBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function getRootDir()
    {
        return __DIR__;
    }
    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }
    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    protected function build(ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(\Doctrine\Common\EventSubscriber::class)
            ->addTag('doctrine.event_subscriber')
        ;
    }


}
