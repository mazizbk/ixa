<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-10-25 17:00:44
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Azimut\Bundle\MediacenterBundle\Form\Transformer\EmbedHtmlToMediaTransformer;

class MediaFromEmbedHtmlType extends AbstractType
{
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new EmbedHtmlToMediaTransformer($this->registry->getManager());

        $builder
            ->resetModelTransformers()
            ->addModelTransformer($transformer)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Media::class
        ));
    }

    public function getParent()
    {
        return TextareaType::class;
    }
}
