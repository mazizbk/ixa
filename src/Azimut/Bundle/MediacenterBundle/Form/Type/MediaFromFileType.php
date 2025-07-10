<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-11-15 11:45:02
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Azimut\Bundle\MediacenterBundle\Form\Transformer\FileToMediaTransformer;

class MediaFromFileType extends AbstractType
{
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new FileToMediaTransformer($this->registry->getManager());

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
        return FileType::class;
    }
}
