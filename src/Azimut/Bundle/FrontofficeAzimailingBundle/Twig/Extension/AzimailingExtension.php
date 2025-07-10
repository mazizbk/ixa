<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-10-23 17:58:34
 */

namespace Azimut\Bundle\FrontofficeAzimailingBundle\Twig\Extension;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Azimut\Bundle\FrontofficeAzimailingBundle\Service\AzimailingDriver;
use Azimut\Bundle\FrontofficeAzimailingBundle\Form\Type\SubscriptionType;

class AzimailingExtension extends \Twig_Extension
{
    /**
     * @var AzimailingDriver
     */
    private $azimailingDriver;

    /**
     * @var EngineInterface
     */
    private $templatingEngine;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    public function __construct(AzimailingDriver $azimailingDriver,EngineInterface $templatingEngine, FormFactoryInterface $formFactory)
    {
        $this->azimailingDriver = $azimailingDriver;
        $this->templatingEngine = $templatingEngine;
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'azimut_azimailing';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('renderAzimailingSubscriptionForm', [$this, 'renderSubscriptionForm'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('renderAzimailingArchives', [$this, 'renderArchives'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Render subscription form
     *
     * @param string $templateSubdir
     *
     * @return string
     */
    public function renderSubscriptionForm($templateSubdir = null)
    {
        $form = $this->formFactory->create(SubscriptionType::class);
        return $this->templatingEngine->render('Azimailing/'. $templateSubdir .($templateSubdir ? '/' : '') .'subscription_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Render newsletter archives
     *
     * @param string $templateSubdir
     *
     * @return string
     */
    public function renderArchives($templateSubdir = null)
    {
        return $this->templatingEngine->render('Azimailing/'. $templateSubdir .($templateSubdir ? '/' : '') .'archives.html.twig', [
            'archivesHtmlContent' => $this->azimailingDriver->getArchives(),
        ]);
    }
}
